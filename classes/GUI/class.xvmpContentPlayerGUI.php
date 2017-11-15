<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpContentPlayerGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentPlayerGUI {


	/**
	 * @var xvmpContentGUI
	 */
	protected $parent_gui;

	/**
	 * xvmpContentTilesGUI constructor.
	 */
	public function __construct($parent_gui) {
		global $tpl, $ilCtrl, $ilTabs, $ilToolbar, $ilUser, $lng;
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

		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_player.css');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/xvmp_content.js');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/waiter.js');
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');

		ilTooltipGUI::initLibrary();
	}

	public function show() {
		$mid = $_GET['mid'] ? $_GET['mid'] : xvmpSelectedMedia::where(array('obj_id' => $this->parent_gui->getObjId(), 'visible' => 1))->orderBy('sort')->first()->getMid();
		$video = xvmpMedium::find($mid);

		$player_tpl = new ilTemplate('tpl.content_player.html', true, true, $this->pl->getDirectory());
		$video_player = new xvmpVideoPlayer($video);
		$player_tpl->setVariable('VIDEO', $video_player->getHTML());
		$player_tpl->setVariable('TITLE', $video->getTitle());
		$player_tpl->setVariable('DESCRIPTION', $video->getDescription());
		$player_tpl->setVariable('LABEL_DURATION', $this->pl->txt('duration'));
		$player_tpl->setVariable('DURATION', strip_tags($video->getDurationFormatted()));
		$player_tpl->setVariable('LABEL_AUTHOR', $this->pl->txt('author'));
		$player_tpl->setVariable('AUTHOR', $video->getCustomAuthor());
		$player_tpl->setVariable('LABEL_CREATED_AT', $this->pl->txt('created_at'));
		$player_tpl->setVariable('CREATED_AT', $video->getCreatedAt('d.m.Y, H:i'));


		$tiles_tpl = new ilTemplate('tpl.content_tiles_waiting.html', true, true, $this->pl->getDirectory());
		$selected_media = xvmpSelectedMedia::getSelected($this->parent_gui->getObjId(), true);
		$json_array = array();
		foreach ($selected_media as $media) {
			if ($media->getMid() == $mid) {
				continue;
			}
			$json_array[] = $media->getMid();
			$tiles_tpl->setCurrentBlock('block_box_clickable');
			$tiles_tpl->setVariable('MID', $media->getMid());

			$this->ctrl->setParameter($this->parent_gui, 'mid', $media->getMid());
			$tiles_tpl->setVariable('PLAY_LINK', $this->ctrl->getLinkTarget($this->parent_gui, xvmpContentGUI::CMD_STANDARD));
			$tiles_tpl->parseCurrentBlock();

//			$tooltip = "Title: {$video->getTitle()} \n
//			Description: {$video->getDescription()}
//			Duration: {$video->getDurationFormatted()}
//			Author: {$video->getCustomAuthor()}
//			Created At: {$video->getCreatedAt('d.m.Y, H:i')}";
//
//			ilTooltipGUI::addTooltip('box_' . $media->getMid(), $tooltip);

		}

		$player_tpl->setVariable('VIDEO_LIST', $tiles_tpl->get());

		$progress = xvmpUserProgress::where(array('usr_id' => $this->user->getId(), 'mid' => $mid))->first();
		if ($progress) {
			$time_ranges = $progress->getRanges();
		} else {
			$time_ranges = '[]';
		}

		$this->tpl->addOnLoadCode('VimpObserver.init(' . $mid  . ', ' . $time_ranges . ');');
		$this->tpl->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
		$this->tpl->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->ctrl->getLinkTarget($this->parent_gui, '', '', true) . "';");
		$this->tpl->addOnLoadCode("VimpContent.template = 'tiles';");
		$this->tpl->addOnLoadCode('VimpContent.loadTilesInOrder(0);');

		$this->tpl->setContent($player_tpl->get());
	}
}