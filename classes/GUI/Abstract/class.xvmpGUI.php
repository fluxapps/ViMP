<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpGUI {

	const CMD_STANDARD = 'index';
	const CMD_CANCEL = 'cancel';
	const CMD_FLUSH_CACHE = 'flushCache';
	const CMD_FILL_MODAL = 'fillModalPlayer';

	const TAB_ACTIVE = ''; // overwrite in subclass
	/**
	 * @var ilObjViMPGUI
	 */
	protected $parent_gui;

	public function __construct(ilObjViMPGUI $parent_gui) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];
		/**
		 * @var $ilCtrl    ilCtrl
		 * @var $ilTabs    ilTabsGUI
		 * @var $tpl       ilTemplate
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->user = $ilUser;
		$this->pl = ilViMPPlugin::getInstance();
		$this->lng = $lng;
		$this->parent_gui = $parent_gui;
	}


	public function executeCommand() {
		if (!$this->ctrl->isAsynch()) {
			$this->tabs->activateTab(static::TAB_ACTIVE);
		}

		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
				$this->performCommand($cmd);
				break;
		}
	}

	/**
	 * @param $cmd
	 */
	protected function performCommand($cmd) {
		switch ($cmd) {
			case self::CMD_FILL_MODAL:
				$mid = $_GET['mid'];
				if (!$mid || !xvmpSelectedMedia::isSelected($mid, $this->getObjId())) {
					ilUtil::sendFailure($this->pl->txt('access_denied'), true);
					$this->ctrl->redirect($this->parent_gui, ilObjViMPGUI::CMD_SHOW_CONTENT);
				}
		}

		$this->{$cmd}();
	}

	public function addFlushCacheButton () {
		$button = ilLinkButton::getInstance();
		$button->setUrl($this->ctrl->getLinkTarget($this,self::CMD_FLUSH_CACHE));
		$button->setCaption($this->pl->txt('flush_video_cache'), false);
		$button->setId('xvmp_flush_video_cache');
		$this->toolbar->addButtonInstance($button);

		ilTooltipGUI::addTooltip('xvmp_flush_video_cache', $this->pl->txt('flush_video_cache_tooltip'));
	}

	/**
	 *
	 */
	public function flushCache() {
//		xvmpCacheFactory::getInstance()->flush();
		foreach (xvmpSelectedMedia::getSelected($this->getObjId()) as $selected) {
			xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $selected->getMid());
		}
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}


	abstract protected function index();


	/**
	 *
	 */
	protected function cancel() {
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @return ilObjViMP
	 */
	public function getObject() {
		return $this->parent_gui->object;
	}

	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->parent_gui->obj_id;
	}

	/**
	 * @return ilModalGUI
	 */
	public function getModalPlayer() {
		$modal = ilModalGUI::getInstance();
		$modal->setId('xvmp_modal_player');
		$modal->setType(ilModalGUI::TYPE_LARGE);
//		$modal->setHeading('<div id="xoct_waiter_modal" class="xoct_waiter xoct_waiter_mini"></div>');
		$modal->setBody('<section><div id="xvmp_video_container"></div></section>');
		return $modal;
	}


	/**
	 * ajax
	 */
	public function fillModalPlayer() {
		$mid = $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$video_infos = "				
			<p>{$this->pl->txt(xvmpMedium::F_DURATION)}: {$video->getDurationFormatted()}</p>
			<p>{$this->pl->txt(xvmpMedium::F_CREATED_AT)}: {$video->getCreatedAt('m.d.Y, H:i')}</p>
			<p class='xvmp_ellipsis'>{$this->pl->txt(xvmpMedium::F_DESCRIPTION)}: {$video->getDescription()}</p>
		";
		$response = new stdClass();
		$video_player = new xvmpVideoPlayer($video);
		$response->html = $video_player->getHTML() . $video_infos;
		$response->video_title = $video->getTitle();
		/** @var xvmpUserProgress $progress */
		$progress = xvmpUserProgress::where(array(xvmpUserProgress::F_USR_ID => $this->user->getId(), xvmpMedium::F_MID => $mid))->first();
		if ($progress) {
			$response->time_ranges = json_decode($progress->getRanges());
		} else {
			$response->time_ranges = array();
		}
		echo json_encode($response);
		exit;
	}



	/**
	 * ajax
	 */
	public function updateProgress() {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$mid = $_POST[xvmpMedium::F_MID];
		$ranges = $_POST[xvmpUserProgress::F_RANGES];
		xvmpUserProgress::storeProgress($ilUser->getid(), $mid, $ranges);
		echo "ok";
		exit;
	}

}