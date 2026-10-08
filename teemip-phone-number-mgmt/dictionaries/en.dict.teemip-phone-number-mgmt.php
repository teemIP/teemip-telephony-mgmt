<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

//
// Class: TipPNObject
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TipPNObject' => 'Phone Number Object',
    'Class:TipPNObject+' => '',
    'Class:TipPNObject/Attribute:finalclass' => 'Final class',
    'Class:TipPNObject/Attribute:finalclass+' => 'Name of the final class',
    'Class:TipPNObject/Attribute:org_id' => 'Organization',
    'Class:TipPNObject/Attribute:org_id+' => 'Organization that the phone number object belongs to',
    'Class:TipPNObject/Attribute:org_name' => 'Organization name',
    'Class:TipPNObject/Attribute:org_name+' => '',
    'Class:TipPNObject/Attribute:status' => 'Status',
    'Class:TipPNObject/Attribute:status+' => '',
    'Class:TipPNObject/Attribute:status/Value:reserved' => 'Reserved',
    'Class:TipPNObject/Attribute:status/Value:reserved+' => '',
    'Class:TipPNObject/Attribute:status/Value:allocated' => 'Allocated',
    'Class:TipPNObject/Attribute:status/Value:allocated+' => '',
    'Class:TipPNObject/Attribute:status/Value:released' => 'Released',
    'Class:TipPNObject/Attribute:status/Value:released+' => '',
    'Class:TipPNObject/Attribute:status/Value:unassigned' => 'Unassigned',
    'Class:TipPNObject/Attribute:status/Value:unassigned+' => '',
    'Class:TipPNObject/Attribute:comment' => 'Note',
    'Class:TipPNObject/Attribute:comment+' => '',
    'Class:TipPNObject/Attribute:requestor_id' => 'Requestor',
    'Class:TipPNObject/Attribute:requestor_id+' => 'Person who requested the creation of the object',
    'Class:TipPNObject/Attribute:requestor_name' => 'Requestor name',
    'Class:TipPNObject/Attribute:requestor_name+' => '',
    'Class:TipPNObject/Attribute:allocation_date' => 'Allocation date',
    'Class:TipPNObject/Attribute:allocation_date+' => 'Date when PN object has been allocated',
    'Class:TipPNObject/Attribute:release_date' => 'Release date',
    'Class:TipPNObject/Attribute:release_date+' => 'Date when PN object has been released and is not used anymore.',
    'Class:TipPNObject/Attribute:contacts_list' => 'Contacts',
    'Class:TipPNObject/Attribute:contacts_list+' => 'Contacts attached to the PN object',
    'Class:TipPNObject/Attribute:documents_list' => 'Documents',
    'Class:TipPNObject/Attribute:documents_list+' => 'Documents attached to the PN object',
    'Class:TipPNObject/Attribute:tickets_list' => 'Documents',
    'Class:TipPNObject/Attribute:tickets_list+' => 'Documents attached to the PN object',
));

//
// Class: lnkContactToTipPNObject
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:lnkContactToTipPNObject' => 'Link Contact / PN Object',
    'Class:lnkContactToTipPNObject+' => '',
    'Class:lnkContactToTipPNObject/Name' => '%1$s / %2$s',
    'Class:lnkContactToTipPNObject/Attribute:tippnobject_id' => 'PN Object',
    'Class:lnkContactToTipPNObject/Attribute:tippnobject_id+' => '',
    'Class:lnkContactToTipPNObject/Attribute:contact_id' => 'Contact',
    'Class:lnkContactToTipPNObject/Attribute:contact_id+' => '',
    'Class:lnkContactToTipPNObject/Attribute:contact_name' => 'Contact name',
    'Class:lnkContactToTipPNObject/Attribute:contact_name+' => '',
));

//
// Class: lnkDocToTipPNObject
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:lnkDocToTipPNObject' => 'Link Document / PN Object',
    'Class:lnkDocToTipPNObject+' => '',
    'Class:lnkDocToTipPNObject/Name' => '%1$s / %2$s',
    'Class:lnkDocToTipPNObject/Attribute:tippnobject_id' => 'PN Object',
    'Class:lnkDocToTipPNObject/Attribute:tippnobject_id+' => '',
    'Class:lnkDocToTipPNObject/Attribute:document_id' => 'Document',
    'Class:lnkDocToTipPNObject/Attribute:document_id+' => '',
    'Class:lnkDocToTipPNObject/Attribute:document_name' => 'Document name',
    'Class:lnkDocToTipPNObject/Attribute:document_name+' => '',
));

