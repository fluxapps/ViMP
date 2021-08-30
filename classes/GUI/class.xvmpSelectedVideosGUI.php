<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;

/**
 * Class xvmpSelectedVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpSelectedVideosGUI: ilObjViMPGUI
 */
class xvmpSelectedVideosGUI extends xvmpVideosGUI {

	const SUBTAB_ACTIVE = xvmpVideosGUI::SUBTAB_SELECTED;

	const TABLE_CLASS = 'xvmpSelectedVideosTableGUI';

	const CMD_MOVE_UP = 'moveUp';
	const CMD_MOVE_DOWN = 'moveDown';
	const CMD_SET_VISIBILITY = 'setVisibility';


	public function executeCommand() {
		if (!ilObjViMPAccess::hasWriteAccess()) {
			$this->accessDenied();
		}

		if (!$this->dic->ctrl()->isAsynch()) {
			$this->addFlushCacheButton();
		}

		parent::executeCommand();
	}


	/**
	 * ajax
	 */
	public function reorder() {
		$ids = $_POST['ids'];
		$sort = 10;
		foreach ($ids as $id) {
			$SelectedMediaAR = SelectedMediaAR::where(array('mid' => $id, 'obj_id' => $this->getObjId()))->first();
			$SelectedMediaAR->setSort($sort);
			$SelectedMediaAR->update();
			$sort += 10;
		}
		echo "{\"success\": true}";
		exit;
	}


	/**
	 * ajax
	 */
	public function moveUp() {
		$mid = $_GET['mid'];
		SelectedMediaAR::moveUp($mid, $this->getObjId());
		exit;
	}

	/**
	 * ajax
	 */
	public function moveDown() {
		$mid = $_GET['mid'];
		SelectedMediaAR::moveDown($mid, $this->getObjId());
		exit;
	}


	/**
	 * ajax
	 */
	public function setVisibility() {
		$mid = $_GET['mid'];
		$visible = $_GET['visible'];
		/** @var SelectedMediaAR $video */
		$video = SelectedMediaAR::where(array('mid' => $mid, 'obj_id' => $this->getObjId()))->first();
		$video->setVisible($visible);
		$video->update();
		exit;
	}
}
