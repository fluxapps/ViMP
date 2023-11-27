<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\UIComponents\Renderer\PlayerInSiteRenderer;
use ILIAS\DI\Container;
use srag\Plugins\ViMP\Content\MediumMetadataParser;

/**
 * Class xvmpContentPlayerGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentPlayerGUI
{
    /**
     * @var PlayerInSiteRenderer
     */
    private $player_renderer;
    /**
     * @var ilViMPPlugin
     */
    protected $pl;
    /**
     * @var xvmpContentGUI
     */
    protected $parent_gui;
    /**
     * @var Container
     */
    protected $dic;

    /**
     * xvmpContentTilesGUI constructor.
     */
    public function __construct($parent_gui)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilViMPPlugin::getInstance();
        $this->parent_gui = $parent_gui;
        $this->player_renderer = new PlayerInSiteRenderer(new MediumMetadataParser($DIC, $this->pl), $DIC, $this->pl);

        $this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/content_player.css'));
        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getAssetURL('js/xvmp_content.js'));
        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getAssetURL('js/waiter.js'));
        $this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/waiter.css'));
    }

    /**
     * @return string|void
     * @throws arException
     * @throws ilTemplateException
     * @throws xvmpException
     */
    public function getHTML()
    {
        $selected_media = xvmpSelectedMedia::where(array('obj_id' => $this->parent_gui->getObjId(),
                                                         'visible' => 1
        ))->orderBy('sort');
        if (!$selected_media->hasSets()) {
            $this->tpl->setOnScreenMessage("info", $this->pl->txt('msg_no_videos'), true);
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

        $in_site_player = $this->player_renderer->render(
            $this->parent_gui->buildPlayerContainerDTO($medium),
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

            $tiles_tpl->parseCurrentBlock();

        }

        $player_tpl->setVariable('VIDEO_LIST', $tiles_tpl->get());

        $progress = xvmpUserProgress::where(array('usr_id' => $this->dic->user()->getId(), 'mid' => $mid))->first();
        if ($progress) {
            $time_ranges = $progress->getRanges();
        } else {
            $time_ranges = '[]';
        }

        /** @var xvmpSettings $settings */
        $settings = xvmpSettings::find($this->parent_gui->getObjId());
        if ($settings->getLpActive() && !xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER)) {
            $this->dic->ui()->mainTemplate()->addOnLoadCode('VimpObserver.init(' . $mid . ', ' . $time_ranges . ');');
        }
        $this->dic->ui()->mainTemplate()->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
        $this->dic->ui()->mainTemplate()->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->dic->ctrl()->getLinkTarget($this->parent_gui, '',
                '', true) . "';");
        $this->dic->ui()->mainTemplate()->addOnLoadCode("VimpContent.template = 'TileSmall';");
        $this->dic->ui()->mainTemplate()->addOnLoadCode('VimpContent.loadTilesInOrder(0);');

        return $player_tpl->get();
    }

}
