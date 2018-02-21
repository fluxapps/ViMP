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

	const CMD_SAVE = 'save';


	/**
	 *
	 */
	public function executeCommand() {
		xvmpVideoPlayer::loadVideoJSAndCSS(false);
		if (!ilObjViMPAccess::hasWriteAccess()) {
			$this->accessDenied();
		}
		parent::executeCommand();
	}


	protected function index() {
		ilUtil::sendInfo($this->pl->txt('hint_learning_progress_gui'));
		$xvmpLearningProgressTableGUI = new xvmpLearningProgressTableGUI($this, self::CMD_STANDARD);
		$this->tpl->setContent($xvmpLearningProgressTableGUI->getHTML() . $this->getModalPlayer()->getHTML());
	}

	protected function save() {
		foreach ($_POST['lp_required_percentage'] as $mid => $percentage) {
			/** @var xvmpSelectedMedia $selected_medium */
			$selected_medium = xvmpSelectedMedia::where(array('mid' => $mid, 'obj_id' => $this->getObjId()))->first();
			$selected_medium->setLpReqPercentage($percentage);
			$selected_medium->setLpIsRequired((int) isset($_POST['lp_required'][$mid]));
			$selected_medium->update();
		}
		xvmpUserLPStatus::updateLPStatuses($this->getObjId(), false);
		ilUtil::sendSuccess($this->pl->txt('form_saved'), true);
		$this->ctrl->redirect($this,self::CMD_STANDARD);
	}
}