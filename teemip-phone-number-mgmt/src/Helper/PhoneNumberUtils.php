<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper;

use DBObjectSearch;
use DBObjectSet;
use MetaModel;
use SetupUtils;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Hook\AllocatePhoneNumbersAttachedToCIs;
use TeemIp\TeemIp\Extension\PhoneNumberManagement\Hook\ReleasePhoneNumbersFromObsoleteCIs;
use utils;

/**
 * Helpers to handle phone numbers stored in E.164 format (ITU-T E.164: '+' followed by up to 15 digits)
 */
class PhoneNumberUtils
{
    const E164_PATTERN = '/^\+[1-9][0-9]{1,14}$/';

    const MODULE_CODE = 'teemip-phone-number-mgmt';
    const DEVELOPER_MODE_FUNCTION_CODE = 'developer_mode.enabled';
    const TEEMIP_CACHE_DIR = 'teemip';
    const LIST_CLASSES_WITH_PNS_FILE_NAME = 'ListOfClassesWithPNs.php';

    /** @var array|null Calling code per TipPNCountryCode id, loaded once per request */
    protected static ?array $aCallingCodes = null;
    /** @var array|null List of display formats per TipPNCountryCode id, loaded once per request */
    protected static ?array $aFormats = null;

    /**
     * Convert a phone number typed in international format into E.164.
     *   . Separators (space, dot, dash, slash, parenthesis) are removed
     *   . The '(0)' trunk prefix sometimes inserted after the country code is removed
     *   . The '00' international prefix is replaced by '+'
     * Numbers in national format are returned without '+' and will therefore be rejected by IsE164().
     *
     * @param string|null $sNumber
     * @return string
     */
    public static function Normalize(?string $sNumber): string
    {
        if ($sNumber === null) {
            return '';
        }
        $sNumber = str_replace('(0)', '', trim($sNumber));
        $sNumber = preg_replace('/[\s.\-\/()]/', '', $sNumber);
        if (str_starts_with($sNumber, '00')) {
            $sNumber = '+'.substr($sNumber, 2);
        }

        return $sNumber;
    }

    /**
     * Check if number complies with E.164 format
     *
     * @param string $sNumber
     * @return bool
     */
    public static function IsE164(string $sNumber): bool
    {
        return (preg_match(static::E164_PATTERN, $sNumber) === 1);
    }

    /**
     * Check if number starts with the given country calling code
     *
     * @param string $sNumber E.164 number
     * @param string $sCallingCode calling code without '+'
     * @return bool
     */
    public static function HasCallingCode(string $sNumber, string $sCallingCode): bool
    {
        return ($sCallingCode != '') && str_starts_with($sNumber, '+'.$sCallingCode);
    }

    /**
     * Check if 2 E.164 numbers have the same number of digits
     *
     * @param string $sNumber1
     * @param string $sNumber2
     * @return bool
     */
    public static function IsSameLength(string $sNumber1, string $sNumber2): bool
    {
        return (strlen($sNumber1) == strlen($sNumber2));
    }

