<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\UIComponents\Player\VideoPlayer;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;

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

	const CMD_SHOW = 'show';
	const CMD_SHOW_FILTERED = 'showFiltered';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_ADD_VIDEO = 'addVideo';
	const CMD_TOGGLE_VIDEO = 'toggleVideo';
	const CMD_REMOVE_VIDEO = 'removeVideo';


	const TABLE_CLASS = '';


	/**
	 * @param $cmd
	 */
	protected function performCommand($cmd) {
		VideoPlayer::loadVideoJSAndCSS(false);

		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_SHOW:
			case self::CMD_SHOW_FILTERED:
				if (!$this->dic->ctrl()->isAsynch()) {
					$this->setSubTabs();
					$this->dic->tabs()->activateSubTab(static::SUBTAB_ACTIVE);
					$this->initUploadButton();
				}
				break;
			case self::CMD_TOGGLE_VIDEO:
				$mid = $_GET[xvmpMedium::F_MID];
				$medium = xvmpMedium::find($mid);
				$checked = $_GET['checked'];
				if ($checked) {
					ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_ADD_VIDEO, $this, $medium);
				} else {
					ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_REMOVE_VIDEO, $this, $medium);
				}
				break;
		}
		parent::performCommand($cmd);
	}

	/**
	 *
	 */
	protected function setSubTabs() {
		if (ilObjViMPAccess::hasWriteAccess()) {
			$this->dic->tabs()->addSubTab(self::SUBTAB_SEARCH, $this->pl->txt(self::SUBTAB_SEARCH), $this->dic->ctrl()->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
			$this->dic->tabs()->addSubTab(self::SUBTAB_SELECTED, $this->pl->txt(self::SUBTAB_SELECTED), $this->dic->ctrl()->getLinkTargetByClass(xvmpSelectedVideosGUI::class, xvmpSelectedVideosGUI::CMD_STANDARD));
			$this->dic->tabs()->addSubTab(self::SUBTAB_OWN, $this->pl->txt(self::SUBTAB_OWN), $this->dic->ctrl()->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_STANDARD));
		}
	}


	/**
	 *
	 */
	protected function index() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_SHOW);
		$this->dic->ui()->mainTemplate()->setContent($table_gui->getHTML() . $this->getModalPlayer()->getHTML());
	}


	/**
	 *
	 */
	protected function show() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_SHOW);
		$table_gui->parseData();
		$table_gui->determineOffsetAndOrder();
		$this->dic->ui()->mainTemplate()->setContent($table_gui->getHTML() . $this->getModalPlayer()->getHTML());
	}

	/**
	 *
	 */
	protected function showFiltered() {
		$class_name = static::TABLE_CLASS;
		/** @var xvmpTableGUI $table_gui */
		$table_gui = new $class_name($this, self::CMD_SHOW_FILTERED);
		$table_gui->parseData();
		$table_gui->determineOffsetAndOrder();
		$this->dic->ui()->mainTemplate()->setContent($table_gui->getHTML() . $this->getModalPlayer()->getHTML());
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
		$this->dic->ctrl()->redirect($this, self::CMD_SHOW_FILTERED);
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
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}

	public function toggleVideo() {
		$mid = $_GET[xvmpMedium::F_MID];
		$checked = $_GET['checked'];
		$visible = $_GET[SelectedMediaAR::F_VISIBLE];
		if ($checked) {
            $this->db_service->addVideoToSelected($this->getObjId(), $mid, $visible);
		} else {
            $this->db_service->removeVideoFromSelected($this->getObjId(), $mid);
		}
		echo "{\"success\": true}";
		exit;
	}

	/**
	 * ajax
	 */
	public function addVideo() {
		$mid = $_GET[xvmpMedium::F_MID];
		$visible = $_GET[SelectedMediaAR::F_VISIBLE];
        try {
            $this->db_service->addVideoToSelected($this->getObjId(), $mid, $visible);
            echo "{\"success\": true}";
            exit;
        } catch (Exception $e) {
            //ToDo
        }

	}

	/**
	 * ajax
	 */
	public function removeVideo() {
		$mid = $_GET[xvmpMedium::F_MID];
		try {
            $this->db_service->removeVideoFromSelected($this->getObjId(), $mid);
            echo "{\"success\": true}";
            exit;
        } catch (Exception $e) {
            // ToDo
        }
	}




	/**
	 *
	 */
	protected function initUploadButton() {
		$upload_button = ilLinkButton::getInstance();
		$upload_button->setCaption($this->pl->txt('upload_video'), false);
		$upload_button->setUrl($this->dic->ctrl()->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_UPLOAD_VIDEO_FORM));
		$this->dic->toolbar()->addButtonInstance($upload_button);
	}

    protected function getVideoPlayer($video, int $obj_id) : VideoPlayer
    {
        return (new VideoPlayer($video, xvmp::isUseEmbeddedPlayer($obj_id, $video), false));
    }

}
