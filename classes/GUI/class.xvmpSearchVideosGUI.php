<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSearchVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpSearchVideosGUI: ilObjViMPGUI
 */
class xvmpSearchVideosGUI extends xvmpVideosGUI {

	const SUBTAB_ACTIVE = xvmpVideosGUI::SUBTAB_SEARCH;

	const TABLE_CLASS = 'xvmpSearchVideosTableGUI';


	public function executeCommand() {
		if (!ilObjViMPAccess::hasWriteAccess()) {
			ilUtil::sendFailure($this->pl->txt('access_denied'), true);
			$this->ctrl->redirect($this->parent_gui, ilObjViMPGUI::CMD_SHOW_CONTENT);
		}

		parent::executeCommand();
	}

}