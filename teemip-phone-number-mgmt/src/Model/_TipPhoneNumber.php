<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Model;

use cmdbAbstractObject;
use CMDBObjectSet;
use Combodo\iTop\Service\Events\EventData;
use DBObjectSearch;
use DBObjectSet;
use DBSearch;
use Dict;
use DisplayBlock;
use MetaModel;
use TeemIp\TeemIp\Extension\Framework\Helper\IPUtils;
use TipPNObject;
use TipPNRange;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper\PhoneNumberUtils;
use WebPage;

class _TipPhoneNumber extends TipPNObject {
    /**
     * @inheritdoc
     */
    public function DisplayBareRelations(WebPage $oPage, $bEditMode = false)
    {
        // Execute parent function first
        parent::DisplayBareRelations($oPage, $bEditMode);

        if ($this->GetDisplayMode() != cmdbAbstractObject::ENUM_DISPLAY_MODE_VIEW) {
            return;
        }

        // Tab for CIs using the phone number
        //   Retrieve CIs first
        $aCISets = [];
        $iNbAllCIs = 0;
        foreach ($this->GetHostingCISets() as $sCIClass => $oCISet) {
            $iNbCIs = $oCISet->Count();
            if ($iNbCIs != 0) {
                $aCISets[$sCIClass] = $oCISet;
                $iNbAllCIs += $iNbCIs;
            }
        }

        //   Next, display them
        $sName = Dict::S('Class:TipPhoneNumber/Tab:ci_list');
        $sTitle = Dict::S('Class:TipPhoneNumber/Tab:ci_list+');
        if ($iNbAllCIs == 0) {
            $oSet = CMDBObjectSet::FromScratch('FunctionalCI');
            IPUtils::DisplayTabContent($oPage, $sName, 'ci_list', 'FunctionalCI', $sTitle, '', $oSet, false);

            return;
        }
        $oPage->SetCurrentTab('ci_list', $sName.' ('.$iNbAllCIs.')', $sTitle);
        foreach ($aCISets as $sCIClass => $oCISet) {
            $oBlock = DisplayBlock::FromObjectSet($oCISet, 'list', array('show_obsolete_data' => true));
            $oBlock->Display($oPage, 'blk-'.strtolower($sCIClass), array(
                'menu' => false,
                'panel_title' => MetaModel::GetName($sCIClass),
                'panel_title_tooltip' => Dict::Format('Class:TipPhoneNumber/Tab:ci_list_class', MetaModel::GetName($sCIClass)),
                'panel_icon' => MetaModel::GetClassIcon($sCIClass, false),
            ));
        }
    }

    /**
     * Get, for each class of CI that may point to a phone number, the set of CIs pointing to the current one.
     * Obsolete CIs are included.
     *
     * @return array of CMDBObjectSet indexed by class
     * @throws \CoreException
     * @throws \OQLException
     */
    public function GetHostingCISets(): array
    {
        $aCISets = [];
        foreach (PhoneNumberUtils::GetListOfClassesWithPNs() as $sCIClass => $aPNAttributes) {
            $aConditions = [];
            foreach ($aPNAttributes as $sPNAttribute) {
                $aConditions[] = "c.$sPNAttribute = :id";
            }
            $sOQL = "SELECT $sCIClass AS c WHERE ".implode(' OR ', $aConditions);
            $oCISet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array('id' => $this->GetKey()));
            // Obsolete CIs must be visible from the phone number
            $oCISet->SetShowObsoleteData(true);
            $aCISets[$sCIClass] = $oCISet;
        }

