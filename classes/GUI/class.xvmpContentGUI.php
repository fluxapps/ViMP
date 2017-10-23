<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpContentGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpContentGUI: ilObjViMPGUI
 */
class xvmpContentGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_CONTENT;

	/**
	 * @param $cmd
	 */
	protected function performCommand($cmd) {
		$this->{$cmd}();
	}

	protected function index() {

	}
}