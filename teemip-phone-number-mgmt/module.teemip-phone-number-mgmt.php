<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

SetupWebPage::AddModule(
    __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
    'teemip-phone-number-mgmt/1.0.0-dev',
    array(
        // Identification
        //
        'label' => 'Phone Number Management',
        'category' => 'business',

        // Setup
        //
        'dependencies' => array(
            'teemip-framework/3.3.0',
        ),
        'mandatory' => false,
        'visible' => true,

        // Components
        //
        'datamodel' => array(
            'vendor/autoload.php',
            'src/Controller/PhoneNumberMgmtController.php',
            'src/Hook/PhoneNumberMgmtOtherActions.php',
            'model.teemip-phone-number-mgmt.php',
        ),
        'data.struct' => array(
            'data/data.struct.TipPNCountryCode.xml',
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


