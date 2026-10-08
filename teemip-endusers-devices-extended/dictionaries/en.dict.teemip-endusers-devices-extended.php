<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

//
// Class: TipSimCard
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TipSimCard' => 'SIM Card',
    'Class:TipSimCard+' => 'SIM Card that may be linked to a physial device like mobile phone, tablet, PC...',
    'TipSimCard:baseinfo' => 'General Information',
    'TipSimCard:techinfo' => 'Technical Information',
    'TipSimCard:date' => 'Dates',
    'TipSimCard:otherinfo' => 'Other Information',
    'Class:TipSimCard/Attribute:format' => 'Format',
    'Class:TipSimCard/Attribute:format+' => '',
    'Class:TipSimCard/Attribute:format/Value:full' => 'Full-size',
    'Class:TipSimCard/Attribute:format/Value:full+' => '1FF',
    'Class:TipSimCard/Attribute:format/Value:mini' => 'Mini-SIM',
    'Class:TipSimCard/Attribute:format/Value:mini+' => '2FF',
    'Class:TipSimCard/Attribute:format/Value:micro' => 'Micro-SIM',
    'Class:TipSimCard/Attribute:format/Value:micro+' => '3FF',
    'Class:TipSimCard/Attribute:format/Value:nano' => 'Nano-SIM',
    'Class:TipSimCard/Attribute:format/Value:nano+' => '4FF',
    'Class:TipSimCard/Attribute:format/Value:esim' => 'Embedded-SIM',
    'Class:TipSimCard/Attribute:format/Value:esim+' => 'eSIM',
    'Class:TipSimCard/Attribute:format/Value:full_to_micro' => 'Full-size that can be cut to micro',
    'Class:TipSimCard/Attribute:format/Value:full_to_nano' => 'Full-size that can be cut to nano',
    'Class:TipSimCard/Attribute:format/Value:micro_to_nano' => 'Micro-SIM that can be cut to nano',
    'Class:TipSimCard/Attribute:iccid' => 'ICCID',
    'Class:TipSimCard/Attribute:iccid+' => 'ICCID code or SIM Card number',
    'Class:TipSimCard/Attribute:carrier_id' => 'Carrier',
    'Class:TipSimCard/Attribute:carrier_id+' => '',
    'Class:TipSimCard/Attribute:tipphonenumber_id' => 'Phone number',
    'Class:TipSimCard/Attribute:tipphonenumber_id+' => 'The phone number linked to the SIM Card',
    'Class:TipSimCard/Attribute:tipphonenumber' => 'Phone number',
    'Class:TipSimCard/Attribute:tipphonenumber+' => '',
    'Class:TipSimCard/Attribute:pin' => 'PIN',
    'Class:TipSimCard/Attribute:pin+' => 'Primary PIN of the SIM Card - Number with 4 to 8 digits',
    'Class:TipSimCard/Attribute:pin2' => 'PIN 2',
    'Class:TipSimCard/Attribute:pin2+' => 'Secondary PIN of the SIM Card - Number with 4 to 8 digits',
    'Class:TipSimCard/Attribute:puk' => 'PUK',
    'Class:TipSimCard/Attribute:puk+' => 'Primary PUK of the SIM Card - 8 digits number',
    'Class:TipSimCard/Attribute:puk2' => 'PUK 2',
    'Class:TipSimCard/Attribute:puk2+' => 'Secondary PUK of the SIM Card - 8 digits number',
    'Class:TipSimCard/Attribute:contact_id' => 'Main contact',
    'Class:TipSimCard/Attribute:contact_id+' => 'The person or team using the SIM Card',
    'Class:TipSimCard/Attribute:physicaldevice_id' => 'Host',
    'Class:TipSimCard/Attribute:physicaldevice_id+' => 'Mobile phone, PC or tablet that hosts the SIM Card',
    'Class:TipSimCard/Attribute:physicaldevice_name' => 'Name of the physical device',
    'Class:TipSimCard/Attribute:physicaldevice_name+' => '',
    'Class:TipSimCard/Attribute:physicalinterface_id' => 'Host interface',
    'Class:TipSimCard/Attribute:physicalinterface_id+' => 'Interface where the SIM Card is stored',
    'Class:TipSimCard/Attribute:physicalinterface_name' => 'Name of the physical interface',
    'Class:TipSimCard/Attribute:physicalinterface_name+' => '',
));

