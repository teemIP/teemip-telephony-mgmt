<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Helper;

use MetaModel;
use SetupUtils;
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
