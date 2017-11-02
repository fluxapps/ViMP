<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

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
	const TAB_INFO = 'info_short';
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
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd();
		if (!ilObjViMPAccess::hasReadAccess() && $next_class != "ilinfoscreengui" && $cmd != "infoScreen") {
			ilUtil::sendFailure($this->pl->txt('access_denied'), true);
			$this->ctrl->returnToParent($this);
		}
		$this->tpl->getStandardTemplate();

		try {
			switch ($next_class) {
				case 'xvmpcontentgui':
					if (!$this->ctrl->isAsynch()) {
						$this->initHeader();
						$this->setTabs();
					}
					$xvmpGUI = new xvmpContentGUI($this);
					$this->ctrl->forwardCommand($xvmpGUI);
					$this->tpl->show();
					break;
				case 'xvmpsearchvideosgui':
					if (!$this->ctrl->isAsynch()) {
						$this->initHeader();
						$this->setTabs();
					}
					$xvmpGUI = new xvmpSearchVideosGUI($this);
					$this->ctrl->forwardCommand($xvmpGUI);
					$this->tpl->show();
					break;
				case 'xvmpsettingsgui':
					if (!$this->ctrl->isAsynch()) {
						$this->initHeader();
						$this->setTabs();
					}
					$xvmpGUI = new xvmpSettingsGUI($this);
					$this->ctrl->forwardCommand($xvmpGUI);
					$this->tpl->show();
					break;
				case 'xvmpselectedvideosgui':
					if (!$this->ctrl->isAsynch()) {
						$this->initHeader();
						$this->setTabs();
					}
					$xvmpGUI = new xvmpSelectedVideosGUI($this);
					$this->ctrl->forwardCommand($xvmpGUI);
					$this->tpl->show();
					break;
				case 'xvmpownvideosgui':
					$this->initHeader();
					if (!$this->ctrl->isAsynch()) {
						$this->setTabs();
					}
					$xvmpGUI = new xvmpOwnVideosGUI($this);
					$this->ctrl->forwardCommand($xvmpGUI);
					$this->tpl->show();
					break;
				case "ilinfoscreengui":
					if (!$this->ctrl->isAsynch()) {
						$this->initHeader();
						$this->setTabs();
					}
					$this->checkPermission("visible");
					$this->infoScreen();	// forwards command
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


		$this->tpl->setTitleIcon(ilObjViMP::_getIcon($this->object_id));
		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setDescription($this->object->getDescription());

		if (!xvmpSettings::find($this->obj_id)->getIsOnline()) {
			require_once('./Services/Object/classes/class.ilObjectListGUIFactory.php');
			/**
			 * @var $list_gui ilObjViMPListGUI
			 */
			$list_gui = ilObjectListGUIFactory::_getListGUIByType('xvmp');
			$this->tpl->setAlertProperties($list_gui->getAlertProperties());
		}

//		$this->tpl->setTitleIcon(ilObjViMP::_getIcon($this->object_id));
		$this->tpl->setPermanentLink('xvmp', $_GET['ref_id']);
	}

	/**
	 * @return bool
	 */
	protected function setTabs() {
		global $lng;

		$this->tabs_gui->addTab(self::TAB_CONTENT, $this->pl->txt(self::TAB_CONTENT), $this->ctrl->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD));
		$this->tabs_gui->addTab(self::TAB_INFO, $this->pl->txt(self::TAB_INFO), $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class));

		if (ilObjViMPAccess::hasWriteAccess()) {
			$this->tabs_gui->addTab(self::TAB_VIDEOS, $this->pl->txt(self::TAB_VIDEOS), $this->ctrl->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
		} else if (ilObjViMPAccess::hasUploadPermission()) {
			$this->tabs_gui->addTab(self::TAB_VIDEOS, $this->pl->txt(self::TAB_VIDEOS), $this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_STANDARD));
		}

		if (ilObjViMPAccess::hasWriteAccess()) {
			$this->tabs_gui->addTab(self::TAB_SETTINGS, $this->pl->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTargetByClass(xvmpSettingsGUI::class, xvmpSettingsGUI::CMD_STANDARD));
		}


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