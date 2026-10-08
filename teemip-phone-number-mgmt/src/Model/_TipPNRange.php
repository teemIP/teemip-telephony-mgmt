<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Model;

use CMDBObjectSet;
use Combodo\iTop\Service\Events\EventData;
use DBObjectSearch;
use DBObjectSet;
use DBSearch;
use Dict;
use MetaModel;
use TipPNObject;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper\PhoneNumberUtils;

class _TipPNRange extends TipPNObject {
    /**
     * Handler for EVENT_DB_COMPUTE_VALUES event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPNRangeComputeValuesRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        // Store numbers in E.164 format
        foreach (['firstnumber', 'lastnumber'] as $sAttCode) {
            $sNumber = PhoneNumberUtils::Normalize($this->Get($sAttCode));
            if ($sNumber != $this->Get($sAttCode)) {
                $this->Set($sAttCode, $sNumber);
            }
        }

        $aEventData = $oEventData->GetEventData();
        if ($aEventData['is_new']) {
            // At creation, prefill numbers with the calling code of the selected country
            $this->PrefillNumbersWithCallingCode();

            // At creation, compute parent_id only in the case where no delegation is done.
            $iParentOrgId = $this->Get('parent_org_id');
            if ($iParentOrgId == 0) {
                $iOrgId = $this->Get('org_id');
                $sFirstNumber = $this->Get('firstnumber');
                $sLastNumber = $this->Get('lastnumber');

                // Parent is the smallest range containing the new range
                $oParent = static::FindSmallestRangeContaining($iOrgId, $sFirstNumber, $sLastNumber);
                $this->Set('parent_id', ($oParent === null) ? 0 : $oParent->GetKey());
            }
        }
    }

    /**
     * Handler for EVENT_DB_SET_ATTRIBUTES_FLAGS event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPNRangeSetAttributesFlagsRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $this->AddAttributeFlags('org_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('tippncountrycode_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('occupancy', OPT_ATT_READONLY);
        $this->AddAttributeFlags('parent_org_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('parent_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('firstnumber', OPT_ATT_READONLY);
        $this->AddAttributeFlags('lastnumber', OPT_ATT_READONLY);
    }

    /**
     * Handler for EVENT_DB_CHECK_TO_WRITE event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPNRangeCheckToWriteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $sFirstNumber = $this->Get('firstnumber');
        $sLastNumber = $this->Get('lastnumber');

        // Make sure numbers are in E.164 format, have the same length and match the country calling code
        foreach ([$sFirstNumber, $sLastNumber] as $sNumber) {
            if (!PhoneNumberUtils::IsE164($sNumber)) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPhoneNumber:NotE164', $sNumber));
                return;
            }
        }
        if (!PhoneNumberUtils::IsSameLength($sFirstNumber, $sLastNumber)) {
            $this->AddCheckIssue(Dict::S('UI:PNManagement:Action:New:TipPNRange:NotSameLength'));
            return;
        }
        $oPNCountryCode = MetaModel::GetObject('TipPNCountryCode', $this->Get('tippncountrycode_id'), false /* MustBeFound */);
        if ($oPNCountryCode !== null) {
            $sCallingCode = $oPNCountryCode->Get('calling_code');
            if (!PhoneNumberUtils::HasCallingCode($sFirstNumber, $sCallingCode) || !PhoneNumberUtils::HasCallingCode($sLastNumber, $sCallingCode)) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:WrongCallingCode', $sCallingCode, $oPNCountryCode->Get('name')));
                return;
            }
        }

        // Make sure first number is smaller than last one
        if (strcmp($sFirstNumber, $sLastNumber) > 0) {
            $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:Reverted'));
            return;
        }

        // Make sure range is fully and strictly contained in requested parent range, if any
        $iParentId = $this->Get('parent_id');
        if ($iParentId > 0) {
            $oParent = MetaModel::GetObject('TipPNRange', $iParentId);
            if ($oParent) {
                if ($oParent->Get('tippncountrycode_id') != $this->Get('tippncountrycode_id')) {
                    $this->AddCheckIssue(Dict::S('UI:PNManagement:Action:New:TipPNRange:NotSameCountryAsParent'));
                    return;
                }
                if (!PhoneNumberUtils::IsSameLength($sFirstNumber, $oParent->Get('firstnumber'))) {
                    $this->AddCheckIssue(Dict::S('UI:PNManagement:Action:New:TipPNRange:NotInParent'));
                    return;
                }
                $sParentFirstNumber = $oParent->Get('firstnumber');
                $sParentLastNumber = $oParent->Get('lastnumber');
                if (($sFirstNumber < $sParentFirstNumber) || ($sParentLastNumber < $sLastNumber) || (($sFirstNumber == $sParentFirstNumber) && ($sParentLastNumber == $sLastNumber))) {
                    $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:NotInParent'));
                    return;
                }
            }
        }

        // Make sure range doesn't collide with another range attached to the same parent.
        //		If no parent is specified (null), then check is done with all such ranges with no parent specified.
        //		It is done on ranges belonging to the same parent otherwise
        $iId = $this->GetKey();
        $iOrgId = $this->Get('org_id');
        $sOQL = "SELECT TipPNRange AS r WHERE r.parent_id = :parent_id AND (r.org_id = :org_id OR r.parent_org_id = :org_id) 
                 UNION SELECT TipPNRange AS r WHERE IF (:parent_id = 0, r.parent_org_id != 0 AND r.org_id = :org_id, 0)";
        $oPNRangeSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
            'parent_id' => $iParentId,
            'id' => $iId,
            'org_id' => $iOrgId,
        ));
        while ($oPNRange = $oPNRangeSet->Fetch()) {
            $sCurrentFirstNumber = $oPNRange->Get('firstnumber');
            $sCurrentLastNumber = $oPNRange->Get('lastnumber');
            // Ranges of numbers with different length cannot collide
            if (!PhoneNumberUtils::IsSameLength($sFirstNumber, $sCurrentFirstNumber)) {
                continue;
            }

            // Does the range already exist?
            // Note that case where org_id are the same is already covered by unicity rule
            if (($oPNRange->Get('org_id') == $this->Get('parent_org_id')) && ($sCurrentFirstNumber == $sFirstNumber) && ($sCurrentLastNumber == $sLastNumber)) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:Collision0'));
                return;
            }
            // Is new first # part of an existing range?
            if (($sCurrentFirstNumber < $sFirstNumber) && ($sFirstNumber <= $sCurrentLastNumber)) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:Collision1'));
                return;
            }
            // Is new last Ip part of an existing range?
            if (($sCurrentFirstNumber <= $sLastNumber) && ($sLastNumber < $sCurrentLastNumber)) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNRange:Collision2'));
                return;
            }
        }

        // Make sure range doesn't contain any range delegated from another organization
        $sOQL = "SELECT TipPNRange AS r WHERE :firstnumber <= r.firstnumber AND r.lastnumber <= :lastnumber AND r.org_id = :org_id AND r.parent_org_id > 0";
        $oPNRangeSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
            'firstnumber' => $sFirstNumber,
            'lastnumber' => $sLastNumber,
            'org_id' => $iOrgId,
        ));
        if ($oPNRangeSet->CountExceeds(0)) {
            $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:Delegate:TipPNRange:ConflictWithDelegatedBlockFromOtherOrg'));
            return;
        }

        // If block is delegated straight away
        $iParentOrgId = $this->Get('parent_org_id');
        if ($iParentOrgId != 0) {
            // FIXME See later

        }
    }

    /**
     * Handler for EVENT_DB_AFTER_WRITE event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPNRangeAfterWriteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $iId = $this->GetKey();
        $iOrgId = $this->Get('org_id');
        $iParentOrgId = $this->Get('parent_org_id');
        $sFirstNumber = $this->Get('firstnumber');
        $sLastNumber = $this->Get('lastnumber');

        $aEventData = $oEventData->GetEventData();
        if ($aEventData['is_new']) {
            // Look for all ranges attached to the parent of the range being created and contained in it
            // Attach them to the new range
            $sOQL = "SELECT TipPNRange AS r WHERE r.parent_id = :parent_id AND :firstnumber <= r.firstnumber AND r.lastnumber <= :lastnumber AND (r.org_id = :org_id OR r.parent_org_id = :org_id) AND r.id != :id";
            $oPNRangeSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
                'parent_id' => $this->Get('parent_id'),
                'firstnumber' => $sFirstNumber,
                'lastnumber' => $sLastNumber,
                'org_id' => $iOrgId,
                'id' => $iId,
            ));
            while ($oPNRange = $oPNRangeSet->Fetch()) {
                $oPNRange->Set('parent_id', $iId);
                $oPNRange->DBUpdate();
            }

            // If range is delegated, look for ranges at the top of the tree, in the same org, that are contained within the new range
            // Attach them to the new range
            if ($iParentOrgId != 0) {
                $sOQL = "SELECT TipPNRange AS r WHERE r.parent_id = 0 AND :firstnumber <= r.firstnumber AND r.lastnumber <= :lastnumber AND r.org_id = :org_id";
                $oPNRangeSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
                    'firstnumber' => $sFirstNumber,
                    'lastnumber' => $sLastNumber,
                    'org_id' => $iOrgId,
                ));
                while ($oPNRange = $oPNRangeSet->Fetch()) {
                    $oPNRange->Set('parent_id', $iId);
                    $oPNRange->DBUpdate();
                }
            }

            // Attach the phone numbers of the organization that belong to the new range
            $this->AttachPhoneNumbers();
        }
    }

    /**
     * Prefill first and last numbers with the calling code of the selected country.
     * Numbers are only replaced when empty or when they only contain the calling code of another country.
     * As firstnumber and lastnumber depend on tippncountrycode_id, this is also applied when the country is changed in the creation form.
     *
     * @return void
     */
    protected function PrefillNumbersWithCallingCode(): void
    {
        $oPNCountryCode = MetaModel::GetObject('TipPNCountryCode', $this->Get('tippncountrycode_id'), false /* MustBeFound */);
        if ($oPNCountryCode === null) {
            return;
        }
        $sPrefix = '+'.$oPNCountryCode->Get('calling_code');
        foreach (['firstnumber', 'lastnumber'] as $sAttCode) {
            $sNumber = $this->Get($sAttCode);
            if (($sNumber == '') || (($sNumber != $sPrefix) && preg_match('/^\+[0-9]{1,3}$/', $sNumber))) {
                $this->Set($sAttCode, $sPrefix);
            }
        }
    }

    /**
     * Attach to the range the phone numbers of the organization that it contains, when the range is now the smallest range containing them:
     *   . orphan numbers
     *   . numbers attached to a larger range, i.e. a range that contains the current one, as ranges cannot overlap
     *
     * @return void
     */
    public function AttachPhoneNumbers(): void
    {
        $iId = $this->GetKey();
        $sFirstNumber = $this->Get('firstnumber');
        $sLastNumber = $this->Get('lastnumber');
        $iSize = $this->GetSize();

        $sOQL = "SELECT TipPhoneNumber AS p WHERE p.org_id = :org_id AND :firstnumber <= p.number AND p.number <= :lastnumber AND p.tippnrange_id != :id";
        $oPhoneNumberSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
            'firstnumber' => $sFirstNumber,
            'lastnumber' => $sLastNumber,
            'org_id' => $this->Get('org_id'),
            'id' => $iId,
        ));
        $aRangeSizes = array();
        while ($oPhoneNumber = $oPhoneNumberSet->Fetch()) {
            // OQL comparison is lexicographic: make sure number really belongs to the range
            if (!static::IsInRange($oPhoneNumber->Get('number'), $sFirstNumber, $sLastNumber)) {
                continue;
            }
            $iCurrentRangeId = $oPhoneNumber->Get('tippnrange_id');
            if ($iCurrentRangeId != 0) {
                if (!array_key_exists($iCurrentRangeId, $aRangeSizes)) {
                    $oCurrentRange = MetaModel::GetObject('TipPNRange', $iCurrentRangeId, false /* MustBeFound */);
                    $aRangeSizes[$iCurrentRangeId] = ($oCurrentRange === null) ? PHP_INT_MAX : $oCurrentRange->GetSize();
                }
                // Number is already attached to a smaller range (a child of the current one)
                if ($aRangeSizes[$iCurrentRangeId] <= $iSize) {
                    continue;
                }
            }
            // Occupancy of previous and new ranges is updated by TipPhoneNumber's after write event
            $oPhoneNumber->Set('tippnrange_id', $iId);
            $oPhoneNumber->DBUpdate();
        }
    }

    /**
     * Check if number belongs to [first, last]. All numbers must be E.164 and of the same length.
     *
     * @param string $sNumber
     * @param string $sFirstNumber
     * @param string $sLastNumber
     * @return bool
     */
    public static function IsInRange(string $sNumber, string $sFirstNumber, string $sLastNumber): bool
    {
        if (!PhoneNumberUtils::IsSameLength($sNumber, $sFirstNumber) || !PhoneNumberUtils::IsSameLength($sNumber, $sLastNumber)) {
            return false;
        }

        return (strcmp($sFirstNumber, $sNumber) <= 0) && (strcmp($sNumber, $sLastNumber) <= 0);
    }

    /**
     * Number of phone numbers within [first, last]. Both numbers must be E.164 and of the same length.
     *
     * @param string $sFirstNumber
     * @param string $sLastNumber
     * @return int
     */
    public static function GetRangeSize(string $sFirstNumber, string $sLastNumber): int
    {
        if (!PhoneNumberUtils::IsSameLength($sFirstNumber, $sLastNumber)) {
            return 0;
        }
        // E.164 numbers have at most 15 digits: they fit in a 64 bits integer
        return (int)substr($sLastNumber, 1) - (int)substr($sFirstNumber, 1) + 1;
    }

    /**
     * Get the smallest range of an organization that contains a number or, if a last number is given, a range of numbers
     *
     * @param int $iOrgId
     * @param string $sFirstNumber E.164 number
     * @param string|null $sLastNumber E.164 number, same as first number if null
     * @return \TipPNRange|null
     */
    public static function FindSmallestRangeContaining($iOrgId, string $sFirstNumber, ?string $sLastNumber = null)
    {
        $sLastNumber = $sLastNumber ?? $sFirstNumber;
        // OQL comparison is lexicographic: numbers of different length are filtered out afterward
        $sOQL = "SELECT TipPNRange AS r WHERE r.org_id = :org_id AND r.firstnumber <= :firstnumber AND :lastnumber <= r.lastnumber";
        $oPNRangeSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array(
            'org_id' => $iOrgId,
            'firstnumber' => $sFirstNumber,
            'lastnumber' => $sLastNumber,
        ));
        $oSmallestPNRange = null;
        $iMinSize = 0;
        while ($oPNRange = $oPNRangeSet->Fetch()) {
            $sRangeFirstNumber = $oPNRange->Get('firstnumber');
            $sRangeLastNumber = $oPNRange->Get('lastnumber');
            if (!static::IsInRange($sFirstNumber, $sRangeFirstNumber, $sRangeLastNumber) || !static::IsInRange($sLastNumber, $sRangeFirstNumber, $sRangeLastNumber)) {
                continue;
            }
            $iSize = $oPNRange->GetSize();
            if (($oSmallestPNRange === null) || ($iSize < $iMinSize)) {
                $oSmallestPNRange = $oPNRange;
                $iMinSize = $iSize;
            }
        }

        return $oSmallestPNRange;
    }

    /**
     * Compute the size f the range
     *
     * @return int
     */
    public function GetSize(): int
    {
        return static::GetRangeSize($this->Get('firstnumber'), $this->Get('lastnumber'));
    }

    /**
     * Compute the occupancy percentage
     *
     * @return int
     */
    public function GetOccupancy(): int
    {
        $sOQL = "SELECT TipPhoneNumber AS p WHERE tippnrange_id = :id";
        $oPhoneNumberSet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array('id' => $this->GetKey()));
        $iSize = $this->GetSize();
        if ($iSize <= 0) {
            return 0;
        }

        return (int)round(($oPhoneNumberSet->Count() / $iSize) * 100);
    }

    /**
     * Returns index to be used within tree computations
     *
     * @return int
     * @throws \ArchivedObjectException
     * @throws \CoreException
     */
    public function GetIndexForTree() {
        return $this->Get('firstnumber');
    }

    /**
     * Get TipPNRange in the node of a hierarchical tree
     *
     * @param $bWithIcon
     * @param $iTreeOrgId
     * @return string
     */
    public function GetAsLeaf($bWithIcon, $iTreeOrgId) {
        $sHtml = '';
        $sHtml .= "&nbsp;".$this->GetHyperlink();
        $sHtml .= "&nbsp;&nbsp;&nbsp;[".$this->Get('firstnumber')." - ".$this->Get('lastnumber')."]";
//        $sHtml .= "&nbsp;&nbsp;&nbsp;".$this->GetAsHTML('ipblocktype_name');

        // Display delegation information if required
        $iOrgId = $this->Get('org_id');
        $iParentOrgId = $this->Get('parent_org_id');
        if ($iParentOrgId != 0) {
            if ($iTreeOrgId == $iOrgId) {
                // Range is delegated from parent org
                $sHtml .= "&nbsp;&nbsp;&nbsp; - ".Dict::Format('Class:TipPNRange:DelegatedFromParent', $this->GetAsHTML('parent_org_id'));
            } else {
                // Range is delegated to child org
                $sHtml .= "&nbsp;&nbsp;&nbsp; - ".Dict::Format('Class:TipPNRange:DelegatedToChild', $this->GetAsHTML('org_id'));
            }
        }

        return $sHtml;
    }

    /**
     * Check if TipPNRange is delegated
     *
     * @return bool
     */
    public function IsDelegated(): bool
    {
        return ($this->Get('parent_org_id') == 0) ? false : true;
    }

    /**
     * Delegate TipPNRange
     *
     * @param $aParam
     * @return void
     */
    public function DoDelegate($aParam) {
        $iOrgId = $this->Get('org_id');
        $iChildOrgId = $aParam['child_org_id'];

        $this->Set('parent_org_id', $iOrgId);
        $this->Set('org_id', $iChildOrgId);
        $this->DBUpdate();
    }

    /**
     * Undelegate TipPNRange
     *
     * @return void
     */
    public function DoUndelegate() {
        $iParentOrgId = $this->Get('parent_org_id');

        $this->Set('parent_org_id', 0);
        $this->Set('org_id', $iParentOrgId);
        $this->DBUpdate();
    }

    /**
     * Return next operation after current one
     *
     * @param $sOperation
     *
     * @return string
     */
    public function GetNextOperation($sOperation) {
        switch ($sOperation) {
            case 'findspace':
                return 'dofindspace';
            case 'dofindspace':
                return 'findspace';

            case 'shrinkrange':
                return 'doshrinkrange';
            case 'doshrinkrange':
                return 'shrinkrange';

            case 'splitrange':
                return 'dosplitrange';
            case 'dosplitrange':
                return 'splitrange';

            case 'expandrange':
                return 'doexpandrange';
            case 'doexpandrange':
                return 'expandrange';

            case 'delegate':
                return 'dodelegate';
            case 'dodelegate':
                return 'delegate';

            default:
                return '';
        }
    }

    /**
     * @inheritdoc
     */
    public static function GetShortcutActions($sFinalClass)
    {
        // Prepend the shortcut actions with the navigation menu
        $aNavigationActions = ['previous_pnrange', 'next_pnrange'];
        $aConfiguredActions = parent::GetShortcutActions($sFinalClass);
        $aShortcutActions = array_merge($aNavigationActions, $aConfiguredActions);

        return $aShortcutActions;
    }

    /**
     * Get the previous TipPNRange if it exists
     *
     * @param bool $bInRange if lookup should be done in parent range only
     *
     * @return null
     */
    public function GetPreviousRange($bInRange)
    {
        // Create OQL according to $bInRange
        $iParent = $this->Get('parent_id');
        if ($bInRange) {
            if ($iParent > 0) {
                $sOQL = 'SELECT TipPNRange AS r WHERE r.parent_id = :parent_id AND r.firstnumber < :number';
            } else {
                return null;
            }
        } else {
            $sOQL = 'SELECT TipPNRange AS r WHERE r.org_id = :org_id AND r.firstnumber < :number';
        }
        // Set the ordering criteria ['ip'=> false] and set a limit (1)
        $oPNRangeSet = new DBObjectSet(DBSearch::FromOQL($sOQL), ['firstnumber' => false], ['org_id' => $this->Get('org_id'), 'parent_id' => $iParent, 'number' => $this->Get('firstnumber')], null, 1);
        $oPNRangeSet->OptimizeColumnLoad(['TipPNRange' => ['id', 'firstnumber']]);
        if ($oPreviousRange = $oPNRangeSet->Fetch()) {
            return $oPreviousRange;
        }

        return null;
    }

    /**
     * Get the next TipPNRange if it exists
     *
     * @param bool $bInRange if lookup should be done in parent range only
     *
     * @return null
     */
    public function GetNextRange($bInRange)
    {
        // Create OQL according to $bInRange
        $iParent = $this->Get('parent_id');
        if ($bInRange) {
            if ($iParent > 0) {
                $sOQL = 'SELECT TipPNRange AS r WHERE r.parent_id = :parent_id AND r.firstnumber > :number';
            } else {
                return null;
            }
        } else {
            $sOQL = 'SELECT TipPNRange AS r WHERE r.org_id = :org_id AND r.firstnumber > :number';
        }
        // Set the ordering criteria ['ip'=> false] and set a limit (1)
        $oPNRangeSet = new DBObjectSet(DBSearch::FromOQL($sOQL), ['firstnumber' => true], ['org_id' => $this->Get('org_id'), 'parent_id' => $iParent, 'number' => $this->Get('firstnumber')], null, 1);
        $oPNRangeSet->OptimizeColumnLoad(['TipPNRange' => ['id', 'firstnumber']]);
        if ($oNextRange = $oPNRangeSet->Fetch()) {
            return $oNextRange;
        }

        return null;
    }


}