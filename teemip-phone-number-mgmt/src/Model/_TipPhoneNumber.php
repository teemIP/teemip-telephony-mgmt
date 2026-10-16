<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Model;

use ApplicationContext;
use cmdbAbstractObject;
use CMDBObjectSet;
use Combodo\iTop\Application\UI\Base\Component\Button\ButtonUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Form\FormUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Html\HtmlFactory;
use Combodo\iTop\Application\UI\Base\Component\Input\InputUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Input\Select\SelectOptionUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Input\Select\SelectUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Toolbar\ToolbarUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Layout\MultiColumn\Column\Column;
use Combodo\iTop\Application\UI\Base\Layout\MultiColumn\MultiColumn;
use Combodo\iTop\Application\UI\Base\Layout\Object\ObjectFactory;
use Combodo\iTop\Application\UI\Base\Layout\PageContent\PageContentFactory;
use Combodo\iTop\Application\UI\Base\Layout\UIContentBlock;
use Combodo\iTop\Application\UI\Base\Layout\UIContentBlockUIBlockFactory;
use Combodo\iTop\Service\Events\EventData;
use DBObjectSearch;
use DBObjectSet;
use DBSearch;
use Dict;
use DisplayBlock;
use iTopWebPage;
use MetaModel;
use TeemIp\TeemIp\Extension\Framework\Helper\IPUtils;
use TipPNObject;
use TipPNRange;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper\PhoneNumberUtils;
use UserRights;
use utils;
use WebPage;

class _TipPhoneNumber extends TipPNObject {
    /**
     * @inheritdoc
     */
    public function DisplayBareRelations($oPage, $bEditMode = false)
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
     * Check if phone number can be unallocated from all CIs
     *
     * @return string error message, empty if number can be unallocated
     * @throws \CoreException
     * @throws \OQLException
     */
    public function DoCheckToUnallocate(): string
    {
        // Make sure number is allocated
        if ($this->Get('status') != 'allocated') {
            return Dict::S('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:NumberNotAllocated');
        }

        // Check if number is attached to at least one CI's attribute that cannot be emptied
        foreach ($this->GetHostingCIs() as $aHostingCI) {
            $oCI = $aHostingCI['ci'];
            $sPNAttribute = $aHostingCI['pn_attribute'];
            $iFlags = $oCI->GetAttributeFlags($sPNAttribute);
            if ($iFlags & OPT_ATT_SLAVE) {
                // Attribute is read only because of a synchro
                return Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:AttributeIsSynchronized', $oCI->GetName());
            }
            if ($iFlags & OPT_ATT_READONLY) {
                return Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:AttributeIsReadOnly', $oCI->GetName());
            }
            if (!MetaModel::GetAttributeDef(get_class($oCI), $sPNAttribute)->IsNullAllowed()) {
                return Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:AttributeIsMandatory', $oCI->GetName());
            }
        }

        return '';
    }

    /**
     * Unallocate phone number from all CIs and release it
     *
     * @return void
     * @throws \CoreCannotSaveObjectException
     * @throws \CoreException
     * @throws \CoreUnexpectedValue
     * @throws \OQLException
     */
    public function DoUnallocate(): void
    {
        // Remove from CIs
        foreach ($this->GetHostingCIs() as $aHostingCI) {
            $oCI = $aHostingCI['ci'];
            $oCI->Set($aHostingCI['pn_attribute'], 0);
            $oCI->DBUpdate();
        }

        // Update number status - release_date is managed at TipPNObject level
        $this->Set('status', 'released');
        $this->DBUpdate();
    }

    /**
     * Get the classes of CIs that the number can be allocated to: classes with phone number attributes that have instances in the organization
     *
     * @param int $iOrgId
     * @return array label of the class, indexed by class
     * @throws \CoreException
     * @throws \OQLException
     */
    public static function GetCIClassesToAllocate($iOrgId): array
    {
        $aCIClasses = [];
        foreach (array_keys(PhoneNumberUtils::GetListOfClassesWithPNs()) as $sCIClass) {
            $oCISet = new CMDBObjectSet(DBObjectSearch::FromOQL("SELECT $sCIClass AS ci WHERE ci.org_id = :org_id"), [], ['org_id' => $iOrgId]);
            $oCISet->SetShowObsoleteData(utils::ShowObsoleteData());
            if ($oCISet->CountExceeds(0)) {
                $aCIClasses[$sCIClass] = MetaModel::GetName($sCIClass);
            }
        }

        return $aCIClasses;
    }

