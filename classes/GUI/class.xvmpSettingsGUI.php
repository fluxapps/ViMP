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
		$this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/xvmp_settings.css'));
		$xvmpSettingsFormGUI = new xvmpSettingsFormGUI($this);
		$this->dic->ui()->mainTemplate()->setContent($xvmpSettingsFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function update() {
		$xvmpSettingsFormGUI = new xvmpSettingsFormGUI($this);
		$xvmpSettingsFormGUI->setValuesByPost();
		if (!$xvmpSettingsFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'), true);
			$this->dic->ui()->mainTemplate()->setContent($xvmpSettingsFormGUI->getHTML());
		}
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}

}
