<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilViMPConfigGUI
 *
 * @ilCtrl_IsCalledBy  ilViMPConfigGUI: ilObjComponentSettingsGUI
 */
class ilViMPConfigGUI extends ilPluginConfigGUI {

	const CMD_STANDARD = 'configure';
	const CMD_UPDATE = 'update';
	const CMD_FLUSH_CACHE = 'flushCache';
	const CMD_SHOW_LOG = 'showLog';

	const SUBTAB_SETTINGS = 'settings';
	const SUBTAB_LOG = 'log';

	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * ilViMPConfigGUI constructor.
	 */
	public function __construct() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilTabs = $DIC['ilTabs'];
		$this->toolbar = $ilToolbar;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->pl = ilViMPPlugin::getInstance();
		$this->tabs = $ilTabs;
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd): void
    {
		$this->addSubTabs();
		switch ($cmd) {
			default:
				$this->{$cmd}();
				break;
		}
	}

	protected function addSubTabs() {
		$this->tabs->addSubTab(self::SUBTAB_SETTINGS, $this->pl->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));
		$this->tabs->addSubTab(self::SUBTAB_LOG, $this->pl->txt(self::SUBTAB_LOG), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_LOG));
	}

	protected function showLog() {
		$this->tabs->activateSubTab(self::SUBTAB_LOG);
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_SHOW_LOG);
		$xvmpEventLogTableGUI->parseData();
		$this->tpl->setContent($xvmpEventLogTableGUI->getHTML());
	}

	protected function applyFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_SHOW_LOG);
		$xvmpEventLogTableGUI->writeFilterToSession();
		$xvmpEventLogTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_SHOW_LOG);
	}

	protected function resetFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_SHOW_LOG);
		$xvmpEventLogTableGUI->resetFilter();
		$xvmpEventLogTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_SHOW_LOG);
	}

	public function addFlushCacheButton () {
		$button = ilLinkButton::getInstance();
		$button->setUrl($this->ctrl->getLinkTarget($this,self::CMD_FLUSH_CACHE));
		$button->setCaption($this->pl->txt('flush_cache'), false);
		$this->toolbar->addButtonInstance($button);
	}

	/**
	 *
	 */
	public function flushCache() {
		xvmpCacheFactory::getInstance()->flush();

		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	/**
	 *
	 */
	protected function configure() {
		$this->tabs->activateSubTab(self::SUBTAB_SETTINGS);
		$this->addFlushCacheButton();
		$xvmpConfFormGUI = new xvmpConfFormGUI($this);
		$xvmpConfFormGUI->fillForm();
		$this->tpl->setContent($xvmpConfFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function update() {
		$xvmpConfFormGUI = new xvmpConfFormGUI($this);
		$xvmpConfFormGUI->setValuesByPost();
		if ($xvmpConfFormGUI->saveObject()) {
            $this->tpl->setOnScreenMessage("success", $this->pl->txt('msg_success'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$this->tpl->setContent($xvmpConfFormGUI->getHTML());
	}
}