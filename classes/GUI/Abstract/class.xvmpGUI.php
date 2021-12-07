<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

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
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilToolbarGUI|mixed
	 */
	protected $toolbar;
	/**
	 * @var ilObjUser
	 */
	protected $user;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var mixed
	 */
	protected $lng;
    /**
     * @var Container
     */
	protected $dic;


	/**
	 * xvmpGUI constructor.
	 *
	 * @param ilObjViMPGUI $parent_gui
	 */
	public function __construct(ilObjViMPGUI $parent_gui) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];
		$this->dic = $DIC;
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->user = $ilUser;
		$this->pl = ilViMPPlugin::getInstance();
		$this->lng = $lng;
		$this->parent_gui = $parent_gui;
		$this->addJavaScript();
	}

    protected function addJavaScript()
    {
        $this->tpl->addJavaScript('./libs/bower/bower_components/webui-popover/dist/jquery.webui-popover.js');
        $this->tpl->addJavaScript('./src/UI/templates/js/Popover/popover.js');
    }

    /**
     * @param xvmpMedium $video
     * @return ILIAS\UI\Component\Component[]
     */
    public function buildPermLinkUI(xvmpMedium $video) : array
    {
        $link_tpl = ilLink::_getStaticLink(
            $this->parent_gui->ref_id,
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
	 *
	 */
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


	/**
	 * @return mixed
	 */
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
	 * called by ilObjViMPAccess
	 */
	public function accessDenied() {
		ilUtil::sendFailure($this->pl->txt('access_denied'), true);
		$this->ctrl->redirect($this->parent_gui, ilObjViMPGUI::CMD_SHOW_CONTENT);
	}

		/**
	 * @return ilModalGUI
	 */
	public static function getModalPlayer() {
		global $tpl;
		$tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/templates/default/modal.css?v=2');
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
    public function getFilledModalPlayer($video_mid)
    {
        global $tpl, $DIC;
        $selected_medium = xvmpSelectedMedia::where(array('obj_id' => $this->getObjId(), 'mid' => $video_mid));
        if (!ilObjViMPAccess::hasWriteAccess()) {
            $selected_medium = $selected_medium->where(['visible' => 1]);
        }
        $selected_medium = $selected_medium->first();
        if (!$selected_medium) {
            $modal = ilModalGUI::getInstance();
            $modal->setId('xvmp_modal_player');
            $modal->setType(ilModalGUI::TYPE_LARGE);
            if (xvmp::is54()) {
                $modal->setBody($DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($this->pl->txt('access_denied'))));
            } else {
                $modal->setBody($DIC->ui()->mainTemplate()->getMessageHTML($this->pl->txt('access_denied'), "failure"));
            }
            return $modal;
        }
        $tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/templates/default/modal.css?v=2');
        $modal_content = $this->fillModalPlayer($video_mid, false);
        /** @var xvmpSettings $settings */
        $settings = xvmpSettings::find($this->getObjId());
        if ($settings->getLpActive()) {
            $tpl->addOnLoadCode('VimpObserver.init(' . $video_mid . ', ' . json_encode($modal_content->time_ranges) . ');');
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
     *
     * @return stdClass
     * @throws ilTemplateException
     * @throws xvmpException
     */
	public function fillModalPlayer($play_video_id = null, $async = true) {
		$mid = $play_video_id ?? $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$video_infos = '';
		if ($video->getStatus() !== 'legal') {
			$msg = xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER) ? $this->pl->txt('info_transcoding_full') : $this->pl->txt('info_transcoding_possible_full');
			$video_infos .= "
				<p style='color:red'>" . $msg . "</p>
			";
		}
		$video_infos .= "				
			<p>{$this->pl->txt(xvmpMedium::F_DURATION)}: {$video->getDurationFormatted()}</p>
			<p>{$this->pl->txt(xvmpMedium::F_CREATED_AT)}: {$video->getCreatedAt('d.m.Y, H:i')}</p>
			
		";
		foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
			if ($value = $video->getField($field[xvmpConf::F_FORM_FIELD_ID])) {
                               $lng_title = $this->lng->exists($this->pl->getPrefix() . "_" . $field[xvmpConf::F_FORM_FIELD_ID])
                                   ? $this->lng->txt($this->pl->getPrefix() . "_" . $field[xvmpConf::F_FORM_FIELD_ID])
                                   : $field[xvmpConf::F_FORM_FIELD_TITLE];
                               $video_infos .= "<p>{$lng_title}: {$value}</p>";
			}
		}
		$video_infos .= "<div class='xvmp_ellipsis'>{$this->pl->txt(xvmpMedium::F_DESCRIPTION)}: " . nl2br($video->getDescription(), false) . "</div>";

		if (!is_null($this->getObject())) {
            $link = $this->buildPermLinkUI($video);
            $video_infos .= $async ?
                $this->dic->ui()->renderer()->renderAsync($link)
                : $this->dic->ui()->renderer()->render($link);
        }

		$response = new stdClass();
		if ($video->getStatus() === 'legal' || !xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER)) {
			$video_player_html = (new xvmpVideoPlayer($video, xvmp::useEmbeddedPlayer($this->getObjId(), $video)))->getHTML();
		}
		$response->html = $video_player_html . $video_infos;
		$response->video_title = $video->getTitle();
		/** @var xvmpUserProgress $progress */
		$progress = xvmpUserProgress::where(array(xvmpUserProgress::F_USR_ID => $this->user->getId(), xvmpMedium::F_MID => $mid))->first();
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
