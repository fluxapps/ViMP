<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSettingsGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpSettingsGUI: ilObjViMPGUI
 */
class xvmpSettingsGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_SETTINGS;

	const CMD_UPDATE = 'update';


	/**
	 *
	 */
	public function executeCommand() {
		if (!ilObjViMPAccess::hasWriteAccess()) {
			$this->accessDenied();
		}
		parent::executeCommand();
	}


	/**
	 *
	 */
	protected function index() {
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/xvmp_settings.css');
		$xvmpSettingsFormGUI = new xvmpSettingsFormGUI($this);
		$this->tpl->setContent($xvmpSettingsFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function update() {
		$xvmpSettingsFormGUI = new xvmpSettingsFormGUI($this);
		$xvmpSettingsFormGUI->setValuesByPost();
		if (!$xvmpSettingsFormGUI->saveForm()) {
			ilUtil::sendFailure($this->pl->txt('msg_incomplete'));
			$this->tpl->setContent($xvmpSettingsFormGUI->getHTML());
		}
		ilUtil::sendSuccess($this->pl->txt('form_saved'), true);
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

}