//
// Class: lnkTipPNObjectToTicket
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:lnkTipPNObjectToTicket' => 'Link PN Object / Ticket',
    'Class:lnkTipPNObjectToTicket+' => '',
    'Class:lnkTipPNObjectToTicket/Name' => '%1$s / %2$s',
    'Class:lnkTipPNObjectToTicket/Attribute:tippnobject_id' => 'PN Object',
    'Class:lnkTipPNObjectToTicket/Attribute:tippnobject_id+' => '',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_id' => 'Ticket',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_id+' => '',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_ref' => 'Ref',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_ref+' => '',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_title' => 'Title',
    'Class:lnkTipPNObjectToTicket/Attribute:ticket_title+' => '',
));

//
// Class: TipPNCountryCode
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TipPNCountryCode' => 'Country Calling Code',
    'Class:TipPNCountryCode+' => 'International calling code (ITU-T E.164) of a country',
    'Class:TipPNCountryCode/Name' => '%1$s (+%2$s)',
    'Class:TipPNCountryCode/Attribute:name' => 'Country',
    'Class:TipPNCountryCode/Attribute:name+' => '',
    'Class:TipPNCountryCode/Attribute:iso_code' => 'ISO code',
    'Class:TipPNCountryCode/Attribute:iso_code+' => 'ISO 3166-1 alpha-2 code of the country',
    'Class:TipPNCountryCode/Attribute:calling_code' => 'Calling code',
    'Class:TipPNCountryCode/Attribute:calling_code+' => 'Country calling code, without \'+\'',
    'Class:TipPNCountryCode/Attribute:tippnranges_list' => 'Phone number ranges',
    'Class:TipPNCountryCode/Attribute:tippnranges_list+' => 'Phone number ranges belonging to the country',
    'Class:TipPNCountryCode/UniquenessRule:no_duplicate_name' => 'This country already exists',
    'Class:TipPNCountryCode/UniquenessRule:no_duplicate_iso_code' => 'This ISO code is already used by another country',
));

//
// Class: TipPNRange
//

Dict::Add('EN US', 'English', 'English', array(
	'Class:TipPNRange' => 'Phone Number Range',
	'Class:TipPNRange+' => '',
	'Class:TipPNRange:baseinfo' => 'General Information',
    'Class:TipPNRange:delegationinfo' => 'Delegation Information',
	'Class:TipPNRange:numberinginfo' => 'Numbering Information',
    'Class:TipPNRange:DelegatedToChild' => '<delegation_highlight>Delegated to organization: </delegation_highlight>%1$s',
    'Class:TipPNRange:DelegatedFromParent' => '<delegation_highlight>Delegated from organization: </delegation_highlight>%1$s',
	'Class:TipPNRange/Attribute:name' => 'Name',
	'Class:TipPNRange/Attribute:name+' => '',
    'Class:TipPNRange/Attribute:parent_org_id' => 'Delegated from',
    'Class:TipPNRange/Attribute:parent_org_id+' => 'Organization where the phone number range has been delegated from',
    'Class:TipPNRange/Attribute:parent_org_name' => 'Delegating organization name',
    'Class:TipPNRange/Attribute:parent_org_name+' => 'Name of the organization where the phone number range has been delegated from',
    'Class:TipPNRange/Attribute:parent_id' => 'Parent range',
    'Class:TipPNRange/Attribute:parent_id+' => 'Parent phone number range that the range belongs to',
    'Class:TipPNRange/Attribute:parent_name' => 'Parent name',
    'Class:TipPNRange/Attribute:parent_name+' => '',
    'Class:TipPNRange/Attribute:tippncountrycode_id' => 'Country',
    'Class:TipPNRange/Attribute:tippncountrycode_id+' => 'Country the range belongs to. It defines the calling code of its numbers.',
    'Class:TipPNRange/Attribute:tippncountrycode_name' => 'Country name',
    'Class:TipPNRange/Attribute:tippncountrycode_name+' => '',
    'Class:TipPNRange/Attribute:calling_code' => 'Calling code',
    'Class:TipPNRange/Attribute:calling_code+' => '',
	'Class:TipPNRange/Attribute:firstnumber' => 'First number',
	'Class:TipPNRange/Attribute:firstnumber+' => 'First number of the range in international E.164 format (e.g. +33123456789)',
    'Class:TipPNRange/Attribute:lastnumber' => 'Last number',
    'Class:TipPNRange/Attribute:lastnumber+' => 'Last number of the range in international E.164 format (e.g. +33123456789)',
    'Class:TipPNRange/Attribute:occupancy' => 'Registered numbers',
    'Class:TipPNRange/Attribute:occupancy+' => 'Percentage of phone number objects in the range',
    'Class:TipPNRange/Attribute:tipphonenumbers_list' => 'Phone numbers',
    'Class:TipPNRange/Attribute:tipphonenumbers_list+' => 'All the pone numbers that belong to the range',
    'Class:TipPNRange/UniquenessRule:no_duplicate_name' => 'The same Name already exist in the organization: duplicates are not allowed.',
    'Class:TipPNRange/UniquenessRule:no_duplicate_range' => 'The same Range of numbers already exist in the organization: duplicates are not allowed',
));

