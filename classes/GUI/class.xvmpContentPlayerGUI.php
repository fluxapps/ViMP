<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\UIComponents\Player\VideoPlayer;
use srag\Plugins\ViMP\UIComponents\Renderer\PlayerModalRenderer;
use srag\Plugins\ViMP\Content\MediumMetadataDTOBuilder;
use srag\Plugins\ViMP\UIComponents\Renderer\PlayerInSiteRenderer;
use srag\Plugins\ViMP\UIComponents\PlayerModal\PlayerContainerDTO;

/**
 * Class xvmpContentPlayerGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentPlayerGUI {
    /**
     * @var PlayerInSiteRenderer
     */
    private $player_renderer;
    /**
     * @var MediumMetadataDTOBuilder
     */
    private $metadata_builder;

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
        $this->player_renderer = new PlayerInSiteRenderer($DIC, $this->pl);
        $this->metadata_builder = new MediumMetadataDTOBuilder($DIC, $this->pl);

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

		$mid = filter_input(INPUT_GET, 'mid', FILTER_VALIDATE_INT) ?: $selected_media->first()->getMid();

		try {
			$medium = xvmpMedium::find($mid);
		} catch (Exception $e) {
			if ($e->getCode() != 404) {
				throw $e;
			}
			
		}

		$player_tpl = new ilTemplate('tpl.content_player.html', true, true, $this->pl->getDirectory());
		
		$video_player = new VideoPlayer($medium, xvmp::isUseEmbeddedPlayer($this->parent_gui->getObjId(), $medium));
		$in_site_player = $this->player_renderer->render(
		    new PlayerContainerDTO($video_player, $this->metadata_builder->buildFromVimpMedium($medium, false, true)),
            $medium instanceof xvmpDeletedMedium);
		$player_tpl->setVariable('VIDEO_PLAYER', $in_site_player);

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

		/** @var xvmpSettings $settings */
		$settings = xvmpSettings::find($this->parent_gui->getObjId());
		if ($settings->getLpActive() && !xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER)) {
			$this->tpl->addOnLoadCode('VimpObserver.init(' . $mid  . ', ' . $time_ranges . ');');
		}
		$this->tpl->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
		$this->tpl->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->ctrl->getLinkTarget($this->parent_gui, '', '', true) . "';");
		$this->tpl->addOnLoadCode("VimpContent.template = 'tiles';");
		$this->tpl->addOnLoadCode('VimpContent.loadTilesInOrder(0);');

		return $player_tpl->get();
	}
}
