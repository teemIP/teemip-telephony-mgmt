<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Controller;

use ApplicationContext;
use cmdbAbstractObject;
use CMDBObjectSet;
use Combodo\iTop\Application\TwigBase\Controller\Controller;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use Exception;
use IssueLog;
use MetaModel;
use TeemIp\TeemIp\Extension\Framework\Helper\DisplayTree;
use utils;
use WebPage;

class PhoneNumberMgmtController extends Controller
{
    public const ROUTE_NAMESPACE = 'phone_number_mgmt';

	public function __construct($sViewPath = '', $sModuleName = 'teemip-phone-number-mgmt', $aAdditionalPaths = [])
	{
		$sModuleName = 'teemip-phone-number-mgmt';
		$sViewPath = MODULESROOT.$sModuleName.'/templates';
		parent::__construct($sViewPath, $sModuleName, $aAdditionalPaths);

		$this->CheckAccess();
	}

    /**
     * List available wiring paths between 2 PathPanels
     *
     */
    public function OperationDisplayTree()
    {
        $iCurrentOrganization = utils::ReadParam('org_id', '', false, 'raw_data');
        $sFilter = utils::ReadParam('filter', '', false, 'raw_data');
        $sDelegatedNodesRendering = utils::ReadParam('delegated_nodes_rendering', 'folded', false, 'raw_data');

        $oDisplayTree = new DisplayTree();
        $aParams['Class'] = MetaModel::GetName('TipPNRange');
        $aParams['sHtml'] = $oDisplayTree->GetTree('TipPNRange', $iCurrentOrganization, $sDelegatedNodesRendering);;

        $aParams['sCancelURL'] = utils::GetAbsoluteUrlAppRoot().'pages/UI.php?operation=search&filter='.$sFilter;

        $this->m_sOperation = 'DisplayTree';
        $this->AddLinkedStylesheet(utils::GetAbsoluteUrlModulesRoot().'teemip-framework/asset/css/teemip-display-tree.css');
        $this->DisplayPage($aParams);
    }

}
