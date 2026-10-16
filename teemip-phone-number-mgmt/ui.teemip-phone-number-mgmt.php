<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

use Combodo\iTop\Application\UI\Base\Component\Html\HtmlFactory;
use Combodo\iTop\Application\UI\Base\Component\Panel\PanelUIBlockFactory;
use Combodo\iTop\Application\WebPage\iTopWebPage;
use TeemIp\TeemIp\Extension\Framework\Helper\DisplayMessage;
use TeemIp\TeemIp\Extension\Framework\Helper\DisplayTree;
use TeemIp\TeemIp\Extension\Framework\Helper\IPUtils;

/*******************************************************************
 *
 * Main user interface pages for Phone Number Management extension starts here
 *
 * *****************************************************************/
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
require_once(APPROOT.'/application/startup.inc.php');

try {
	$operation = utils::ReadParam('operation', '');
	$bPrintable = (utils::ReadParam('printable', 0) == '1');

	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	$sLoginMessage = LoginWebPage::DoLogin(); // Check user rights and prompt if needed
	$oAppContext = new ApplicationContext();

	$oP = new iTopWebPage(Dict::S('UI:WelcomeToITop'), $bPrintable);
	$oP->SetMessage($sLoginMessage);
	$oP->set_base(utils::GetAbsoluteUrlAppRoot().'pages/');

	switch ($operation) {
		///////////////////////////////////////////////////////////////////////////////////////////

		case 'displaytree':     // Display hierarchical tree of phone number ranges
			$sClass = 'TipPNRange';
			$sDelegatedNodesRendering = utils::ReadParam('delegated_nodes_rendering', 'folded');

			// Get number of records
			$iCurrentOrganization = $oAppContext->GetCurrentValue('org_id');
			if ($iCurrentOrganization == '') {
				$oSet = new CMDBObjectSet(DBObjectSearch::FromOQL("SELECT $sClass"));
			} else {
				$oSet = new CMDBObjectSet(DBObjectSearch::FromOQL("SELECT $sClass AS c WHERE c.org_id = :org_id"), array(), array('org_id' => $iCurrentOrganization));
			}
			$iSetCount = $oSet->Count();

			// Display block with list menu on top, as for IP blocks and subnets
			$sHeaderTitle = Dict::S('UI:IPManagement:Action:DisplayTree:'.$sClass.':PageTitle_Class');
			$oP->SetBreadCrumbEntry($sHeaderTitle, $sHeaderTitle, '', '', 'fas fa-sitemap', iTopWebPage::ENUM_BREADCRUMB_ENTRY_ICON_TYPE_CSS_CLASSES);
			$sTitle = Dict::Format('UI:IPManagement:Action:DisplayTree:'.$sClass.':Title_Class', MetaModel::GetName($sClass));
			$oP->set_title($sHeaderTitle);

			$oMenuBlock = new MenuBlock($oSet->GetFilter(), 'list');
			$oBlockMenu = $oMenuBlock->GetRenderContent($oP, array('selection_mode' => false), 'displaytree_menu');

			$oPanel = PanelUIBlockFactory::MakeForClass($sClass, $sTitle);
			$oPanel->SetSubTitle(Dict::Format('UI:Pagination:HeaderNoSelection', $iSetCount));
			$oPanel->AddToolbarBlock($oBlockMenu);
			$oP->AddUiBlock($oPanel);

			// Button to fold / unfold delegated ranges
			$sUrl = utils::GetAbsoluteUrlModulePage('teemip-phone-number-mgmt', 'ui.teemip-phone-number-mgmt.php');
			$sNewDelegatedNodesRendering = ($sDelegatedNodesRendering == 'folded') ? 'unfolded' : 'folded';
			$sHtml = "<form method=\"post\" action=\"".$sUrl."\">";
			$sHtml .= "<input type=\"hidden\" name=\"operation\" value=\"displaytree\">";
			$sHtml .= "<input type=\"hidden\" name=\"delegated_nodes_rendering\" value=\"$sNewDelegatedNodesRendering\">";
			$sHtml .= $oAppContext->GetForForm();
			$sHtml .= "<button type=\"submit\" class=\"action\"><span>".Dict::S('UI:Action:DisplayTree:DelegatedItems:'.$sNewDelegatedNodesRendering)."</span></button><br><br>";
			$sHtml .= "</form>";

			$oPanel->AddSubBlock(HtmlFactory::MakeParagraph(''))
				->AddHtml($sHtml)
				->AddHtml(DisplayTree::GetTree($sClass, $iCurrentOrganization, $sDelegatedNodesRendering));

			$oP->add_ready_script("\$('#tree ul').treeview();");
			$oP->LinkStylesheetFromModule('teemip-framework/asset/css/teemip-display-tree.css');
			break; // End case displaytree

		///////////////////////////////////////////////////////////////////////////////////////////

		case 'allocatenumber':    // Allocate existing phone number (not already allocated) to an existing CI
			$sClass = utils::ReadParam('class', '', false, 'class');
			$id = utils::ReadParam('id', '');
			// Check if right parameters have been given
			if (empty($sClass) || empty($id)) {
				throw new ApplicationException(Dict::Format('UI:Error:2ParametersMissing', 'class', 'id'));
			}
			if ($sClass != 'TipPhoneNumber') {
				throw new ApplicationException(Dict::Format('UI:Error:WrongActionForClass', $operation, $sClass));
			}

			// Check if the object exists
			$oObj = MetaModel::GetObject($sClass, $id, false /* MustBeFound */);
			if (is_null($oObj)) {
				$oP->set_title(Dict::S('UI:ErrorPageTitle'));
				$oP->P(Dict::S('UI:ObjectDoesNotExist'));
			} else {
				// The object can be read - Check now that user is allowed to modify it
				$oSet = CMDBObjectSet::FromObject($oObj);
				if (UserRights::IsActionAllowed($sClass, UR_ACTION_MODIFY, $oSet) == UR_ALLOWED_NO) {
					throw new SecurityException('User not allowed to modify this object', array('class' => $sClass, 'id' => $id));
				}

				if ($oObj->Get('status') == 'allocated') {
					DisplayMessage::Warning($oP, Dict::S('UI:PNManagement:Action:Allocate:TipPhoneNumber:NumberAlreadyAllocated'));
					IPUtils::DisplayDetails($oP, $oObj);
				} else {
					$oObj->DisplayAllocateForm($oP, $oAppContext);
				}
			}
			break; // End case allocatenumber

		///////////////////////////////////////////////////////////////////////////////////////////

		case 'doallocatenumber':    // Apply allocation of phone number to a CI
			$sClass = utils::ReadPostedParam('class', '', 'class');
			$id = utils::ReadPostedParam('id', '');
			$sTransactionId = utils::ReadPostedParam('transaction_id', '', 'transaction_id');

			// Check if right parameters have been given
			if (empty($sClass) || empty($id)) {
				throw new ApplicationException(Dict::Format('UI:Error:2ParametersMissing', 'class', 'id'));
			}
			if ($sClass != 'TipPhoneNumber') {
				throw new ApplicationException(Dict::Format('UI:Error:WrongActionForClass', $operation, $sClass));
			}

			// Object does exist. It has already been checked in action allocatenumber but check anyway.
			$oObj = MetaModel::GetObject($sClass, $id, true /* MustBeFound */);
			$oSet = CMDBObjectSet::FromObject($oObj);
			if (UserRights::IsActionAllowed($sClass, UR_ACTION_MODIFY, $oSet) == UR_ALLOWED_NO) {
				throw new SecurityException('User not allowed to modify this object', array('class' => $sClass, 'id' => $id));
			}

			// Make sure we don't follow the same path twice in a row.
			$sClassLabel = MetaModel::GetName($sClass);
			if (!utils::IsTransactionValid($sTransactionId, false)) {
				$oP->set_title(Dict::Format('UI:ModificationPageTitle_Object_Class', $oObj->GetName(), $sClassLabel));
				$oP->p("<strong>".Dict::S('UI:Error:ObjectAlreadyUpdated')."</strong>\n");
			} else {
				$aPostedParam = [
					'ciclass' => utils::ReadPostedParam('attr_ciclass', '', 'class'),
					'ci_id' => utils::ReadPostedParam('attr_ci_id', 0, 'integer'),
					'pnattribute' => utils::ReadPostedParam('attr_pnattribute', '', 'field_name'),
				];

				// Make sure number can be allocated
				$sErrorString = $oObj->DoCheckToAllocate($aPostedParam);
				if ($sErrorString != '') {
					// Found issues, explain and give the user another chance
					$sMessage = Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:CannotBeAllocated', $sErrorString);
					DisplayMessage::Warning($oP, $sMessage);
					$oObj->DisplayAllocateForm($oP, $oAppContext, $aPostedParam);
				} else {
					// Allocate number
					$oObj->DoAllocate($aPostedParam);

					// Display result
					$oP->set_title(Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:PageTitle_Object_Class', $oObj->GetName(), $sClassLabel));
					$sMessage = Dict::Format('UI:PNManagement:Action:Allocate:TipPhoneNumber:Done', $sClassLabel, $oObj->GetName());
					DisplayMessage::Success($oP, $sMessage);
					IPUtils::DisplayDetails($oP, $oObj);
				}
			}
			break; // End case doallocatenumber

		///////////////////////////////////////////////////////////////////////////////////////////

		case 'unallocatenumber':    // Unallocate allocated phone number from all CIs
			$sClass = utils::ReadParam('class', '', false, 'class');
			$id = utils::ReadParam('id', '');
			// Check if right parameters have been given
			if (empty($sClass) || empty($id)) {
				throw new ApplicationException(Dict::Format('UI:Error:2ParametersMissing', 'class', 'id'));
			}
			if ($sClass != 'TipPhoneNumber') {
				throw new ApplicationException(Dict::Format('UI:Error:WrongActionForClass', $operation, $sClass));
			}

			// Check if the object exists
			$oObj = MetaModel::GetObject($sClass, $id, false /* MustBeFound */);
			if (is_null($oObj)) {
				$oP->set_title(Dict::S('UI:ErrorPageTitle'));
				$oP->P(Dict::S('UI:ObjectDoesNotExist'));
			} else {
				// The object can be read - Check now that user is allowed to modify it
				$oSet = CMDBObjectSet::FromObject($oObj);
				if (UserRights::IsActionAllowed($sClass, UR_ACTION_MODIFY, $oSet) == UR_ALLOWED_NO) {
					throw new SecurityException('User not allowed to modify this object', array('class' => $sClass, 'id' => $id));
				}

				// Make sure object can be unallocated
				$sErrorString = $oObj->DoCheckToUnallocate();
				if ($sErrorString != '') {
					// Found issues: explain and display object again
					$sMessage = Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:CannotBeUnallocated', $sErrorString);
					DisplayMessage::Warning($oP, $sMessage);
					IPUtils::DisplayDetails($oP, $oObj);
				} else {
					// Unallocate number
					$oObj->DoUnallocate();

					// Display result
					$sClassLabel = MetaModel::GetName($sClass);
					$oP->set_title(Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:PageTitle_Object_Class', $oObj->GetName(), $sClassLabel));
					$sMessage = Dict::Format('UI:PNManagement:Action:UnAllocate:TipPhoneNumber:Done', $sClassLabel, $oObj->GetName());
					DisplayMessage::Success($oP, $sMessage);
					IPUtils::DisplayDetails($oP, $oObj);
				}
			}
			break; // End case unallocatenumber

		///////////////////////////////////////////////////////////////////////////////////////////

		default: // Menu node rendering (templates)
			ApplicationMenu::LoadAdditionalMenus();
			$oMenuNode = ApplicationMenu::GetMenuNode(ApplicationMenu::GetMenuIndexById(ApplicationMenu::GetActiveNodeId()));
			if (is_object($oMenuNode)) {
				$oMenuNode->RenderContent($oP, $oAppContext->GetAsHash());
				$oP->set_title($oMenuNode->GetLabel());
			}
			break;
	}
	$oP->output(); // Display the whole content now !
} catch (CoreException $e) {
	require_once(APPROOT.'/setup/setuppage.class.inc.php');
	$oP = new SetupPage(Dict::S('UI:PageTitle:FatalError'));
	if ($e instanceof SecurityException) {
		$oP->add("<h1>".Dict::S('UI:SystemIntrusion')."</h1>\n");
	} else {
		$oP->add("<h1>".Dict::S('UI:FatalErrorMessage')."</h1>\n");
	}
	$oP->error(Dict::Format('UI:Error_Details', $e->getHtmlDesc()));
	$oP->output();
	IssueLog::Error($e->getMessage());
} catch (Exception $e) {
	require_once(APPROOT.'/setup/setuppage.class.inc.php');
	$oP = new SetupPage(Dict::S('UI:PageTitle:FatalError'));
	$oP->add("<h1>".Dict::S('UI:FatalErrorMessage')."</h1>\n");
	$oP->error(Dict::Format('UI:Error_Details', $e->getMessage()));
	$oP->output();
	IssueLog::Error($e->getMessage());
}
