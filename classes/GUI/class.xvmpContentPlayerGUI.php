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

		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_player.css');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/js/xvmp_content.js');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/js/waiter.js');
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
	}


    /**
     * @return string|void
     * @throws arException
     * @throws ilTemplateException
     * @throws xvmpException
     */
	public function getHTML() {
		$selected_media = xvmpSelectedMedia::where(array('obj_id' => $this->parent_gui->getObjId(), 'visible' => 1))->orderBy('sort');
		if (!$selected_media->hasSets()) {
			ilUtil::sendInfo($this->pl->txt('msg_no_videos'));
			return;
		}

		$mid = $_GET['mid'] ? $_GET['mid'] : $selected_media->first()->getMid();

		try {
			$video = xvmpMedium::find($mid);
		} catch (Exception $e) {
			if ($e->getCode() != 404) {
				throw $e;
			}
			
		}

		$player_tpl = new ilTemplate('tpl.content_player.html', true, true, $this->pl->getDirectory());
		$video_player = new xvmpVideoPlayer($video, xvmp::useEmbeddedPlayer($this->parent_gui->getObjId(), $video));
		$player_tpl->setVariable('VIDEO', $video_player->getHTML());
		$player_tpl->setVariable('TITLE', $video->getTitle());
		$player_tpl->setVariable('DESCRIPTION', $video->getDescription(50));

		if ($video->getStatus() !== 'legal') {
			$player_tpl->setCurrentBlock('info_transcoding');
			$player_tpl->setVariable('INFO_TRANSCODING', $this->pl->txt('info_transcoding_full'));
			$player_tpl->parseCurrentBlock();
		}

		if (!$video instanceof xvmpDeletedMedium) {
			$player_tpl->setCurrentBlock('video_info');
			$player_tpl->setVariable('VALUE', $this->pl->txt('duration') . ': ' . strip_tags($video->getDurationFormatted()));
			$player_tpl->parseCurrentBlock();

			foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $custom_field) {
				$value = $video->getField($custom_field[xvmpConf::F_FORM_FIELD_ID]);
				if (!$value) {
					continue;
				}
				$player_tpl->setCurrentBlock('video_info');
				$player_tpl->setVariable('VALUE', $custom_field[xvmpConf::F_FORM_FIELD_TITLE] . ': ' . $value);
				$player_tpl->parseCurrentBlock();
			}

			$player_tpl->setCurrentBlock('video_info');
			$player_tpl->setVariable('VALUE', $this->pl->txt('created_at') . ': ' . $video->getCreatedAt('d.m.Y, H:i'));
			$player_tpl->parseCurrentBlock();

			if (xvmp::showWatched($this->parent_gui->getObjId(), $video)) {
				$player_tpl->setCurrentBlock('video_info');
				$player_tpl->setVariable('VALUE', $this->pl->txt('watched') . ': ' . xvmpUserProgress::calcPercentage($this->user->getId(), $mid) . '%');
				$player_tpl->parseCurrentBlock();
			}

			$perm_link = (new ilPermanentLinkGUI($this->parent_gui->getObject()->getType(), $this->parent_gui->getObject()->getRefId(), '_' . $video->getMid()));
			$perm_link->setIncludePermanentLinkText(false);
            $player_tpl->setVariable('PERMANENT_LINK', $perm_link->getHTML());
        }

		$tiles_tpl = new ilTemplate('tpl.content_tiles_waiting.html', true, true, $this->pl->getDirectory());
		$json_array = array();
		/** @var xvmpSelectedMedia $media */
		foreach ($selected_media->get() as $media) {
			if ($media->getMid() == $mid) {
				continue;
			}
			$json_array[] = $media->getMid();
			$tiles_tpl->setCurrentBlock('block_box_clickable');
			$tiles_tpl->setVariable('MID', $media->getMid());

			$this->ctrl->setParameter($this->parent_gui, 'mid', $media->getMid());
			$tiles_tpl->setVariable('PLAY_LINK', $this->ctrl->getLinkTarget($this->parent_gui, xvmpContentGUI::CMD_STANDARD));
			$tiles_tpl->parseCurrentBlock();

		}

		$player_tpl->setVariable('VIDEO_LIST', $tiles_tpl->get());

		$progress = xvmpUserProgress::where(array('usr_id' => $this->user->getId(), 'mid' => $mid))->first();
		if ($progress) {
			$time_ranges = $progress->getRanges();
		} else {
			$time_ranges = '[]';
		}

		if (!xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER)) {
			$this->tpl->addOnLoadCode('VimpObserver.init(' . $mid  . ', ' . $time_ranges . ');');
		}
		$this->tpl->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
		$this->tpl->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->ctrl->getLinkTarget($this->parent_gui, '', '', true) . "';");
		$this->tpl->addOnLoadCode("VimpContent.template = 'tiles';");
		$this->tpl->addOnLoadCode('VimpContent.loadTilesInOrder(0);');
//        $this->tpl->addOnLoadCode('VimpContent.loadTiles();');

		return $player_tpl->get();
	}
}