//
// Class: TelephonyCI
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TelephonyCI/Attribute:tipphonenumber_id' => 'Phone number',
    'Class:TelephonyCI/Attribute:tipphonenumber_id+' => 'The phone number linked to this CI',
    'Class:TelephonyCI/Attribute:phonenumber' => 'Phone number',
    'Class:TelephonyCI/Attribute:phonenumber+' => '',
    'TelephonyCI:baseinfo' => 'General Information',
    'TelephonyCI:hwinfo' => 'Hardware Information',
    'TelephonyCI:date' => 'Dates',
    'TelephonyCI:techinfo' => 'Technical Information',
));

//
// Class: MobilePhone
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:MobilePhone/Attribute:tipsimcard_format' => 'SIM Card Format',
    'Class:MobilePhone/Attribute:tipsimcard_format+' => '',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:full' => 'Full-size',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:full+' => '1FF',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:mini' => 'Mini-SIM',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:mini+' => '2FF',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:micro' => 'Micro-SIM',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:micro+' => '3FF',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:nano' => 'Nano-SIM',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:nano+' => '4FF',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:esim' => 'Embedded-SIM',
    'Class:MobilePhone/Attribute:tipsimcard_format/Value:esim+' => 'eSIM',
    'Class:MobilePhone/Attribute:tipsimcard1_id' => 'SIM Card #1',
    'Class:MobilePhone/Attribute:tipsimcard1_id+' => 'Primary SIM Card of the phone',
    'Class:MobilePhone/Attribute:tipsimcard1_name' => 'Name of the primary SIM Card',
    'Class:MobilePhone/Attribute:tipsimcard1_name+' => '',
    'Class:MobilePhone/Attribute:tipsimcard2_id' => 'SIM Card #2',
    'Class:MobilePhone/Attribute:tipsimcard2_id+' => 'Secondary SIM Card of the phone',
    'Class:MobilePhone/Attribute:tipsimcard2_name' => 'Name of the secondary SIM Card',
    'Class:MobilePhone/Attribute:tipsimcard2_name+' => '',
));

//
// Class: PC
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:PC/Attribute:tipsimcard_format' => 'SIM Card Format',
    'Class:PC/Attribute:tipsimcard_format+' => '',
    'Class:PC/Attribute:tipsimcard_format/Value:full' => 'Full-size',
    'Class:PC/Attribute:tipsimcard_format/Value:full+' => '1FF',
    'Class:PC/Attribute:tipsimcard_format/Value:mini' => 'Mini-SIM',
    'Class:PC/Attribute:tipsimcard_format/Value:mini+' => '2FF',
    'Class:PC/Attribute:tipsimcard_format/Value:micro' => 'Micro-SIM',
    'Class:PC/Attribute:tipsimcard_format/Value:micro+' => '3FF',
    'Class:PC/Attribute:tipsimcard_format/Value:nano' => 'Nano-SIM',
    'Class:PC/Attribute:tipsimcard_format/Value:nano+' => '4FF',
    'Class:PC/Attribute:tipsimcard_format/Value:esim' => 'Embedded-SIM',
    'Class:PC/Attribute:tipsimcard_format/Value:esim+' => 'eSIM',
    'Class:PC/Attribute:tipsimcard_id' => 'SIM Card',
    'Class:PC/Attribute:tipsimcard_id+' => 'SIM Card of the PC',
    'Class:PC/Attribute:tipsimcard_name' => 'Name of the SIM Card',
    'Class:PC/Attribute:tipsimcard_name+' => '',
));

