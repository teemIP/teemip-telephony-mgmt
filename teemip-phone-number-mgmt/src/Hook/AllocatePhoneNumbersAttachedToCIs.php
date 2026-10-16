<?php
/*
 * @copyright   Copyright (C) 2010-2026 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Hook;

use CMDBObject;
use CMDBObjectSet;
use DateTime;
use DBObjectSearch;
use Exception;
use iScheduledProcess;
use MetaModel;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper\PhoneNumberUtils;

/**
 * Background process that sets to 'allocated' the phone numbers attached to at least one CI whose status belongs to a given list.
 * For classes of CI without status, or if the list is empty, the status of the CI is not considered.
 */
class AllocatePhoneNumbersAttachedToCIs implements iScheduledProcess
{
	const MODULE_CODE = 'teemip-phone-number-mgmt';
	const FUNCTION_CODE = 'pn_allocate_on_ci_status';
	const FUNCTION_SETTING_ENABLED = 'enabled';
	const FUNCTION_SETTING_DEBUG = 'debug';
	const FUNCTION_SETTING_PERIODICITY = 'periodicity';
	const FUNCTION_SETTING_STATUS_LIST = 'status_list';

	const DEFAULT_FUNCTION_SETTING_ENABLED = false;
	const DEFAULT_FUNCTION_SETTING_DEBUG = false;
	const DEFAULT_FUNCTION_SETTING_PERIODICITY = 3600;
	const DEFAULT_FUNCTION_SETTING_STATUS_LIST = array('implementation', 'production');

	protected $aDefaultSettings = array();
	protected $bDebug;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->aDefaultSettings = array(
			static::FUNCTION_SETTING_ENABLED => static::DEFAULT_FUNCTION_SETTING_ENABLED,
			static::FUNCTION_SETTING_DEBUG => static::DEFAULT_FUNCTION_SETTING_DEBUG,
			static::FUNCTION_SETTING_PERIODICITY => static::DEFAULT_FUNCTION_SETTING_PERIODICITY,
			static::FUNCTION_SETTING_STATUS_LIST => static::DEFAULT_FUNCTION_SETTING_STATUS_LIST,
		);
		$aFunctionSettings = MetaModel::GetModuleSetting(static::MODULE_CODE, static::FUNCTION_CODE, $this->aDefaultSettings);
		$this->bDebug = (bool)($aFunctionSettings[static::FUNCTION_SETTING_DEBUG] ?? static::DEFAULT_FUNCTION_SETTING_DEBUG);
	}

	/**
	 * Read module settings and return parameters required for the process to run
	 *
	 * @return array
	 */
	private function GetSetting(): array
	{
		$aFunctionSettings = MetaModel::GetModuleSetting(static::MODULE_CODE, static::FUNCTION_CODE, $this->aDefaultSettings);

		return [
			(bool)($aFunctionSettings[static::FUNCTION_SETTING_ENABLED] ?? static::DEFAULT_FUNCTION_SETTING_ENABLED),
			(int)($aFunctionSettings[static::FUNCTION_SETTING_PERIODICITY] ?? static::DEFAULT_FUNCTION_SETTING_PERIODICITY),
			(array)($aFunctionSettings[static::FUNCTION_SETTING_STATUS_LIST] ?? static::DEFAULT_FUNCTION_SETTING_STATUS_LIST),
		];
	}

	/**
	 * Gives the exact time at which the process must be run next time
	 *
	 * @return \DateTime
	 * @throws \Exception
	 */
	public function GetNextOccurrence()
	{
		// Get module parameters and postpone next occurrence if function is disabled
		list ($bEnabled, $iPeriodicity) = $this->GetSetting();
		if (!$bEnabled) {
			$iPeriodicity = 86400;
		}
		$oRet = new DateTime();
		$oRet->modify($iPeriodicity.' seconds');

		return $oRet;
	}

	/**
	 * @inheritdoc
	 */
	public function Process($iUnixTimeLimit)
	{
		// Get module parameters and don't run the process if the module is disabled
		list ($bEnabled, , $aStatusList) = $this->GetSetting();
		if (!$bEnabled) {
			return 'Process being disabled for the time being, execution has not run.';
		}

		// Keep only valid status codes, as they are inserted in OQL queries
		$aValidStatusList = array_filter($aStatusList, function ($sStatus) {
			return is_string($sStatus) && (preg_match('/^[A-Za-z0-9_]+$/', $sStatus) === 1);
		});
		if (count($aValidStatusList) != count($aStatusList)) {
			$this->Trace('Some status of the status list are invalid and have been ignored.');
		}
		$sStatusList = empty($aValidStatusList) ? '' : "('".implode("', '", $aValidStatusList)."')";

		CMDBObject::SetTrackInfo('Automatic - Background task to allocate phone numbers attached to CIs');
		CMDBObject::SetTrackOrigin('custom-extension');

		// Build OQL listing the non allocated phone numbers attached to at least one CI with a listed status
		$aClassesWithPNs = PhoneNumberUtils::GetListOfClassesWithPNs();
		if (empty($aClassesWithPNs)) {
			$this->Trace('No CI has external keys toward phone numbers. Exiting...');

			return '';
		}
		$aOQLs = [];
		foreach ($aClassesWithPNs as $sClass => $aPNAttributes) {
			$sStatusCondition = (($sStatusList != '') && MetaModel::IsValidAttCode($sClass, 'status')) ? " AND ci.status IN $sStatusList" : '';
			foreach ($aPNAttributes as $sPNAttribute) {
				$aOQLs[] = "SELECT TipPhoneNumber AS pn JOIN $sClass AS ci ON ci.$sPNAttribute = pn.id WHERE pn.status != 'allocated'$sStatusCondition";
			}
		}

		// Correct status of phone numbers - allocation_date is managed at TipPNObject level
		$iNbCorrected = 0;
		$oSearch = DBObjectSearch::FromOQL(implode(' UNION ', $aOQLs));
		$oSearch->AllowAllData();
		$oPhoneNumberSet = new CMDBObjectSet($oSearch);
		while ((time() < $iUnixTimeLimit) && ($oPhoneNumber = $oPhoneNumberSet->Fetch())) {
			try {
				$oPhoneNumber->Set('status', 'allocated');
				$oPhoneNumber->DBUpdate();
				$iNbCorrected++;
			} catch (Exception $e) {
				$this->Trace('Skipping phone number '.$oPhoneNumber->GetName().' as there was an exception! ('.$e->getMessage().')');
			}
		}

		// Info to help understand why not all objects have been processed during this batch.
		if (time() >= $iUnixTimeLimit) {
			$this->Trace('Stopped because time limit exceeded!');
		}

		return ($iNbCorrected === 0) ? "\nNo phone number has been allocated\n" : "\n".$iNbCorrected." phone numbers have been allocated.\n";
	}

	/**
	 * Prints a $sMessage in the CRON output.
	 *
	 * @param string $sMessage
	 */
	protected function Trace($sMessage)
	{
		if ($this->bDebug) {
			echo $sMessage."\n";
		}
	}
}
