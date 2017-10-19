<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');

/**
 * Class ilViMPConfigGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilViMPConfigGUI extends ilPluginConfigGUI {

	function performCommand($cmd) {
		switch ($cmd) {
			default:
				$this->{$cmd}();
				break;
		}
	}


	public function configure() {

	}
}