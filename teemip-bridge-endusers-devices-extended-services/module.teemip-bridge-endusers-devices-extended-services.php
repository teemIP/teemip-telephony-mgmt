<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'teemip-bridge-endusers-devices-extended-services/1.0.0-dev',
	array(
		// Identification
		//
		'label' => 'Bridge for End-user Devices Management extended and Services',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			'teemip-endusers-devices-extended/1.0.0-dev',
			'itop-bridge-endusers-devices-services/3.3.0||teemip-endusers-devices-extended/1.0.0-dev',
		),
		'mandatory' => false,
		'visible' => false,
		'auto_select' => 'SetupInfo::ModuleIsSelected("teemip-endusers-devices-extended") &&  (SetupInfo::ModuleIsSelected("itop-bridge-endusers-devices-services")) ',

		// Components
		//
		'datamodel' => array(
		),
		'webservice' => array(

		),
		'data.struct' => array(
		),
		'data.sample' => array(
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any

		// Default settings
		//
		'settings' => array(
		),
	)
);

