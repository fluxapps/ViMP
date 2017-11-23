<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpEventLogGUI
 *
 * @ilCtrl_isCalledBy xvmpEventLogGUI: ilObjViMPGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEventLogGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_LOG;

	/**
	 *
	 */
	public function executeCommand() {
		if (!ilObjViMPAccess::hasWriteAccess()) {
			ilUtil::sendFailure($this->pl->txt('access_denied'), true);
			$this->ctrl->redirect($this->parent_gui, ilObjViMPGUI::CMD_SHOW_CONTENT);
		}
		parent::executeCommand();
	}

	protected function index() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->parseData();
		$this->tpl->setContent($xvmpEventLogTableGUI->getHTML());
	}

	protected function applyFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->writeFilterToSession();
		$xvmpEventLogTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	protected function resetFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->resetFilter();
		$xvmpEventLogTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}
}