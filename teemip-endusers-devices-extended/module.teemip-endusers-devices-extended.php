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