    /**
     * Format an E.164 number for display (ITU-T E.123): '+', calling code, then groups of digits separated by spaces.
     *   . The country is the given one if its calling code matches the number, otherwise the one with the longest matching calling code
     *   . Among the TipPNFormat of the country with the length of the national number, the one with the longest matching leading digits wins
     *   . With no matching format, the national number is displayed as a single group after the calling code
     * Values that are not E.164 numbers are returned unchanged.
     *
     * @param string|null $sNumber E.164 number
     * @param int|null $iCountryId id of the TipPNCountryCode the number belongs to, if known
     * @return string
     * @throws \CoreException
     * @throws \MySQLException
     * @throws \OQLException
     */
    public static function Format(?string $sNumber, ?int $iCountryId = null): string
    {
        if (($sNumber === null) || !static::IsE164($sNumber)) {
            return $sNumber ?? '';
        }
        static::LoadFormats();

        // Get candidate countries: the given one or the ones with the longest calling code, those having formats first
        $sDigits = substr($sNumber, 1);
        $aCandidates = [];
        if (($iCountryId !== null) && isset(static::$aCallingCodes[$iCountryId]) && static::HasCallingCode($sNumber, static::$aCallingCodes[$iCountryId])) {
            $aCandidates[] = $iCountryId;
        } else {
            $iLongest = 0;
            foreach (static::$aCallingCodes as $iId => $sCallingCode) {
                if (str_starts_with($sDigits, $sCallingCode) && (strlen($sCallingCode) >= $iLongest)) {
                    if (strlen($sCallingCode) > $iLongest) {
                        $aCandidates = [];
                        $iLongest = strlen($sCallingCode);
                    }
                    $aCandidates[] = $iId;
                }
            }
            // Shared calling codes (+1, +44...): the main country of the code, having the most formats, wins ties
            usort($aCandidates, fn($a, $b) => count(static::$aFormats[$b] ?? []) <=> count(static::$aFormats[$a] ?? []));
        }
        if (empty($aCandidates)) {
            return $sNumber;
        }

        // Apply the format with the longest matching leading digits among the formats of the candidates
        $sCallingCode = static::$aCallingCodes[$aCandidates[0]];
        $sNationalNumber = substr($sDigits, strlen($sCallingCode));
        $aBestFormat = null;
        foreach ($aCandidates as $iId) {
            foreach (static::$aFormats[$iId] ?? [] as $aFormat) {
                if (($aFormat['length'] == strlen($sNationalNumber)) && str_starts_with($sNationalNumber, $aFormat['leading_digits'])
                    && (($aBestFormat === null) || (strlen($aFormat['leading_digits']) > strlen($aBestFormat['leading_digits'])))) {
                    $aBestFormat = $aFormat;
                }
            }
        }
        if ($aBestFormat === null) {
            return rtrim('+'.$sCallingCode.' '.$sNationalNumber);
        }
        $sFormatted = '';
        $iDigit = 0;
        foreach (str_split($aBestFormat['pattern']) as $sChar) {
            $sFormatted .= ($sChar == '#') ? $sNationalNumber[$iDigit++] : $sChar;
        }

        return '+'.$sCallingCode.' '.$sFormatted;
    }

    /**
     * Load calling codes of countries and display formats once per request
     *
     * @return void
     * @throws \CoreException
     * @throws \MySQLException
     * @throws \OQLException
     */
    protected static function LoadFormats(): void
    {
        if (static::$aCallingCodes !== null) {
            return;
        }
        static::$aCallingCodes = [];
        static::$aFormats = [];

        $oSearch = DBObjectSearch::FromOQL('SELECT TipPNCountryCode');
        $oSearch->AllowAllData();
        $oSet = new DBObjectSet($oSearch);
        $oSet->OptimizeColumnLoad(['TipPNCountryCode' => ['calling_code']]);
        while ($oCountry = $oSet->Fetch()) {
            static::$aCallingCodes[$oCountry->GetKey()] = $oCountry->Get('calling_code');
        }

        $oSearch = DBObjectSearch::FromOQL('SELECT TipPNFormat');
        $oSearch->AllowAllData();
        $oSet = new DBObjectSet($oSearch);
        $oSet->OptimizeColumnLoad(['TipPNFormat' => ['tippncountrycode_id', 'leading_digits', 'length', 'pattern']]);
        while ($oFormat = $oSet->Fetch()) {
            static::$aFormats[$oFormat->Get('tippncountrycode_id')][] = [
                'leading_digits' => $oFormat->Get('leading_digits') ?? '',
                'length' => (int)$oFormat->Get('length'),
                'pattern' => $oFormat->Get('pattern'),
            ];
        }
    }