//
// Class: Tablet
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:Tablet/Attribute:tipsimcard_format' => 'SIM Card Format',
    'Class:Tablet/Attribute:tipsimcard_format+' => '',
    'Class:Tablet/Attribute:tipsimcard_format/Value:full' => 'Full-size',
    'Class:Tablet/Attribute:tipsimcard_format/Value:full+' => '1FF',
    'Class:Tablet/Attribute:tipsimcard_format/Value:mini' => 'Mini-SIM',
    'Class:Tablet/Attribute:tipsimcard_format/Value:mini+' => '2FF',
    'Class:Tablet/Attribute:tipsimcard_format/Value:micro' => 'Micro-SIM',
    'Class:Tablet/Attribute:tipsimcard_format/Value:micro+' => '3FF',
    'Class:Tablet/Attribute:tipsimcard_format/Value:nano' => 'Nano-SIM',
    'Class:Tablet/Attribute:tipsimcard_format/Value:nano+' => '4FF',
    'Class:Tablet/Attribute:tipsimcard_format/Value:esim' => 'Embedded-SIM',
    'Class:Tablet/Attribute:tipsimcard_format/Value:esim+' => 'eSIM',
    'Class:Tablet/Attribute:tipsimcard_id' => 'SIM Card',
    'Class:Tablet/Attribute:tipsimcard_id+' => 'SIM Card of the tablet',
    'Class:Tablet/Attribute:tipsimcard_name' => 'Name of the SIM Card',
    'Class:Tablet/Attribute:tipsimcard_name+' => '',
    'Tablet:baseinfo' => 'General Information',
    'Tablet:hwinfo' => 'Hardware Information',
    'Tablet:date' => 'Dates',
    'Tablet:techinfo' => 'Technical Information',
));

//
// Class: Peripheral
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:Peripheral/Attribute:tipsimcard_format' => 'SIM Card Format',
    'Class:Peripheral/Attribute:tipsimcard_format+' => '',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:full' => 'Full-size',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:full+' => '1FF',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:mini' => 'Mini-SIM',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:mini+' => '2FF',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:micro' => 'Micro-SIM',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:micro+' => '3FF',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:nano' => 'Nano-SIM',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:nano+' => '4FF',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:esim' => 'Embedded-SIM',
    'Class:Peripheral/Attribute:tipsimcard_format/Value:esim+' => 'eSIM',
    'Class:Peripheral/Attribute:tipsimcard_id' => 'SIM Card',
    'Class:Peripheral/Attribute:tipsimcard_id+' => 'SIM Card of the peripheral',
    'Class:Peripheral/Attribute:tipsimcard_name' => 'Name of the SIM Card',
    'Class:Peripheral/Attribute:tipsimcard_name+' => '',
    'Peripheral:baseinfo' => 'General Information',
    'Peripheral:hwinfo' => 'Hardware Information',
    'Peripheral:date' => 'Dates',
    'Peripheral:techinfo' => 'Technical Information',
));

//
// Class: NetworkDevice
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:NetworkDevice/Attribute:tipsimcard_format' => 'SIM Card Format',
    'Class:NetworkDevice/Attribute:tipsimcard_format+' => '',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:full' => 'Full-size',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:full+' => '1FF',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:mini' => 'Mini-SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:mini+' => '2FF',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:micro' => 'Micro-SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:micro+' => '3FF',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:nano' => 'Nano-SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:nano+' => '4FF',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:esim' => 'Embedded-SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_format/Value:esim+' => 'eSIM',
    'Class:NetworkDevice/Attribute:tipsimcard_id' => 'SIM Card',
    'Class:NetworkDevice/Attribute:tipsimcard_id+' => 'IM Card of the PC',
    'Class:NetworkDevice/Attribute:tipsimcard_name' => 'Name of the SIM Card',
    'Class:NetworkDevice/Attribute:tipsimcard_name+' => '',
));

//
// Menus & actions
//

Dict::Add('EN US', 'English', 'English', array(
    'Menu:TelefonySpace:TelephonyDevices' => 'Telephony Devices',
    'Menu:TelefonySpace:DevicesWithSIM' => 'Devices with a SIM Card',
    'Title:DevicesWithSIM:NetworkDevice' => 'Network devices',
    'Title:DevicesWithSIM:PC' => 'PCs',
    'Title:DevicesWithSIM:Peripheral' => 'Peripherals',
    'Title:DevicesWithSIM:Tablet' => 'Tablets',

    // SIM card checks
    'UI:EndusersDevicesExtended:Action:New:TipSimCard:WrongPhysicalDevice' => 'The selected device cannot host a SIM card',
    'UI:EndusersDevicesExtended:Action:New:TipSimCard:IncompatibleSIMs' => 'The format of the SIM card is not compatible with the SIM card format of the device',
));