//
// Class: TipPhoneNumber
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TipPhoneNumber' => 'Phone Number',
    'Class:TipPhoneNumber+' => '',
    'Class:TipPhoneNumber:baseinfo' => 'General Information',
    'Class:TipPhoneNumber:numberinfo' => 'Number Information',
    'Class:TipPhoneNumber/Attribute:tippnrange_id' => 'Phone number range',
    'Class:TipPhoneNumber/Attribute:tippnrange_id+' => '',
    'Class:TipPhoneNumber/Attribute:tippnrange_name' => 'Name of the range',
    'Class:TipPhoneNumber/Attribute:tippnrange_name+' => '',
    'Class:TipPhoneNumber/Attribute:number' => 'Number',
    'Class:TipPhoneNumber/Attribute:number+' => 'Number in international E.164 format (e.g. +33123456789)',
    'Class:TipPhoneNumber/Attribute:tippnextensions_list' => 'Extensions',
    'Class:TipPhoneNumber/Attribute:tippnextensions_list+' => 'All the extensions attached to that number',
    'Class:TipPhoneNumber/UniquenessRule:no_duplicate_number' => 'This number already exists in the organization',
    'Class:TipPhoneNumber/Tab:ci_list' => 'CIs',
    'Class:TipPhoneNumber/Tab:ci_list+' => 'List of CIs using this phone number',
    'Class:TipPhoneNumber/Tab:ci_list_class' => '%1$ss using this phone number',
));

//
// Class: TipPNExtension
//

Dict::Add('EN US', 'English', 'English', array(
    'Class:TipPNExtension' => 'Extension',
    'Class:TipPNExtension+' => '',
    'Class:TipPNExtension/Name' => '%1$s - %2$s',
    'Class:TipPNExtension:baseinfo' => 'General Information',
    'Class:TipPNExtension:numberinfo' => 'Extension Information',
    'Class:TipPNExtension/Attribute:tipphonenumber_id' => 'Phone number',
    'Class:TipPNExtension/Attribute:tipphonenumber_id+' => '',
    'Class:TipPNExtension/Attribute:tipphonenumber_number' => 'Phone number',
    'Class:TipPNExtension/Attribute:tipphonenumber_number+' => '',
    'Class:TipPNExtension/Attribute:code' => 'Code',
    'Class:TipPNExtension/Attribute:code+' => '',
));

//
// Menus & actions
//

