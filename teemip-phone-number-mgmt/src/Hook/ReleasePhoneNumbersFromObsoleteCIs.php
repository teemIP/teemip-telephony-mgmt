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
use TipPhoneNumber;

/**
 * Background process that releases the allocated phone numbers whose CIs all have a status belonging to a given list (obsolete by default).
 * Links between CIs and phone numbers are kept.
 */
class ReleasePhoneNumbersFromObsoleteCIs implements iScheduledProcess
{
	const MODULE_CODE = 'teemip-phone-number-mgmt';
	const FUNCTION_CODE = 'pn_release_on_ci_status';
	const FUNCTION_SETTING_ENABLED = 'enabled';
	const FUNCTION_SETTING_DEBUG = 'debug';
	const FUNCTION_SETTING_PERIODICITY = 'periodicity';
	const FUNCTION_SETTING_STATUS_LIST = 'status_list';

	const DEFAULT_FUNCTION_SETTING_ENABLED = false;
	const DEFAULT_FUNCTION_SETTING_DEBUG = false;
	const DEFAULT_FUNCTION_SETTING_PERIODICITY = 3600;
	const DEFAULT_FUNCTION_SETTING_STATUS_LIST = array('obsolete');

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
	 * Check that all CIs hosting the phone number have a status belonging to the list.
	 * CIs of classes without status are never considered as obsolete.
	 *
	 * @param TipPhoneNumber $oPhoneNumber
	 * @param array $aStatusList
	 * @return bool
	 * @throws \CoreException
	 * @throws \OQLException
	 */
	private function CheckToRelease(TipPhoneNumber $oPhoneNumber, array $aStatusList): bool
	{
		foreach ($oPhoneNumber->GetHostingCIs() as $aHostingCI) {
			$oCI = $aHostingCI['ci'];
			if (!MetaModel::IsValidAttCode(get_class($oCI), 'status') || !in_array($oCI->Get('status'), $aStatusList)) {
				return false;
			}
		}

		return true;
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

		// Don't run if CI statuses are shared with the allocation task, as numbers would be allocated and released at each run
		$aConflictingStatus = PhoneNumberUtils::GetStatusConflictsBetweenAllocationAndRelease();
		if (!empty($aConflictingStatus)) {
			return 'Status lists of pn_allocate_on_ci_status and pn_release_on_ci_status share the following status: '.implode(', ', $aConflictingStatus).'. Execution has not run.';
		}

		// Keep only valid status codes, as they are inserted in OQL queries
		$aValidStatusList = array_filter($aStatusList, function ($sStatus) {
			return is_string($sStatus) && (preg_match('/^[A-Za-z0-9_]+$/', $sStatus) === 1);
		});
		if (count($aValidStatusList) != count($aStatusList)) {
			$this->Trace('Some status of the status list are invalid and have been ignored.');
		}
		if (empty($aValidStatusList)) {
			$this->Trace('Status list is empty. Exiting...');

			return '';
		}
		$sStatusList = "('".implode("', '", $aValidStatusList)."')";

		CMDBObject::SetTrackInfo('Automatic - Background task to release phone numbers from obsolete CIs');
		CMDBObject::SetTrackOrigin('custom-extension');

		// Build OQL listing the allocated phone numbers attached to at least one CI with a listed status
		$aOQLs = [];
		foreach (PhoneNumberUtils::GetListOfClassesWithPNs() as $sClass => $aPNAttributes) {
			if (!MetaModel::IsValidAttCode($sClass, 'status')) {
				continue;
			}
			foreach ($aPNAttributes as $sPNAttribute) {
				$aOQLs[] = "SELECT TipPhoneNumber AS pn JOIN $sClass AS ci ON ci.$sPNAttribute = pn.id WHERE pn.status = 'allocated' AND ci.status IN $sStatusList";
			}
		}
		if (empty($aOQLs)) {
			$this->Trace('No CI with a status has external keys toward phone numbers. Exiting...');

			return '';
		}

		// Release phone numbers whose CIs all have a listed status - release_date is managed at TipPNObject level
		$iNbReleased = 0;
		$oSearch = DBObjectSearch::FromOQL(implode(' UNION ', $aOQLs));
		$oSearch->AllowAllData();
		$oPhoneNumberSet = new CMDBObjectSet($oSearch);
		while ((time() < $iUnixTimeLimit) && ($oPhoneNumber = $oPhoneNumberSet->Fetch())) {
			try {
				if ($this->CheckToRelease($oPhoneNumber, $aValidStatusList)) {
					$oPhoneNumber->Set('status', 'released');
					$oPhoneNumber->DBUpdate();
					$iNbReleased++;
				}
			} catch (Exception $e) {
				$this->Trace('Skipping phone number '.$oPhoneNumber->GetName().' as there was an exception! ('.$e->getMessage().')');
			}
		}

		// Info to help understand why not all objects have been processed during this batch.
		if (time() >= $iUnixTimeLimit) {
			$this->Trace('Stopped because time limit exceeded!');
		}

		return ($iNbReleased === 0) ? "\nNo phone number has been released\n" : "\n".$iNbReleased." phone numbers have been released.\n";
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
