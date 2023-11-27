<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use srag\Plugins\ViMP\UIComponents\PlayerModal\PlayerContainerDTO;
use srag\Plugins\ViMP\Content\MediumMetadataDTOBuilder;
use srag\Plugins\ViMP\UIComponents\Renderer\Factory;
use srag\Plugins\ViMP\UIComponents\Player\VideoPlayer;

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
    const CMD_DOWNLOAD_MEDIUM = 'downloadMedium';
    /**
	 * @var ilObjViMPGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
    /**
     * @var Container
     */
	protected $dic;
    /**
     * @var Factory
     */
	protected $renderer_factory;
    /**
     * @var MediumMetadataDTOBuilder
     */
	protected $metadata_builder;
    protected ilGlobalTemplateInterface $tpl;


    /**
	 * xvmpGUI constructor.
	 *
	 * @param ilObjViMPGUI $parent_gui
	 */
	public function __construct(ilObjViMPGUI $parent_gui) {
		global $DIC;
		$this->dic = $DIC;
		$this->pl = ilViMPPlugin::getInstance();
		$this->parent_gui = $parent_gui;
		$this->metadata_builder = new MediumMetadataDTOBuilder($DIC, $this->pl);
		$this->renderer_factory = new Factory($DIC, $this->pl);
        $this->tpl = $DIC->ui()->mainTemplate();
		$this->addJavaScript();
	}

	protected function addJavaScript()
    {
        $this->dic->ui()->mainTemplate()->addJavaScript('./libs/bower/bower_components/webui-popover/dist/jquery.webui-popover.js');
        $this->dic->ui()->mainTemplate()->addJavaScript('./src/UI/templates/js/Popover/popover.js');
    }

    /**
     * @return ilModalGUI
     */
    protected function getAccessDeniedModal() : ilModalGUI
    {
        $modal = ilModalGUI::getInstance();
        $modal->setId('xvmp_modal_player');
        $modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setBody($this->dic->ui()->renderer()->render($this->dic->ui()->factory()->messageBox()->failure($this->pl->txt('access_denied'))));
        return $modal;
    }

    /**
     * @param xvmpMedium $medium
     * @return PlayerContainerDTO
     * @throws xvmpException
     */
    public function buildPlayerContainerDTO(xvmpMedium $medium) : PlayerContainerDTO
    {
        $playerContainerDTO = new PlayerContainerDTO(
            $this->getVideoPlayer($medium, $this->getObjId()),
            $this->metadata_builder->buildFromVimpMedium($medium, false, false));

        $buttons = [];
        if (!is_null($this->getObject())) {
            $buttons[] = $this->buildPermLinkUI($medium);
			$buttons[] = $this->buildCopiableVideoLinkUI($medium);
        }

        if ($medium->isDownloadAllowed()) {
            $this->dic->ctrl()->setParameter($this, 'mid', $medium->getMid());
            $buttons[] = $this->dic->ui()->factory()->button()->standard(
                $this->pl->txt('btn_download'),
                $this->dic->ctrl()->getLinkTarget($this, self::CMD_DOWNLOAD_MEDIUM)
            );
        }

        if (!empty($buttons)) {
            $playerContainerDTO = $playerContainerDTO->withButtons($buttons);
        }

        return $playerContainerDTO;
    }

    /**
     * @throws xvmpException
     */
    protected function getVideoPlayer($video, int $obj_id) : VideoPlayer
    {
        return (new VideoPlayer($video, xvmp::isUseEmbeddedPlayer($obj_id, $video), false));
    }

    /**
     * @param xvmpMedium $video
     * @return ILIAS\UI\Component\Component[]
     */
    public function buildPermLinkUI(xvmpMedium $video) : array
    {
        $link_tpl = ilLink::_getStaticLink(
            $this->parent_gui->getRefId(),
            $this->parent_gui->getType(),
            true,
            '_' . $video->getMid() . '_0'
        );

        $popover = $this->dic->ui()->factory()->popover()->standard(
            $this->dic->ui()->factory()->legacy($this->pl->txt('popover_link_copied')));

        if (!xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER)) {
            $items[] = $this->dic->ui()->factory()->button()->shy($this->pl->txt('btn_copy_link_w_time'),
                '')->withOnClick($popover->getShowSignal())->withOnLoadCode(function ($id) use ($link_tpl) {
                return "document.getElementById('{$id}').addEventListener('click', () => VimpContent.copyDirectLinkWithTime('{$link_tpl}'));";
            });
        }
        return [
            $popover,
            $this->dic->ui()->factory()->legacy('
                <div class="ilPermalinkContainer input-group">
                    <input class="form-control" id="current_perma_link" type="text" value="' . $link_tpl . '" readonly="readonly" onclick="this.focus();this.select();return false;" />
                    <span class="input-group-btn">'),
            $this->dic->ui()->factory()->dropdown()->standard($items)->withLabel(''),
            $this->dic->ui()->factory()->legacy('</span></div>'),
        ];
    }

	/**
     * @param xvmpMedium $video
     * @return ILIAS\UI\Component\Component[]
     */
	public function buildCopiableVideoLinkUI($video)
	{
		$medium = $video->getMedium();
		if (is_array($medium)){
			$medium = $medium[0];
		}
		$link_container = '';
		if(ilObjViMPAccess::hasAccessToLink()){
			$link_container = '<div class="ilPermalinkContainer input-group" id ="link_container">'.
					'<input class="form-control" readonly="readonly" id="video_url" type="text"'.
					'value="' .$medium .'"'.
					' onclick="return false;">'.
					'<span class="input-group-btn">	<div class="btn-group"><button type="button" class="btn btn-default" id="copy_video_url">'.
					'<span class="sr-only">Copy to clipboard</span><span class="glyphicon glyphicon-copy"></span></button></div></span></div>';

			$copy_js = "<script> $('#copy_video_url',document).on('click',function()  { $('#video_url',document).select(); VimpContent.copyToClipboard('{$medium}');} );</script>";
			return [
				$this->dic->ui()->factory()->legacy(
					'<div class ="link-info"><p>'. $this->pl->txt("perm_readlink"). '</p></div>'
				),
				$this->dic->ui()->factory()->legacy($link_container),
				$this->dic->ui()->factory()->legacy($copy_js)
			];
		}
		return [];
	}

    /**
	 *
	 */
	public function executeCommand() {
		if (!$this->dic->ctrl()->isAsynch()) {
			$this->dic->tabs()->activateTab(static::TAB_ACTIVE);
		}

		$nextClass = $this->dic->ctrl()->getNextClass();
		switch ($nextClass) {
			default:
				$cmd = $this->dic->ctrl()->getCmd(self::CMD_STANDARD);
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
				$medium = xvmpMedium::find($mid);
				ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_PLAY_VIDEO, $this, $medium);
				break;
		}

		$this->{$cmd}();
	}


	/**
	 *
	 */
	public function addFlushCacheButton () {
		$button = ilLinkButton::getInstance();
		$button->setUrl($this->dic->ctrl()->getLinkTarget($this,self::CMD_FLUSH_CACHE));
		$button->setCaption($this->pl->txt('flush_video_cache'), false);
		$button->setId('xvmp_flush_video_cache');
		$this->dic->toolbar()->addButtonInstance($button);

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
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @return mixed
	 */
	abstract protected function index();


	/**
	 *
	 */
	protected function cancel() {
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 * @return ilObjViMP
	 */
	public function getObject() {
		return $this->parent_gui->getObject();
	}

	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->parent_gui->getObject()->getId();
	}


	/**
	 * called by ilObjViMPAccess
	 */
	public function accessDenied() {
        $this->tpl->setOnScreenMessage("failure", $this->pl->txt('access_denied'), true);
        $this->dic->ctrl()->redirect($this->parent_gui, ilObjViMPGUI::CMD_SHOW_CONTENT);
	}

		/**
	 * @return ilModalGUI
	 */
	public static function getModalPlayer() {
		global $tpl;
		$tpl->addCss(ilViMPPlugin::getInstance()->getAssetURL('default/modal.css'));
		$modal = ilModalGUI::getInstance();
		$modal->setId('xvmp_modal_player');
		$modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setBody('<section><div id="xvmp_video_container"></div></section>');
		return $modal;
	}


    /**
     * @param $video_mid
     *
     * @return ilModalGUI
     * @throws ilTemplateException
     * @throws xvmpException
     */
    public function getFilledModalPlayer($video_mid) : ilModalGUI
    {
        $selected_medium = xvmpSelectedMedia::where(array('obj_id' => $this->getObjId(), 'mid' => $video_mid));
        if (!ilObjViMPAccess::hasWriteAccess()) {
            $selected_medium = $selected_medium->where(['visible' => 1]);
        }
        /** @var xvmpSelectedMedia $selected_medium */
        $selected_medium = $selected_medium->first();
        if (!$selected_medium) {
            return $this->getAccessDeniedModal();
        }
        $this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/modal.css'));
        $modal_content = $this->fillModalPlayer($video_mid, false);
        /** @var xvmpSettings $settings */
        $settings = xvmpSettings::find($this->getObjId());
        if ($settings->getLpActive()) {
            $this->dic->ui()->mainTemplate()->addOnLoadCode('VimpObserver.init(' . $video_mid . ', ' . json_encode($modal_content->time_ranges) . ');');
        }
        $modal = ilModalGUI::getInstance();
        $modal->setId('xvmp_modal_player');
        $modal->setHeading($modal_content->video_title);
        $modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setBody('<section><div id="xvmp_video_container">' .
            $modal_content->html .
            '</div></section>');
        return $modal;
    }


    /**
     * @param null $play_video_id
     * @param bool $async
     * @return stdClass
     * @throws ilTemplateException
     * @throws xvmpException
     */
	public function fillModalPlayer($play_video_id = null, bool $async = true) {
		$mid = $play_video_id ?? $_GET['mid'];
		$video = xvmpMedium::find($mid);
        $playModalDto = $this->buildPlayerContainerDTO($video);

        $response = new stdClass();
        // TODO: Abstract classes MUST NOT know their children. this is a cognitive overload
        // Refactoring Issue: https://git.fluxlabs.ch/fluxlabs/ilias/plugins/RepositoryObjects/ViMP/-/issues/3
        $show_unavailable = ($this instanceof xvmpVideosGUI) || ($this instanceof xvmpContentGUI);
        $response->html = $this->renderer_factory->playerModal()->render($playModalDto, $async, $show_unavailable);

        $response->video_title = $video->getTitle();
		/** @var xvmpUserProgress $progress */
		$progress = xvmpUserProgress::where(array(xvmpUserProgress::F_USR_ID => $this->dic->user()->getId(), xvmpMedium::F_MID => $mid))->first();
		if ($progress) {
			$response->time_ranges = json_decode($progress->getRanges());
		} else {
			$response->time_ranges = array();
		}
		if ($async == true) {
            echo json_encode($response);
            exit;
        } else {
		    return $response;
        }
	}

	protected function downloadMedium()
    {
        $mid = filter_input(INPUT_GET, 'mid', FILTER_VALIDATE_INT);
        $video = xvmpMedium::find($mid);
        ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_DOWNLOAD_VIDEO, $this, $video);
        xvmp::deliverMedium($video);
    }


	/**
	 * ajax
	 */
	public function updateProgress() {
		$mid = $_POST[xvmpMedium::F_MID];
		$ranges = $_POST[xvmpUserProgress::F_RANGES];
		xvmpUserProgress::storeProgress($this->dic->user()->getid(), $mid, $ranges);
		echo "ok";
		exit;
	}

}
