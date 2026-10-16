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
 * Background process that releases the allocated phone numbers that are not attached to any CI
 */
class ReleasePhoneNumbersWithNoCI implements iScheduledProcess
{
	const MODULE_CODE = 'teemip-phone-number-mgmt';
	const FUNCTION_CODE = 'pn_release_on_no_ci';
	const FUNCTION_SETTING_ENABLED = 'enabled';
	const FUNCTION_SETTING_DEBUG = 'debug';
	const FUNCTION_SETTING_PERIODICITY = 'periodicity';
	const FUNCTION_SETTING_TARGET_STATUS = 'target_status';

	const DEFAULT_FUNCTION_SETTING_ENABLED = false;
	const DEFAULT_FUNCTION_SETTING_DEBUG = false;
	const DEFAULT_FUNCTION_SETTING_PERIODICITY = 3600;
	const DEFAULT_FUNCTION_SETTING_TARGET_STATUS = 'released';

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
			static::FUNCTION_SETTING_TARGET_STATUS => static::DEFAULT_FUNCTION_SETTING_TARGET_STATUS,
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
			$aFunctionSettings[static::FUNCTION_SETTING_TARGET_STATUS] ?? static::DEFAULT_FUNCTION_SETTING_TARGET_STATUS,
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
		list ($bEnabled, , $sTargetStatus) = $this->GetSetting();
		if (!$bEnabled) {
			return 'Process being disabled for the time being, execution has not run.';
		}

		// Check target status of phone numbers
		if (!in_array($sTargetStatus, array('released', 'unassigned'))) {
			$this->Trace('Target status for phone numbers to be released is incorrect. Exiting...');

			return '';
		}

		CMDBObject::SetTrackInfo('Automatic - Background task to release phone numbers with no CI');
		CMDBObject::SetTrackOrigin('custom-extension');

		// Build OQL listing the phone numbers attached to at least one CI
		// If no class can host a phone number, do nothing rather than releasing all allocated numbers
		$aClassesWithPNs = PhoneNumberUtils::GetListOfClassesWithPNs();
		if (empty($aClassesWithPNs)) {
			$this->Trace('No CI has external keys toward phone numbers. Exiting...');

			return '';
		}
		$aOQLs = [];
		foreach ($aClassesWithPNs as $sClass => $aPNAttributes) {
			foreach ($aPNAttributes as $sPNAttribute) {
				$aOQLs[] = "SELECT TipPhoneNumber AS pn JOIN $sClass AS ci ON ci.$sPNAttribute = pn.id";
			}
		}

		// Get allocated phone numbers not attached to any CI and set their status accordingly - release_date is managed at TipPNObject level
		$iNbCorrected = 0;
		$oSearch = DBObjectSearch::FromOQL("SELECT TipPhoneNumber WHERE status = 'allocated' AND id NOT IN (".implode(' UNION ', $aOQLs).")");
		$oSearch->AllowAllData();
		$oPhoneNumberSet = new CMDBObjectSet($oSearch);
		while ((time() < $iUnixTimeLimit) && ($oPhoneNumber = $oPhoneNumberSet->Fetch())) {
			try {
				$oPhoneNumber->Set('status', $sTargetStatus);
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

		return ($iNbCorrected === 0) ? "\nNo phone number has been released\n" : "\n".$iNbCorrected." phone numbers have been released.\n";
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
