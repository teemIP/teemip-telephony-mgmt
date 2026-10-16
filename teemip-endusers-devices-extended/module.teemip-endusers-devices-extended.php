<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

SetupWebPage::AddModule(
    __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
    'teemip-endusers-devices-extended/1.0.0-dev',
    array(
        // Identification
        //
        'label' => 'End-user Devices Management Extended',
        'category' => 'business',

        // Setup
        //
        'dependencies' => array(
            'teemip-endusers-devices-adaptor/3.2.0',
            'teemip-phone-number-mgmt/1.0.0-dev',
        ),
        'mandatory' => false,
        'visible' => false,
        'auto_select' => 'SetupInfo::ModuleIsSelected("teemip-phone-number-mgmt") && SetupInfo::ModuleIsSelected("teemip-endusers-devices-adaptor")',
        'installer' => 'EndUsersDevicesExtendedInstaller',

        // Components
        //
        'datamodel' => array(
        ),
        'data.struct' => array(//'data.struct.IPAudit.xml',
        ),
        'data.sample' => array(
        ),

        // Documentation
        //
        'doc.manual_setup' => '',
        'doc.more_information' => '',

        // Default settings
        //
        'settings' => array(),
    )
);


                $sLang = null;
                // Try to get app. language from configuration file (app. upgrade)
                $sConfigFileName = APPCONF.'production/'.ITOP_CONFIG_FILE;
                if (file_exists($sConfigFileName)) {
                    $oFileConfig = new Config($sConfigFileName);
                    if (is_object($oFileConfig)) {
                        $sLang = str_replace(' ', '_', strtolower($oFileConfig->GetDefaultLanguage()));
                    }
                }

                // If still no language, get the default one
                if (null === $sLang) {
                    $sLang = str_replace(' ', '_', strtolower($oConfiguration->GetDefaultLanguage()));
                }

                $sFileName = dirname(__FILE__)."/data/".$sLang.".data.teemip-endusers-devices-extended.xml";
                SetupLog::Info("Searching file: $sFileName");
                if (!file_exists($sFileName)) {
                    $sFileName = dirname(__FILE__)."/data/en_us.data.teemip-endusers-devices-extended.xml";
                }
                SetupLog::Info("Loading file: $sFileName");
                $oDataLoader->StartSession($oMyChange);
                $oDataLoader->LoadFile($sFileName, false, true);
                $oDataLoader->EndSession();
            }
        }
    }
}
