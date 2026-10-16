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
					// Additional actions for TipPNRange: switch between list and tree display
					$oAppContext = new ApplicationContext();
					$aParams = $oAppContext->GetAsHash();
					$aResult[] = new SeparatorPopupMenuItem();
					if (utils::ReadParam('operation', '') == 'displaytree') {
						// Serialized filter is already url encoded and DBSearch::unserialize() decodes it again
						$aParams['operation'] = 'search';
						$aParams['filter'] = $oSet->GetFilter()->serialize();
						$sMenu = 'UI:PNManagement:Action:DisplayList:TipPNRange';
						$aResult[] = new URLPopupMenuItem($sMenu, Dict::S($sMenu), utils::GetAbsoluteUrlAppRoot().'pages/UI.php?'.http_build_query($aParams));
					} else {
						$aParams['operation'] = 'displaytree';
						$sMenu = 'UI:PNManagement:Action:DisplayTree:TipPNRange';
						$aResult[] = new URLPopupMenuItem($sMenu, Dict::S($sMenu), utils::GetAbsoluteUrlModulePage(static::MODULE_CODE, 'ui.teemip-phone-number-mgmt.php', $aParams));
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

                    // Allocate number to a CI or un-allocate it from all CIs, depending on its status
                    if (UserRights::IsActionAllowed('TipPhoneNumber', UR_ACTION_MODIFY) == UR_ALLOWED_YES) {
                        $oAppContext = new ApplicationContext();
                        $aParams = $oAppContext->GetAsHash();
                        $aParams['class'] = 'TipPhoneNumber';
                        $aParams['id'] = $oObj->GetKey();
                        if ($oObj->Get('status') == 'allocated') {
                            $aParams['operation'] = 'unallocatenumber';
                            $sMenu = 'UI:PNManagement:Action:UnAllocate:TipPhoneNumber';
                        } else {
                            $aParams['operation'] = 'allocatenumber';
                            $sMenu = 'UI:PNManagement:Action:Allocate:TipPhoneNumber';
                        }
                        $aResult[] = new SeparatorPopupMenuItem();
                        $aResult[] = new URLPopupMenuItem($sMenu, Dict::S($sMenu), utils::GetAbsoluteUrlModulePage(static::MODULE_CODE, 'ui.teemip-phone-number-mgmt.php', $aParams));
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