    /**
     * Build the select box listing the CIs of a class within an organization
     *
     * @param int $iOrgId
     * @param string $sCIClass
     * @param string $sInputId
     * @param int $iDefault id of the selected CI, if any
     * @return \Combodo\iTop\Application\UI\Base\Component\Input\Select\Select
     * @throws \CoreException
     * @throws \OQLException
     */
    public static function MakeCISelect($iOrgId, string $sCIClass, string $sInputId, $iDefault = 0)
    {
        $oSelect = SelectUIBlockFactory::MakeForSelect('attr_ci_id', $sInputId);
        if (!array_key_exists($sCIClass, PhoneNumberUtils::GetListOfClassesWithPNs())) {
            return $oSelect;
        }
        $oCISet = new CMDBObjectSet(DBObjectSearch::FromOQL("SELECT $sCIClass AS ci WHERE ci.org_id = :org_id"), ['friendlyname' => true], ['org_id' => $iOrgId]);
        $oCISet->SetShowObsoleteData(utils::ShowObsoleteData());
        while ($oCI = $oCISet->Fetch()) {
            $oSelect->AddOption(SelectOptionUIBlockFactory::MakeForSelectOption($oCI->GetKey(), $oCI->GetName(), ($oCI->GetKey() == $iDefault)));
        }

        return $oSelect;
    }

    /**
     * Build the select box listing the phone number attributes of a class
     *
     * @param string $sCIClass
     * @param string $sInputId
     * @param string $sDefault code of the selected attribute, if any
     * @return \Combodo\iTop\Application\UI\Base\Component\Input\Select\Select
     * @throws \CoreException
     */
    public static function MakePNAttributeSelect(string $sCIClass, string $sInputId, string $sDefault = '')
    {
        $oSelect = SelectUIBlockFactory::MakeForSelect('attr_pnattribute', $sInputId);
        $aClassesWithPNs = PhoneNumberUtils::GetListOfClassesWithPNs();
        if (!array_key_exists($sCIClass, $aClassesWithPNs)) {
            return $oSelect;
        }
        foreach ($aClassesWithPNs[$sCIClass] as $sPNAttribute) {
            $sLabel = MetaModel::GetAttributeDef($sCIClass, $sPNAttribute)->GetLabel();
            $oSelect->AddOption(SelectOptionUIBlockFactory::MakeForSelectOption($sPNAttribute, $sLabel, ($sPNAttribute == $sDefault)));
        }

        return $oSelect;
    }

