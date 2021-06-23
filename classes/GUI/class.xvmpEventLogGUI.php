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
			$this->accessDenied();
		}
		parent::executeCommand();
	}

	protected function index() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->parseData();
		$this->dic->ui()->mainTemplate()->setContent($xvmpEventLogTableGUI->getHTML());
	}

	protected function applyFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->writeFilterToSession();
		$xvmpEventLogTableGUI->resetOffset();
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}

	protected function resetFilter() {
		$xvmpEventLogTableGUI = new xvmpEventLogTableGUI($this, self::CMD_STANDARD);
		$xvmpEventLogTableGUI->resetFilter();
		$xvmpEventLogTableGUI->resetOffset();
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}
}