        return $aCISets;
    }

    /**
     * Get the list of CIs pointing to the phone number, together with the attribute pointing to it
     *
     * @return array
     * @throws \CoreException
     * @throws \OQLException
     */
    public function GetHostingCIs(): array
    {
        $aCIs = [];
        foreach (PhoneNumberUtils::GetListOfClassesWithPNs() as $sCIClass => $aPNAttributes) {
            foreach ($aPNAttributes as $sPNAttribute) {
                $sOQL = "SELECT $sCIClass AS c WHERE c.$sPNAttribute = :id";
                $oCISet = new CMDBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array('id' => $this->GetKey()));
                $oCISet->SetShowObsoleteData(true);
                while ($oCI = $oCISet->Fetch()) {
                    $aCIs[] = ['ci' => $oCI, 'pn_attribute' => $sPNAttribute];
                }
            }
        }

        return $aCIs;
    }

    /**
     * Handler for EVENT_DB_COMPUTE_VALUES event
     *   . Store number in E.164 format
     *   . Attach number to the smallest range of the organization that contains it, if no range is set yet
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPhoneNumberComputeValuesRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $sNumber = PhoneNumberUtils::Normalize($this->Get('number'));
        if ($sNumber != $this->Get('number')) {
            $this->Set('number', $sNumber);
        }

        if (($this->Get('tippnrange_id') == 0) && PhoneNumberUtils::IsE164($sNumber)) {
            $oPNRange = TipPNRange::FindSmallestRangeContaining($this->Get('org_id'), $sNumber);
            if ($oPNRange !== null) {
                $this->Set('tippnrange_id', $oPNRange->GetKey());
            }
        }
    }

    /**
     * Handler for EVENT_DB_CHECK_TO_WRITE event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPhoneNumberCheckToWriteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        // Number cannot be changed once created
        if (!$oEventData->Get('is_new')) {
            return;
        }

        // Make sure number is in E.164 format
        $sNumber = $this->Get('number');
        if (!PhoneNumberUtils::IsE164($sNumber)) {
            $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPhoneNumber:NotE164', $sNumber));
            return;
        }

        // If a range is selected, make sure number belongs to it
        $iPNRangeId = $this->Get('tippnrange_id');
        if ($iPNRangeId != 0) {
            $oPNRange = MetaModel::GetObject('TipPNRange', $iPNRangeId, false /* MustBeFound */);
            if (($oPNRange !== null) && !TipPNRange::IsInRange($sNumber, $oPNRange->Get('firstnumber'), $oPNRange->Get('lastnumber'))) {
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPhoneNumber:NotInRange', $sNumber, $oPNRange->GetName()));
            }
        }
    }

    /**
     * Handler for EVENT_DB_SET_ATTRIBUTES_FLAGS event
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPhoneNumberSetAttributesFlagsRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $this->AddAttributeFlags('org_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('tippnrange_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('number', OPT_ATT_READONLY);
    }

    /**
     * Handler for EVENT_DB_AFTER_WRITE event: update occupancy of the range(s) involved
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPhoneNumberAfterWriteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        if ($oEventData->Get('is_new')) {
            static::UpdateRangeOccupancy($this->Get('tippnrange_id'));
        } else {
            $aPreviousValues = $this->ListPreviousValuesForUpdatedAttributes();
            if (array_key_exists('tippnrange_id', $aPreviousValues)) {
                static::UpdateRangeOccupancy($aPreviousValues['tippnrange_id']);
                static::UpdateRangeOccupancy($this->Get('tippnrange_id'));
            }
        }
    }

    /**
     * Handler for EVENT_DB_AFTER_DELETE event: update occupancy of the range
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPhoneNumberAfterDeleteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        static::UpdateRangeOccupancy($this->Get('tippnrange_id'));
    }

    /**
     * Recompute occupancy of a range
     *
     * @param int|null $iPNRangeId
     * @return void
     */
    protected static function UpdateRangeOccupancy($iPNRangeId): void
    {
        if (empty($iPNRangeId)) {
            return;
        }
        $oPNRange = MetaModel::GetObject('TipPNRange', $iPNRangeId, false /* MustBeFound */);
        if ($oPNRange !== null) {
            $oPNRange->Set('occupancy', $oPNRange->GetOccupancy());
            $oPNRange->DBUpdate();
        }
    }

    /**
     * @inheritdoc
     */
    public static function GetShortcutActions($sFinalClass)
    {
        // Prepend the shortcut actions with the navigation menu
        $aNavigationActions = ['previous_phonenumber', 'next_phonenumber'];
        $aConfiguredActions = parent::GetShortcutActions($sFinalClass);

        return array_merge($aNavigationActions, $aConfiguredActions);
    }

    /**
     * Get the previous TipPhoneNumber if it exists
     *
     * @param bool $bInRange if lookup should be done in number's range only
     *
     * @return \TipPhoneNumber|null
     */
    public function GetPreviousNumber($bInRange)
    {
        return $this->GetNeighbourNumber($bInRange, false);
    }

    /**
     * Get the next TipPhoneNumber if it exists
     *
     * @param bool $bInRange if lookup should be done in number's range only
     *
     * @return \TipPhoneNumber|null
     */
    public function GetNextNumber($bInRange)
    {
        return $this->GetNeighbourNumber($bInRange, true);
    }

    /**
     * Get the previous or next TipPhoneNumber of the same length, if it exists
     *
     * @param bool $bInRange
     * @param bool $bNext
     *
     * @return \TipPhoneNumber|null
     */
    protected function GetNeighbourNumber($bInRange, $bNext)
    {
        $sOperator = $bNext ? '>' : '<';
        $iPNRangeId = $this->Get('tippnrange_id');
        if ($bInRange) {
            if ($iPNRangeId == 0) {
                return null;
            }
            $sOQL = "SELECT TipPhoneNumber AS p WHERE p.tippnrange_id = :tippnrange_id AND p.number $sOperator :number";
        } else {
            $sOQL = "SELECT TipPhoneNumber AS p WHERE p.org_id = :org_id AND p.number $sOperator :number";
        }
        // Numbers within a range all have the same length. Outside of ranges, skip numbers of different length.
        $sNumber = $this->Get('number');
        $oPhoneNumberSet = new DBObjectSet(DBSearch::FromOQL($sOQL), ['number' => $bNext], [
            'org_id' => $this->Get('org_id'),
            'tippnrange_id' => $iPNRangeId,
            'number' => $sNumber,
        ]);
        $oPhoneNumberSet->OptimizeColumnLoad(['TipPhoneNumber' => ['id', 'number']]);
        while ($oPhoneNumber = $oPhoneNumberSet->Fetch()) {
            if (PhoneNumberUtils::IsSameLength($sNumber, $oPhoneNumber->Get('number'))) {
                return $oPhoneNumber;
            }
        }

        return null;
    }

}
