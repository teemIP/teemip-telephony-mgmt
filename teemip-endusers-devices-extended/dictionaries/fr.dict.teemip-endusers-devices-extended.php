<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

//
// Class: TipSimCard
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:TipSimCard' => 'Carte SIM',
    'Class:TipSimCard+' => 'Carte SIM povant être liée à un équipement physique tel que téléphone mobile, tablette, PC...',
    'TipSimCard:baseinfo' => 'Informations Générales',
    'TipSimCard:techinfo' => 'Informations Techniques',
    'TipSimCard:date' => 'Dates',
    'TipSimCard:otherinfo' => 'Autres Informations',
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
    'Class:TipSimCard/Attribute:format/Value:full_to_micro' => 'Full-size qui peut être sectionnée à la taille micro',
    'Class:TipSimCard/Attribute:format/Value:full_to_nano' => 'Full-size qui peut être sectionnée à la taille nano',
    'Class:TipSimCard/Attribute:format/Value:micro_to_nano' => 'Micro-SIM qui peut être sectionnée à la taille nano',
    'Class:TipSimCard/Attribute:iccid' => 'ICCID',
    'Class:TipSimCard/Attribute:iccid+' => 'Code ICCID ou numéro de carte SIM',
    'Class:TipSimCard/Attribute:carrier_id' => 'Opérateur',
    'Class:TipSimCard/Attribute:carrier_id+' => '',
    'Class:TipSimCard/Attribute:tipphonenumber_id' => 'Numéro de téléphone',
    'Class:TipSimCard/Attribute:tipphonenumber_id+' => 'Le numéro de téléphone lié à la carte SIM',
    'Class:TipSimCard/Attribute:tipphonenumber' => 'Numéro de téléphone',
    'Class:TipSimCard/Attribute:tipphonenumber+' => '',
    'Class:TipSimCard/Attribute:pin' => 'Code PIN',
    'Class:TipSimCard/Attribute:pin+' => 'Code PIN primaire de la carte SIM - Nombre de 4 à 8 chiffres',
    'Class:TipSimCard/Attribute:pin2' => 'Code PIN #2',
    'Class:TipSimCard/Attribute:pin2+' => 'Code PIN secondaire de la carte SIM - Nombre de 4 à 8 chiffres',
    'Class:TipSimCard/Attribute:puk' => 'Code PUK',
    'Class:TipSimCard/Attribute:puk+' => 'Code PUK primaire de la carte SIM - Nombre  8 chiffres',
    'Class:TipSimCard/Attribute:puk2' => 'Code PUK #2',
    'Class:TipSimCard/Attribute:puk2+' => 'Code PUK secondaire de la carte SIM - Nombre à 8 chiffres',
    'Class:TipSimCard/Attribute:contact_id' => 'Contact principal',
    'Class:TipSimCard/Attribute:contact_id+' => 'Personne ou équipe utilisant la carte SIM',
    'Class:TipSimCard/Attribute:physicaldevice_id' => 'Equipement',
    'Class:TipSimCard/Attribute:physicaldevice_id+' => 'Equipement physique hébergeant la carte SIM',
    'Class:TipSimCard/Attribute:physicaldevice_name' => 'Nom de l\'équipement physique',
    'Class:TipSimCard/Attribute:physicaldevice_name+' => '',
    'Class:TipSimCard/Attribute:physicalinterface_id' => 'Interface',
    'Class:TipSimCard/Attribute:physicalinterface_id+' => 'Interface de l\'équipement ou la carte SIM est stockée',
    'Class:TipSimCard/Attribute:physicalinterface_name' => 'Nom de l\'interface physique',
    'Class:TipSimCard/Attribute:physicalinterface_name+' => '',
));

//
// Class: TelephonyCI
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:TelephonyCI/Attribute:tipphonenumber_id' => 'Numéro de téléphone',
    'Class:TelephonyCI/Attribute:tipphonenumber_id+' => 'Le numéro de téléphone lié à cet équipement',
    'Class:TelephonyCI/Attribute:phonenumber' => 'Numéro de téléphone',
    'Class:TelephonyCI/Attribute:phonenumber+' => '',
    'TelephonyCI:baseinfo' => 'Informations Générales',
    'TelephonyCI:hwinfo' => 'Informations Matériel',
    'TelephonyCI:date' => 'Dates',
    'TelephonyCI:techinfo' => 'Informations Techniques',
));

