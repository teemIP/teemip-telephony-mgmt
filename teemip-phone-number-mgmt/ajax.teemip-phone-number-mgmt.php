<?php
/*
 * @copyright   Copyright (C) 2010-2026 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

use Combodo\iTop\Application\WebPage\AjaxPage;

if (!defined('__DIR__')) {
	define('__DIR__', dirname(__FILE__));
}
if (!defined('APPROOT')) {
	if (file_exists(__DIR__.'/../../approot.inc.php')) {
		require_once(__DIR__.'/../../approot.inc.php');   // When in env-xxxx folder
	} else {
		require_once(__DIR__.'/../../../approot.inc.php');   // When in datamodels/x.x folder
	}
}
require_once(APPROOT.'/application/application.inc.php');

/************************************************
 *
 * Ajax interface for Phone Number Management module starts here
 *
 ************************************************/
try {
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLogin(); // Check user rights and prompt if needed

	$oP = new AjaxPage('');
	ContextTag::AddContext(ContextTag::TAG_CONSOLE);

	$operation = utils::ReadParam('operation', '');
	$sVId = utils::ReadParam('vid', '', false, 'element_identifier');

	switch ($operation) {
		case 'get_cis_to_allocate':     // List of CIs of a class that a phone number can be allocated to
			$iOrgId = utils::ReadParam('org_id', 0, false, 'integer');
			$sCIClass = utils::ReadParam('ciclass', '', false, 'class');
			$oP->AddUiBlock(TipPhoneNumber::MakeCISelect($iOrgId, $sCIClass, $sVId));
			break;

		case 'get_pnattributes_to_allocate':     // List of phone number attributes of a class
			$sCIClass = utils::ReadParam('ciclass', '', false, 'class');
			$oP->AddUiBlock(TipPhoneNumber::MakePNAttributeSelect($sCIClass, $sVId));
			break;

		default:
			break;
	}

	$oP->output();
} catch (Exception $e) {
	echo htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8');
	IssueLog::Error($e->getMessage()."\nDebug trace:\n".$e->getTraceAsString());
}
