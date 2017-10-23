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
		// Always set
		$this->timings_enabled = true;
		$this->subscribe_enabled = true;
		$this->payment_enabled = false;
		$this->link_enabled = false;
		$this->info_screen_enabled = true;
		$this->delete_enabled = true;
		$this->notes_enabled = true;
		$this->comments_enabled = true;

		// Should be overwritten according to status
		$this->cut_enabled = true;
		$this->copy_enabled = true;

		$commands = array(
			array(
				'permission' => 'read',
				'cmd' => ilObjViMPGUI::CMD_SHOW_CONTENT,
				'default' => true,
			),
			array(
				'permission' => 'write',
				'cmd' => ilObjViMPGUI::CMD_SHOW_CONTENT,
				'lang_var' => 'edit'
			)
		);

		return $commands;
	}


	function initType() {
		$this->setType(ilViMPPlugin::XVMP);
	}
}