<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpLearningProgressGUI
 *
 * @ilCtrl_isCalledBy xvmpLearningProgressGUI: ilObjViMPGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpLearningProgressGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_LEARNING_PROGRESS;


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
		$xvmpLearningProgressTableGUI = new xvmpLearningProgressTableGUI($this, self::CMD_STANDARD);
		$this->tpl->setContent($xvmpLearningProgressTableGUI->getHTML());
	}

	public function setRelevance() {
		$mid = $_GET['mid'];
		$relevance = $_GET['relevance'];
	}
}