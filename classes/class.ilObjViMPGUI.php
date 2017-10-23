<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';

/**
 * Class ilObjViMPGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjViMPGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjViMPGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjViMPGUI extends ilObjectPluginGUI {

	const CMD_SHOW_CONTENT = 'showContent';
	const CMD_SEARCH_VIDEOS = 'searchVideos';

	const TAB_CONTENT = 'content';
	const TAB_INFO = 'info';
	const TAB_VIDEOS = 'videos';
	const TAB_SETTINGS = 'settings';
	const TAB_PERMISSION = 'permissions';

	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilObjViMP
	 */
	protected $obj;

	public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0) {
		parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
		$this->pl = ilViMPPlugin::getInstance();
	}


	public function executeCommand() {
		$this->checkPermission('read');
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();
		$this->tpl->getStandardTemplate();

		try {
			switch ($next_class) {
				case 'xvmpcontentgui':
				case 'xvmpownvideosgui':
				case 'xvmpsearchvideosgui':
				case 'xvmpselectedvideosgui':
				case 'xvmpsettingsgui':
					$this->initHeader();
					$this->setTabs();
					$xvmpContentGUI = new $next_class($this);
					$this->ctrl->forwardCommand($xvmpContentGUI);
					$this->tpl->show();
					break;
				case 'ilpermissiongui':
					$this->initHeader(false);
					parent::executeCommand();
					break;
				default:
					// workaround for object deletion; 'parent::executeCommand()' shows the template and leads to "Headers already sent" error
					if ($next_class == "" && $cmd == 'deleteObject') {
						$this->deleteObject();
						break;
					}
					parent::executeCommand();
					break;
			}
		} catch (Exception $e) {
			ilUtil::sendFailure($e->getMessage());
			$this->tpl->show();
		}

	}


	public function performCommand($cmd) {
		switch ($cmd) {
			default:
				$this->$cmd();
				break;
		}
	}

	/**
	 * @return xoctOpenCast
	 */
	protected function initHeader($render_locator = true) {
		if ($render_locator) {
			$this->setLocator();
		}


		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setDescription($this->object->getDescription());

		require_once('./Services/Object/classes/class.ilObjectListGUIFactory.php');
		$list_gui = ilObjectListGUIFactory::_getListGUIByType('xvmp');
		/**
		 * @var $list_gui ilObjViMPListGUI
		 */
//		if (!$this->object->) {
//			$this->tpl->setAlertProperties($list_gui->getAlertProperties());
//		}

//		$this->tpl->setTitleIcon(ilObjViMP::_getIcon($this->object_id));
		$this->tpl->setPermanentLink('xvmp', $_GET['ref_id']);
	}

	/**
	 * @return bool
	 */
	protected function setTabs() {
		global $lng, $ilUser, $tree;

		$this->tabs_gui->addTab(self::TAB_CONTENT, $this->pl->txt(self::TAB_CONTENT), $this->ctrl->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD));
		$this->tabs_gui->addTab(self::TAB_INFO, $this->pl->txt(self::TAB_INFO), $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class));
		$this->tabs_gui->addTab(self::TAB_VIDEOS, $this->pl->txt(self::TAB_VIDEOS), $this->ctrl->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
		$this->tabs_gui->addTab(self::TAB_SETTINGS, $this->pl->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTargetByClass(xvmpSettingsGUI::class, xvmpSettingsGUI::CMD_STANDARD));


		if ($this->checkPermissionBool("edit_permission")) {
			$this->tabs_gui->addTab("perm_settings", $lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(
				get_class($this),
				"ilpermissiongui",
			), "perm"));
		}

		return true;
	}


	/**
	 * called by the button to test connection inside the plugin config
	 */
	public function testConnectionAjax() {
		$apikey = $_GET['apikey'];
		$apiurl = $_GET['apiurl'];

		$xvmpCurl = new xvmpCurl(rtrim($apiurl, '/') . '/' . ltrim(xvmpRequest::VERSION, '/'));
		$xvmpCurl->addPostField('apikey', $apikey);
		try {
			$xvmpCurl->post();
			echo "Connection OK";
			exit;
		} catch (Exception $e) {
			$message = 'No Connection, Status Code ' . $e->getCode();
			switch ($e->getCode()) {
				case 401:
					$message .= ' - No Authorization, possibly wrong API-Key';
					break;
				case 404:
					$message .= ' - Not Found, possibly wrong relative URL';
					break;
				case 500:
					$message .= ' - Internal Server Error, possibly wrong URL';
					break;
			}
			echo $message;
			exit;
		}
	}

	public function getType() {
		return ilViMPPlugin::XVMP;
	}


	public function getAfterCreationCmd() {
		return self::CMD_SHOW_CONTENT;
	}


	public function getStandardCmd() {
		return self::CMD_SHOW_CONTENT;
	}

	public function showContent() {
		$this->ctrl->redirectByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD);
	}

	public function searchVideos() {
		$this->ctrl->redirectByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD);
	}
}