Dict::Add('EN US', 'English', 'English', array(
    'Menu:TipTelephonyManagement' => 'Telephony Management',
    'Menu:TipTelephonyManagement+' => '',
    'Menu:TipTelephonySpace' => 'Telephony Space',
    'Menu:TipTelephonySpace+' => '',
    'Menu:NewTipPNObject' => 'New Phone Number object',
    'Menu:NewTipPNObject+' => 'Creation of a new phone number object',
    'Menu:SearchTipPNObject' => 'Search for Phone Number objects',
    'Menu:SearchTipPNObject+' => '',
    'Menu:TipPNRange' => 'Phone Number Ranges',
    'Menu:TipPNRange+' => '',
    'Menu:TipPhoneNumber' => 'Phone Numbers',
    'Menu:TipPhoneNumber+' => '',
    'Menu:TipPNExtension' => 'Extensions',
    'Menu:TipPNExtension+' => '',
    'Menu:TipTelephonySpace:PNObjects' => 'Phone Numbers',
    'Menu:PNMgmt:Typology' => 'Telephony Typologies',

//
// Management of PNRanges
//
    // Creation Management
    'UI:PNManagement:Action:New:Domain:NameCollision' => 'Domain name already exists!',
    'UI:PNManagement:Action:New:TipPhoneNumber:NotE164' => '%1$s is not a valid international number. Expected format: +<country code><number> (e.g. +33123456789)',
    'UI:PNManagement:Action:New:TipPhoneNumber:NotInRange' => 'Number %1$s doesn\'t belong to range %2$s',
    'UI:PNManagement:Action:New:TipPNRange:NotSameLength' => 'First and last numbers of the range must have the same number of digits',
    'UI:PNManagement:Action:New:TipPNRange:WrongCallingCode' => 'Numbers of the range must start with +%1$s, the calling code of %2$s',
    'UI:PNManagement:Action:New:TipPNRange:NotSameCountryAsParent' => 'Range must belong to the same country as its parent range',
    'UI:PNManagement:Action:New:TipPNRange:Reverted' => 'First number of the range must be smaller than the last one',
    'UI:PNManagement:Action:New:TipPNRange:NotInParent' => 'Range must be strictly included in its parent range',
    'UI:PNManagement:Action:New:TipPNRange:Collision0' => 'Range already exists',
    'UI:PNManagement:Action:New:TipPNRange:Collision1' => 'First number of the range collides with another range',
    'UI:PNManagement:Action:New:TipPNRange:Collision2' => 'Last number of the range collides with another range',
    'UI:PNManagement:Action:Delegate:TipPNRange:ConflictWithDelegatedBlockFromOtherOrg' => 'Range contains a range delegated from another organization',

    // Display tree of PNRanges
    'UI:PNManagement:Action:DisplayTree:TipPNRange' => 'Display Tree',
    'UI:PNManagement:Action:DisplayTree:TipPNRange+' => '',
    'teemip-phone-number-mgmt/Operation:DisplayTree/Title' => 'Display Tree',
    'UI:PhoneNumberManagement:Action:DisplayTree:Title' => 'Phone Number Ranges',
    'UI:IPManagement:Action:DisplayTree:TipPNRange:OrgName' => 'Organization %1$s',

    // Display pointers to previous and next PNRanges
    'UI:PNManagement:Action:DisplayPrevious:TipPNRange' => 'Previous',
    'UI:PNManagement:Action:DisplayNext:TipPNRange' => 'Next',

    // Display pointers to previous and next PhoneNumbers
    'UI:PNManagement:Action:DisplayPrevious:TipPhoneNumber' => 'Previous',
    'UI:PNManagement:Action:DisplayNext:TipPhoneNumber' => 'Next',


    'UI:PNManagement:Action:DisplayList:TipPNRange' => 'Display List',
    'UI:PNManagement:Action:DisplayList:TipPNRange+' => '',
    'UI:IPManagement:Action:DisplayList:TipPNRange:PageTitle_Class' => 'Phone Number Ranges',
    'UI:PNManagement:Action:DisplayList:TipPNRange:Title_Class' => 'Phone Number Ranges',

    // Display tree of PNRanges
    'UI:PNManagement:Action:DisplayTree:TipPNRange' => 'Display Tree',
    'UI:PNManagement:Action:DisplayTree:TipPNRange+' => '',
    'UI:IPManagement:Action:DisplayTree:TipPNRange:PageTitle_Class' => 'Phone Number Ranges',
    'UI:IPManagement:Action:DisplayTree:TipPNRange:Title_Class' => 'Phone Number Ranges',


));
