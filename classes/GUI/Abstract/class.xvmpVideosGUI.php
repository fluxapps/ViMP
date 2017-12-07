<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpVideosGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_VIDEOS;
	const SUBTAB_ACTIVE = ''; // overwrite in subclass

	const SUBTAB_SEARCH = 'search_videos';
	const SUBTAB_SELECTED = 'selected_videos';
	const SUBTAB_OWN = 'own_videos';

	const CMD_SHOW_FILTERED = 'showFiltered';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_ADD_VIDEO = 'addVideo';
	const CMD_REMOVE_VIDEO = 'removeVideo';


	const TABLE_CLASS = '';


	/**
	 * @param $cmd
	 */
	protected function performCommand($cmd) {
		xvmpVideoPlayer::loadVideoJSAndCSS(false);

		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_SHOW_FILTERED:
				if (!$this->ctrl->isAsynch()) {
					$this->initUploadButton();
					$this->setSubTabs();
					$this->tabs->activateSubTab(static::SUBTAB_ACTIVE);
				}
				$this->{$cmd}();
				break;
			default:
				$this->{$cmd}();
				break;
		}
 	}

	/**
	 *
	 */
	protected function setSubTabs() {
		if (ilObjViMPAccess::hasWriteAccess()) {
			$this->tabs->addSubTab(self::SUBTAB_SEARCH, $this->pl->txt(self::SUBTAB_SEARCH), $this->ctrl->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
			$this->tabs->addSubTab(self::SUBTAB_SELECTED, $this->pl->txt(self::SUBTAB_SELECTED), $this->ctrl->getLinkTargetByClass(xvmpSelectedVideosGUI::class, xvmpSelectedVideosGUI::CMD_STANDARD));
			$this->tabs->addSubTab(self::SUBTAB_OWN, $this->pl->txt(self::SUBTAB_OWN), $this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_STANDARD));
		}
	}


	/**
	 *
	 */
	protected function index() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_STANDARD);
		$this->tpl->setContent($table_gui->getHTML() . $this->getModalPlayer()->getHTML());
	}


	/**
	 *
	 */
	protected function showFiltered() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_STANDARD);
		$table_gui->parseData();
		$this->tpl->setContent($table_gui->getHTML() . $this->getModalPlayer()->getHTML());
	}


	/**
	 *
	 */
	public function applyFilter() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_STANDARD);
		$table_gui->resetOffset();
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, self::CMD_SHOW_FILTERED);
	}


	/**
	 *
	 */
	public function resetFilter() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_STANDARD);
		$table_gui->resetOffset();
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	public function toggleVideo() {
		$mid = $_GET['mid'];
		$checked = $_GET['checked'];
		$visible = $_GET['visible'];
		if ($checked) {
			xvmpSelectedMedia::addVideo($mid, $this->getObjId(), $visible);
		} else {
			xvmpSelectedMedia::removeVideo($mid, $this->getObjId());
		}
		echo "{\"success\": true}";
		exit;
	}

	/**
	 * ajax
	 */
	public function addVideo() {
		$mid = $_GET['mid'];
		$visible = $_GET['visible'];
		xvmpSelectedMedia::addVideo($mid, $this->getObjId(), $visible);
		echo "{\"success\": true}";
		exit;
	}

	/**
	 * ajax
	 */
	public function removeVideo() {
		$mid = $_GET['mid'];
		xvmpSelectedMedia::removeVideo($mid, $this->getObjId());
		echo "{\"success\": true}";
		exit;
	}




	/**
	 *
	 */
	protected function initUploadButton() {
		$upload_button = ilLinkButton::getInstance();
		$upload_button->setCaption($this->pl->txt('upload_video'), false);
		$upload_button->setUrl($this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_UPLOAD_VIDEO_FORM));
		$this->toolbar->addButtonInstance($upload_button);
	}
}