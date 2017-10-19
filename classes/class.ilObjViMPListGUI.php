<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
 * Class ilObjViMPListGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjViMPListGUI extends ilObjectPluginListGUI {

	function getGuiClass() {
		return ilObjViMPGUI::class;
	}


	function initCommands() {
		// TODO: Implement initCommands() method.
	}


	function initType() {
		$this->setType(ilViMPPlugin::XVMP);
	}
}