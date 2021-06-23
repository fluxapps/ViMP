<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\UIComponents\Player\VideoPlayer;

/**
 * Class xvmpContentGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpContentGUI: ilObjViMPGUI
 */
class xvmpContentGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_CONTENT;

	const CMD_RENDER_LIST_ITEM = 'renderListItem';
	const CMD_RENDER_TILE = 'renderTile';
	const CMD_RENDER_TILE_SMALL = 'renderTileSmall';
	const CMD_DELIVER_VIDEO = 'deliverVideo';
	const CMD_PLAY_VIDEO = 'playVideo';
    const GET_TEMPLATE = 'tpl';


    /**
	 *
	 */
	protected function index($play_video_id = null) {
        /** @var xvmpSettings $settings */
        $settings = xvmpSettings::find($this->getObjId());
		VideoPlayer::loadVideoJSAndCSS($settings->getLpActive() && !xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER));

		if (!$this->dic->ctrl()->isAsynch() && ilObjViMPAccess::hasWriteAccess()) {
			$this->addFlushCacheButton();
		}

		$layout_type = xvmpSettings::find($this->getObjId())->getLayoutType();

		switch ($layout_type) {
			case xvmpSettings::LAYOUT_TYPE_LIST:
				$xvmpContentListGUI = new xvmpContentListGUI($this);
				if (!is_null($play_video_id)) {
                    $this->dic->ui()->mainTemplate()->setContent($xvmpContentListGUI->getHTML() . $this->getFilledModalPlayer($play_video_id)->getHTML());
                } else {
                    $this->dic->ui()->mainTemplate()->setContent($xvmpContentListGUI->getHTML() . self::getModalPlayer()->getHTML());
                }
				break;
			case xvmpSettings::LAYOUT_TYPE_TILES:
				$xvmpContentTilesGUI = new xvmpContentTilesGUI($this);
                if (!is_null($play_video_id)) {
                    $this->dic->ui()->mainTemplate()->setContent($xvmpContentTilesGUI->getHTML() . $this->getFilledModalPlayer($play_video_id)->getHTML());
                } else {
                    $this->dic->ui()->mainTemplate()->setContent($xvmpContentTilesGUI->getHTML() . self::getModalPlayer()->getHTML());
                }
                break;
			case xvmpSettings::LAYOUT_TYPE_PLAYER:
				$xvmpContentPlayerGUI = new xvmpContentPlayerGUI($this);
                $this->dic->ui()->mainTemplate()->setContent($xvmpContentPlayerGUI->getHTML());
                break;
		}
	}


	protected function performCommand($cmd) {
		switch ($cmd) {
			case self::CMD_RENDER_LIST_ITEM:
			case self::CMD_RENDER_TILE:
			case self::CMD_RENDER_TILE_SMALL:
				$mid = $_GET['mid'];
				if (!$mid || !xvmpSelectedMedia::isSelected($mid, $this->getObjId())) {
					$this->accessDenied();
				}
				break;
            case self::CMD_DELIVER_VIDEO:
                $this->accessDenied();
                break;
		}
		parent::performCommand($cmd);
	}


    /**
     * used for goto link
     */
	public function playVideo() {
	    $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
	    if ($mid) {
	        $this->dic->ui()->mainTemplate()->addOnLoadCode('$(\'#xvmp_modal_player\').modal(\'show\');');
        }
        $this->index($mid);
    }

    /**
     * async
     * @throws xvmpException
     */
    public function renderListItem() {
        $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
        $medium = xvmpMedium::find($mid);
        if ($medium instanceof xvmpDeletedMedium) {
            echo 'deleted';
            exit;
        }
        echo $this->renderer_factory->listElement()->render(
            $this->metadata_builder->buildFromVimpMedium($medium, true, true)
        );
        exit;
    }

    /**
     * async
     * @throws xvmpException
     */
    public function renderTile() {
        $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
        $medium = xvmpMedium::find($mid);
        if ($medium instanceof xvmpDeletedMedium) {
            echo 'deleted';
            exit;
        }
        echo $this->renderer_factory->tile()->render(
            $this->metadata_builder->buildFromVimpMedium($medium, true, true)
        );
        exit;
    }

    /**
     * async
     * @throws xvmpException
     */
    public function renderTileSmall() {
        $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
        $medium = xvmpMedium::find($mid);
        if ($medium instanceof xvmpDeletedMedium) {
            echo 'deleted';
            exit;
        }
        echo $this->renderer_factory->tileSmall()->render(
            $this->metadata_builder->buildFromVimpMedium($medium, true, true)
        );
        exit;
    }
}
