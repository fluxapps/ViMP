<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

/**
 * Class ilViMPConfigGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilViMPConfigGUI extends ilPluginConfigGUI {

	const CMD_STANDARD = 'configure';
	const CMD_UPDATE = 'update';

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
	 * ilViMPConfigGUI constructor.
	 */
	public function __construct() {
		global $tpl, $ilCtrl;
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


	/**
	 *
	 */
	protected function configure() {
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