    /**
     * Get the CI statuses shared by the background tasks that allocate and release phone numbers according to the status of CIs.
     * When both tasks are enabled, such statuses would make numbers be allocated and released alternately at each run.
     * An empty status list for the allocation task means that the status of CIs is not considered, hence conflicts with any status.
     *
     * @return array conflicting statuses, empty if there is none or if one of the tasks is disabled
     */
    public static function GetStatusConflictsBetweenAllocationAndRelease(): array
    {
        $aAllocateSettings = MetaModel::GetModuleSetting(AllocatePhoneNumbersAttachedToCIs::MODULE_CODE, AllocatePhoneNumbersAttachedToCIs::FUNCTION_CODE, []);
        $aReleaseSettings = MetaModel::GetModuleSetting(ReleasePhoneNumbersFromObsoleteCIs::MODULE_CODE, ReleasePhoneNumbersFromObsoleteCIs::FUNCTION_CODE, []);
        $bAllocateEnabled = (bool)($aAllocateSettings[AllocatePhoneNumbersAttachedToCIs::FUNCTION_SETTING_ENABLED] ?? AllocatePhoneNumbersAttachedToCIs::DEFAULT_FUNCTION_SETTING_ENABLED);
        $bReleaseEnabled = (bool)($aReleaseSettings[ReleasePhoneNumbersFromObsoleteCIs::FUNCTION_SETTING_ENABLED] ?? ReleasePhoneNumbersFromObsoleteCIs::DEFAULT_FUNCTION_SETTING_ENABLED);
        if (!$bAllocateEnabled || !$bReleaseEnabled) {
            return [];
        }

        $aAllocateStatusList = (array)($aAllocateSettings[AllocatePhoneNumbersAttachedToCIs::FUNCTION_SETTING_STATUS_LIST] ?? AllocatePhoneNumbersAttachedToCIs::DEFAULT_FUNCTION_SETTING_STATUS_LIST);
        $aReleaseStatusList = (array)($aReleaseSettings[ReleasePhoneNumbersFromObsoleteCIs::FUNCTION_SETTING_STATUS_LIST] ?? ReleasePhoneNumbersFromObsoleteCIs::DEFAULT_FUNCTION_SETTING_STATUS_LIST);
        if (empty($aAllocateStatusList)) {
            return array_values($aReleaseStatusList);
        }

        return array_values(array_intersect($aAllocateStatusList, $aReleaseStatusList));
    }

    /**
     * Get the list of classes referencing the TipPhoneNumber class
     * Restrict list to non-abstract classes inherited from 'FunctionalCI'
     * For each class, we get an array of all attributes being an external key to a TipPhoneNumber object
     *
     * @return array
     * @throws \CoreException
     */
    public static function GetListOfClassesWithPNs(): array
    {
        $aPNClasses = [];

        // Cache is not used when both iTop and teemIP are in development mode
        $bUseCache = !(utils::IsDevelopmentEnvironment() && MetaModel::GetModuleSetting(static::MODULE_CODE, static::DEVELOPER_MODE_FUNCTION_CODE, false));
        $sCacheFileName = utils::GetCachePath().static::TEEMIP_CACHE_DIR.'/'.static::LIST_CLASSES_WITH_PNS_FILE_NAME;
        if ($bUseCache && is_file($sCacheFileName)) {
            $aPNClasses = include $sCacheFileName;
            if (is_array($aPNClasses)) {
                return $aPNClasses;
            }
            $aPNClasses = [];
        }

        $aFunctionalCIChildClasses = MetaModel::EnumChildClasses('FunctionalCI', ENUM_CHILD_CLASSES_EXCLUDETOP);
        foreach ($aFunctionalCIChildClasses as $sClass) {
            $aPNAttributes = static::GetListOfPNAttributes($sClass);
            if (!empty($aPNAttributes)) {
                $aPNClasses[$sClass] = $aPNAttributes;
            }
        }
        ksort($aPNClasses);

        if ($bUseCache) {
            $sCacheContent = "<?php\n\nreturn ".var_export($aPNClasses, true).";";
            SetupUtils::builddir(dirname($sCacheFileName));
            file_put_contents($sCacheFileName, $sCacheContent);
        }

        return $aPNClasses;
    }

    /**
     * Get the list of PN attributes (external keys toward a TipPhoneNumber) for a given class
     *
     * @param string $sClass
     *
     * @return array
     * @throws \CoreException
     */
    public static function GetListOfPNAttributes(string $sClass): array
    {
        $aPNAttributes = [];
        if (MetaModel::IsAbstract($sClass)) {
            return $aPNAttributes;
        }
        foreach (MetaModel::GetExternalKeys($sClass) as $oExternalKey) {
            if ($oExternalKey->GetTargetClass() == 'TipPhoneNumber') {
                $aPNAttributes[] = $oExternalKey->GetCode();
            }
        }

        return $aPNAttributes;
    }
}