//
// Class: MobilePhone
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:MobilePhone/Attribute:tipsimcard_format' => 'Format de la carte SIM',
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
    'Class:MobilePhone/Attribute:tipsimcard1_id' => 'Carte SIM #1',
    'Class:MobilePhone/Attribute:tipsimcard1_id+' => 'Carte SIM primaire du téléphone',
    'Class:MobilePhone/Attribute:tipsimcard1_name' => 'Nom de la carte SIM primaire',
    'Class:MobilePhone/Attribute:tipsimcard1_name+' => '',
    'Class:MobilePhone/Attribute:tipsimcard2_id' => 'Carte SIM #2',
    'Class:MobilePhone/Attribute:tipsimcard2_id+' => 'Carte SIM secondaire du téléphone',
    'Class:MobilePhone/Attribute:tipsimcard2_name' => 'Nom de la carte SIM secondaire',
    'Class:MobilePhone/Attribute:tipsimcard2_name+' => '',
));

//
// Class: PC
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:PC/Attribute:tipsimcard_format' => 'Format de la carte SIM',
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
    'Class:PC/Attribute:tipsimcard_id' => 'Carte SIM',
    'Class:PC/Attribute:tipsimcard_id+' => 'Carte SIM du PC',
    'Class:PC/Attribute:tipsimcard_name' => 'Nom de la carte SIM',
    'Class:PC/Attribute:tipsimcard_name+' => '',
));

//
// Class: Tablet
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:Tablet/Attribute:tipsimcard_format' => 'Format de la carte SIM',
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
    'Class:Tablet/Attribute:tipsimcard_id' => 'Carte SIM',
    'Class:Tablet/Attribute:tipsimcard_id+' => 'Carte SIM de la tablette',
    'Class:Tablet/Attribute:tipsimcard_name' => 'Nom de la carte SIM',
    'Class:Tablet/Attribute:tipsimcard_name+' => '',
    'Tablet:baseinfo' => 'Informations Générales',
    'Tablet:hwinfo' => 'Informations Matériel',
    'Tablet:date' => 'Dates',
    'Tablet:techinfo' => 'Informations Techniques',
));

//
// Class: Peripheral
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:Peripheral/Attribute:tipsimcard_format' => 'Format de la carte SIM',
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
    'Class:Peripheral/Attribute:tipsimcard_id' => 'Carte SIM',
    'Class:Peripheral/Attribute:tipsimcard_id+' => 'Carte SIM du périphérique',
    'Class:Peripheral/Attribute:tipsimcard_name' => 'Nom de la carte SIM',
    'Class:Peripheral/Attribute:tipsimcard_name+' => '',
    'Peripheral:baseinfo' => 'Informations Générales',
    'Peripheral:hwinfo' => 'Informations Matériel',
    'Peripheral:date' => 'Dates',
    'Peripheral:techinfo' => 'Informations Techniques',
));

//
// Class: NetworkDevice
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Class:NetworkDevice/Attribute:tipsimcard_format' => 'Format de la carte SIM',
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
    'Class:NetworkDevice/Attribute:tipsimcard_id' => 'Carte SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_id+' => 'Carte SIM du PC',
    'Class:NetworkDevice/Attribute:tipsimcard_name' => 'Nom de la carte SIM',
    'Class:NetworkDevice/Attribute:tipsimcard_name+' => '',
));

//
// Menus & actions
//

Dict::Add('FR FR', 'French', 'Français', array(
    'Menu:TelefonySpace:TelephonyDevices' => 'Equipements téléphonique',
    'Menu:TelefonySpace:DevicesWithSIM' => 'Matériels équipés d\'une carte SIM',
    'Title:DevicesWithSIM:NetworkDevice' => 'Equipements réseaux',
    'Title:DevicesWithSIM:PC' => 'PCs',
    'Title:DevicesWithSIM:Peripheral' => 'Périphériques',
    'Title:DevicesWithSIM:Tablet' => 'Tablettes',

    // SIM card checks
    'UI:EndusersDevicesExtended:Action:New:TipSimCard:WrongPhysicalDevice' => 'Le matériel sélectionné ne peut pas accueillir de carte SIM',
    'UI:EndusersDevicesExtended:Action:New:TipSimCard:IncompatibleSIMs' => 'Le format de la carte SIM n\'est pas compatible avec le format de carte SIM du matériel',
));

