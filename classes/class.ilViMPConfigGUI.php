<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilViMPConfigGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilViMPConfigGUI extends ilPluginConfigGUI {

	const CMD_STANDARD = 'configure';
	const CMD_UPDATE = 'update';
	const CMD_FLUSH_CACHE = 'flushCache';

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
	 * ilViMPConfigGUI constructor.
	 */
	public function __construct() {
		global $tpl, $ilCtrl, $ilToolbar;
		$this->toolbar = $ilToolbar;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->pl = ilViMPPlugin::getInstance();
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			default:
				$this->{$cmd}();
				break;
		}
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
			ilUtil::sendSuccess($this->pl->txt('msg_success'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$this->tpl->setContent($xvmpConfFormGUI->getHTML());
	}
}