<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Hook;

use ApplicationContext;
use Dict;
use iPopupMenuExtension;
use MetaModel;
use TipPhoneNumber;
use TipPNRange;
use SeparatorPopupMenuItem;
use URLButtonItem;
use URLPopupMenuItem;
use UserRights;
use utils;

class PhoneNumberMgmtOtherActions implements iPopupMenuExtension
{
	const MODULE_CODE = 'teemip-phone-number-mgmt';
    const RANGE_NAV_FUNCTION_CODE = 'range_navigation';
	const RANGE_NAV_FUNCTION_SETTING_ENABLED = 'enabled';
	const RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY = 'within_range_only';
    const DEFAULT_RANGE_NAV_FUNCTION_SETTING_ENABLED = true;
	const DEFAULT_RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY = true;
	const NUMBER_NAV_FUNCTION_CODE = 'number_navigation';

	/**
	 * @inheritdoc
	 */
	public static function EnumItems($iMenuId, $param)
	{
		$aResult = array();
		switch ($iMenuId) {
			case iPopupMenuExtension::MENU_OBJLIST_ACTIONS: // $param is a DBObjectSet
				break;

			case iPopupMenuExtension::MENU_OBJLIST_TOOLKIT: // $param is a DBObjectSet
				$oSet = $param;
				$sClass = $oSet->GetClass();

				if ($sClass == 'TipPNRange') {
					// Additional actions for TipPNRange
					$operation = utils::ReadParam('operation', '');

                    $oAppContext = new ApplicationContext();
                    $iOrgId = $oAppContext->GetCurrentValue('org_id');
                    $sFilter = $param->GetFilter()->serialize();
 					switch ($operation) {
						case 'displaytree':
							$aResult[] = new SeparatorPopupMenuItem();
							$aParams['operation'] = 'displaylist';
							$sMenu = 'UI:PNManagement:Action:DisplayList:TipPNRange';
                            $aResult[] = new URLPopupMenuItem($sMenu, Dict::S($sMenu), utils::GetAbsoluteUrlAppRoot()."pages/UI.php?route=phone_number_mgmt.display_tree&params=$sParam");
							break;

						case 'displaylist':
						default:
							$aResult[] = new SeparatorPopupMenuItem();
							$sMenu = 'UI:PNManagement:Action:DisplayTree:TipPNRange';
                            $aResult[] = new URLPopupMenuItem($sMenu, Dict::S($sMenu), utils::GetAbsoluteUrlAppRoot()."pages/UI.php?route=phone_number_mgmt.display_tree&org_id=$iOrgId&filter=$sFilter");
							break;
					}
				}
				break;

			case iPopupMenuExtension::MENU_OBJDETAILS_ACTIONS: // $param is a DBObject
                $oObj = $param;
                if ($oObj instanceof TipPNRange) {
                    // Display pointers to previous and next range according to configuration parameters
                    $aDefaultSettings = array(
                        static::RANGE_NAV_FUNCTION_SETTING_ENABLED => static::DEFAULT_RANGE_NAV_FUNCTION_SETTING_ENABLED,
                        static::RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY => static::DEFAULT_RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY,
                    );
                    $aFunctionSettings = MetaModel::GetModuleSetting(static::MODULE_CODE, static::RANGE_NAV_FUNCTION_CODE, $aDefaultSettings);
                    $bEnabled = (bool)$aFunctionSettings[static::RANGE_NAV_FUNCTION_SETTING_ENABLED];
                    if ($bEnabled) {
                        $bWithinRangeOnly = (bool)$aFunctionSettings[static::RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY];
                        $oPreviousPNRange = $oObj->GetPreviousRange($bWithinRangeOnly);
                        if (!is_null($oPreviousPNRange)) {
                            $slabel = Dict::S('UI:PNManagement:Action:DisplayPrevious:TipPNRange');
                            $id = $oPreviousPNRange->GetKey();
                            $oItem = new URLButtonItem('previous_pnrange', $slabel, utils::GetAbsoluteUrlAppRoot() . 'pages/UI.php?operation=details&class=TipPNRange&id=' . $id, '_top');
                            $oItem->SetIconClass('fas fa-caret-left');
                            $oItem->SetTooltip($slabel);
                            $aResult[] = $oItem;
                        }
                        $oNextPNRange = $oObj->GetNextRange($bWithinRangeOnly);
                        if (!is_null($oNextPNRange)) {
                            $slabel = Dict::S('UI:PNManagement:Action:DisplayNext:TipPNRange');
                            $id = $oNextPNRange->GetKey();
                            $oItem = new URLButtonItem('next_pnrange', $slabel, utils::GetAbsoluteUrlAppRoot() . 'pages/UI.php?operation=details&class=TipPNRange&id=' . $id, '_top');
                            $oItem->SetIconClass('fas fa-caret-right');
                            $oItem->SetTooltip($slabel);
                            $aResult[] = $oItem;
                        }
                    }
                } elseif ($oObj instanceof TipPhoneNumber) {
                    // Display pointers to previous and next number according to configuration parameters
                    $aDefaultSettings = array(
                        static::RANGE_NAV_FUNCTION_SETTING_ENABLED => static::DEFAULT_RANGE_NAV_FUNCTION_SETTING_ENABLED,
                        static::RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY => static::DEFAULT_RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY,
                    );
                    $aFunctionSettings = MetaModel::GetModuleSetting(static::MODULE_CODE, static::NUMBER_NAV_FUNCTION_CODE, $aDefaultSettings);
                    $bEnabled = (bool)$aFunctionSettings[static::RANGE_NAV_FUNCTION_SETTING_ENABLED];
                    if ($bEnabled) {
                        $bWithinRangeOnly = (bool)$aFunctionSettings[static::RANGE_NAV_FUNCTION_SETTING_WITHIN_RANGE_ONLY];
                        $oPreviousPhoneNumber = $oObj->GetPreviousNumber($bWithinRangeOnly);
                        if (!is_null($oPreviousPhoneNumber)) {
                            $slabel = Dict::S('UI:PNManagement:Action:DisplayPrevious:TipPhoneNumber');
                            $id = $oPreviousPhoneNumber->GetKey();
                            $oItem = new URLButtonItem('previous_phonenumber', $slabel, utils::GetAbsoluteUrlAppRoot() . 'pages/UI.php?operation=details&class=TipPhoneNumber&id=' . $id, '_top');
                            $oItem->SetIconClass('fas fa-caret-left');
                            $oItem->SetTooltip($slabel);
                            $aResult[] = $oItem;
                        }
                        $oNextPhoneNumber = $oObj->GetNextNumber($bWithinRangeOnly);
                        if (!is_null($oNextPhoneNumber)) {
                            $slabel = Dict::S('UI:PNManagement:Action:DisplayNext:TipPhoneNumber');
                            $id = $oNextPhoneNumber->GetKey();
                            $oItem = new URLButtonItem('next_phonenumber', $slabel, utils::GetAbsoluteUrlAppRoot() . 'pages/UI.php?operation=details&class=TipPhoneNumber&id=' . $id, '_top');
                            $oItem->SetIconClass('fas fa-caret-right');
                            $oItem->SetTooltip($slabel);
                            $aResult[] = $oItem;
                        }
                    }
                }
				break;

			case iPopupMenuExtension::MENU_DASHBOARD_ACTIONS: // $param is a Dashboard
				break;

			default:
				// Unknown type of menu, do nothing
				break;
		}

		return $aResult;
	}
}