    /**
     * Display the form to allocate the number to a CI
     *
     * @param iTopWebPage $oP
     * @param ApplicationContext $oAppContext
     * @param array $aDefault previously posted parameters (ciclass, ci_id, pnattribute), if any
     * @return void
     * @throws \ArchivedObjectException
     * @throws \CoreException
     * @throws \OQLException
     */
    public function DisplayAllocateForm(iTopWebPage $oP, ApplicationContext $oAppContext, array $aDefault = []): void
    {
        $id = $this->GetKey();
        $sClass = get_class($this);
        $sClassLabel = MetaModel::GetName($sClass);
        $iOrgId = $this->Get('org_id');

        // Prepare form
        $sUITitle = Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:PageTitle_Object_Class', $this->GetName(), $sClassLabel);
        $oP->SetBreadCrumbEntry($sUITitle, $sUITitle, '', '', 'fas fa-wrench', iTopWebPage::ENUM_BREADCRUMB_ENTRY_ICON_TYPE_CSS_CLASSES);
        $oP->set_title($sUITitle);

        $iTransactionId = utils::GetNewTransactionId();
        $oP->SetTransactionId($iTransactionId);

        $oP->SetContentLayout(PageContentFactory::MakeForObjectDetails($this, cmdbAbstractObject::ENUM_DISPLAY_MODE_VIEW));
        $oContentBlock = new UIContentBlock();
        $oP->AddUiBlock($oContentBlock);

        $oForm = FormUIBlockFactory::MakeStandard();
        $oContentBlock->AddSubBlock($oForm);
        $oForm->AddHtml($oAppContext->GetForForm())
            ->AddSubBlock(InputUIBlockFactory::MakeForHidden('operation', 'doallocatenumber'))
            ->AddSubBlock(InputUIBlockFactory::MakeForHidden('class', $sClass))
            ->AddSubBlock(InputUIBlockFactory::MakeForHidden('id', $id))
            ->AddSubBlock(InputUIBlockFactory::MakeForHidden('transaction_id', $iTransactionId));

        $oToolbarButtons = ToolbarUIBlockFactory::MakeStandard(null);
        $oCancelButton = ButtonUIBlockFactory::MakeForCancel(Dict::S('UI:Button:Cancel'), 'cancel', 'cancel')->SetOnClickJsCode("BackToDetails('$sClass', '$id', '', '{null}');");
        $oCancelButton->AddCSSClasses(['action', 'cancel']);
        $oToolbarButtons->AddSubBlock($oCancelButton);

        $oObjectDetails = ObjectFactory::MakeDetails($this);
        $oToolbarButtons->AddCSSClass('ibo-toolbar-top');
        $oObjectDetails->AddToolbarBlock($oToolbarButtons);
        $oForm->AddSubBlock($oObjectDetails);

        $oP->AddTabContainer(OBJECT_PROPERTIES_TAB, '', $oObjectDetails);
        $oP->SetCurrentTabContainer(OBJECT_PROPERTIES_TAB);
        $oP->SetCurrentTab(Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber'));

        // Action fields
        $aCIClasses = static::GetCIClassesToAllocate($iOrgId);
        if (empty($aCIClasses)) {
            $oP->p(Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:NoCI'));
        } else {
            $oApplyButton = ButtonUIBlockFactory::MakeForPrimaryAction(Dict::S('UI:Button:Apply'), null, null, true);
            $oApplyButton->AddCSSClass('action');
            $oToolbarButtons->AddSubBlock($oApplyButton);

            $sDefaultCIClass = $aDefault['ciclass'] ?? '';
            if (!array_key_exists($sDefaultCIClass, $aCIClasses)) {
                $sDefaultCIClass = array_key_first($aCIClasses);
            }

            $oMultiColumn = new MultiColumn();
            $oP->AddUiBlock($oMultiColumn);
            $oColumn1 = new Column();
            $oMultiColumn->AddColumn($oColumn1);
            $oColumn2 = new Column();
            $oMultiColumn->AddColumn($oColumn2);

            // Target class
            $iFormId = $oObjectDetails->GetId();
            $sClassInputId = 'field_'.$iFormId.'_ciclass';
            $oColumn1->AddSubBlock(HtmlFactory::MakeParagraph(Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:Class')));
            $oColumn1->AddSubBlock(HtmlFactory::MakeRaw('<br>'));
            $oSelect = SelectUIBlockFactory::MakeForSelect('attr_ciclass', $sClassInputId);
            foreach ($aCIClasses as $sCIClass => $sCIClassLabel) {
                $oSelect->AddOption(SelectOptionUIBlockFactory::MakeForSelectOption($sCIClass, $sCIClassLabel, ($sCIClass == $sDefaultCIClass)));
            }
            $oColumn2->AddSubBlock($oSelect);
            $oColumn2->AddSubBlock(HtmlFactory::MakeRaw('<br><br>'));

            // Functional CI
            $sCIInputId = 'field_'.$iFormId.'_ci_id';
            $oColumn1->AddSubBlock(HtmlFactory::MakeParagraph(Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:CI')));
            $oColumn1->AddSubBlock(HtmlFactory::MakeRaw('<br>'));
            $oCIBlock = UIContentBlockUIBlockFactory::MakeStandard('v_ci_id', ['field_select_wrapper']);
            $oCIBlock->AddSubBlock(static::MakeCISelect($iOrgId, $sDefaultCIClass, $sCIInputId, $aDefault['ci_id'] ?? 0));
            $oColumn2->AddSubBlock($oCIBlock);
            $oColumn2->AddSubBlock(HtmlFactory::MakeRaw('<br>'));

            // Phone number attribute
            $sAttributeInputId = 'field_'.$iFormId.'_pnattribute';
            $oColumn1->AddSubBlock(HtmlFactory::MakeParagraph(Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:PNAttribute')));
            $oAttributeBlock = UIContentBlockUIBlockFactory::MakeStandard('v_att_id', ['field_select_wrapper']);
            $oAttributeBlock->AddSubBlock(static::MakePNAttributeSelect($sDefaultCIClass, $sAttributeInputId, $aDefault['pnattribute'] ?? ''));
            $oColumn2->AddSubBlock($oAttributeBlock);

            // Refresh list of CIs and attributes when class changes
            $sAjaxUrl = utils::GetAbsoluteUrlModulePage(PhoneNumberUtils::MODULE_CODE, 'ajax.teemip-phone-number-mgmt.php');
            $oP->add_ready_script(<<<JS
$('#$sClassInputId').on('change', function() {
    var sCIClass = $(this).val();
    $.post('$sAjaxUrl', {operation: 'get_cis_to_allocate', vid: '$sCIInputId', org_id: '$iOrgId', ciclass: sCIClass}, function(data) { $('#v_ci_id').html(data); });
    $.post('$sAjaxUrl', {operation: 'get_pnattributes_to_allocate', vid: '$sAttributeInputId', ciclass: sCIClass}, function(data) { $('#v_att_id').html(data); });
});
JS
            );
        }

        $oP->add_ready_script("$(window).on('unload', function() { return OnUnload('$iTransactionId', '$sClass', $id) });");
    }

    /**
     * Check if number can be allocated to the given CI
     *
     * @param array $aParam ciclass, ci_id and pnattribute
     * @return string error message, empty if number can be allocated
     * @throws \ArchivedObjectException
     * @throws \CoreException
     */
    public function DoCheckToAllocate(array $aParam): string
    {
        if ($this->Get('status') == 'allocated') {
            return Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:NumberAlreadyAllocated');
        }

        $sCIClass = $aParam['ciclass'];
        $sPNAttribute = $aParam['pnattribute'];
        $aClassesWithPNs = PhoneNumberUtils::GetListOfClassesWithPNs();
        if (!array_key_exists($sCIClass, $aClassesWithPNs) || !in_array($sPNAttribute, $aClassesWithPNs[$sCIClass])) {
            return Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:WrongAttribute');
        }

        $oCI = MetaModel::GetObject($sCIClass, $aParam['ci_id'], false /* MustBeFound */);
        if (is_null($oCI) || ($oCI->Get('org_id') != $this->Get('org_id'))) {
            return Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:CIDoesNotExist');
        }
        if (UserRights::IsActionAllowed($sCIClass, UR_ACTION_MODIFY, DBObjectSet::FromObject($oCI)) != UR_ALLOWED_YES) {
            return Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:CINotModifiable', $oCI->GetName());
        }

        $aReasons = [];
        $iFlags = $oCI->GetAttributeFlags($sPNAttribute, $aReasons);
        if ($iFlags & OPT_ATT_SLAVE) {
            // Attribute is read only because of a synchro
            return Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:AttributeIsSynchronized', $oCI->GetName());
        }
        if ($iFlags & OPT_ATT_READONLY) {
            return Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:AttributeIsReadOnly', $oCI->GetName());
        }

        return '';
    }

    /**
     * Allocate number to a CI
     *
     * @param array $aParam ciclass, ci_id and pnattribute
     * @return void
     * @throws \ArchivedObjectException
     * @throws \CoreCannotSaveObjectException
     * @throws \CoreException
     * @throws \CoreUnexpectedValue
     */
    public function DoAllocate(array $aParam): void
    {
        // Attach number to CI
        $oCI = MetaModel::GetObject($aParam['ciclass'], $aParam['ci_id'], true /* MustBeFound */);
        $oCI->Set($aParam['pnattribute'], $this->GetKey());
        $oCI->DBUpdate();

        // Update number status - allocation_date is managed at TipPNObject level
        $this->Set('status', 'allocated');
        $this->DBUpdate();
    }

    /**
     * Handler for EVENT_DB_COMPUTE_VALUES event
     *   . Store number in E.164 format
     *   . At creation, prefill number with the prefix of the selected range
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

        $aEventData = $oEventData->GetEventData();
        if ($aEventData['is_new']) {
            $this->PrefillNumberWithRangePrefix();
            $sNumber = $this->Get('number');
        }

        if (($this->Get('tippnrange_id') == 0) && PhoneNumberUtils::IsE164($sNumber)) {
            $oPNRange = TipPNRange::FindSmallestRangeContaining($this->Get('org_id'), $sNumber);
            if ($oPNRange !== null) {
                $this->Set('tippnrange_id', $oPNRange->GetKey());
            }
        }
    }

    /**
     * Prefill number with the prefix shared by all numbers of the selected range.
     * Number is only replaced when it is empty or incomplete (shorter than the range's numbers) and does not already start with the prefix.
     * As number depends on tippnrange_id, this is also applied when the range is changed in the creation form.
     *
     * @return void
     */
    protected function PrefillNumberWithRangePrefix(): void
    {
        $iPNRangeId = $this->Get('tippnrange_id');
        if ($iPNRangeId == 0) {
            return;
        }
        $oPNRange = MetaModel::GetObject('TipPNRange', $iPNRangeId, false /* MustBeFound */);
        if ($oPNRange === null) {
            return;
        }
        $sPrefix = $oPNRange->GetPrefix();
        if ($sPrefix == '') {
            return;
        }
        $sNumber = $this->Get('number');
        if (($sNumber == '') || ((strlen($sNumber) < strlen($oPNRange->Get('firstnumber'))) && !str_starts_with($sNumber, $sPrefix))) {
            $this->Set('number', $sPrefix);
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
                $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPhoneNumber:NotInRange', $sNumber, $oPNRange->GetName(), $oPNRange->Get('firstnumber'), $oPNRange->Get('lastnumber')));
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
