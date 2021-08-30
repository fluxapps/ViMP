<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ilTemplate;
use xvmpException;
use ilTemplateException;
use ConfigAR;
use ilViMPPlugin;
use srag\Plugins\ViMP\UIComponents\PlayerModal\PlayerContainerDTO;
use srag\Plugins\ViMP\Content\MediumMetadataParser;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class PlayerModalRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.player_modal.html';
    const TEMPLATE_PATH_UNAVAILABLE = __DIR__ . '/../../../templates/default/tpl.video_not_available.html';
    /**
     * @var ilViMPPlugin
     */
    private $plugin;
    /**
     * @var MediumMetadataParser
     */
    private $metadata_parser;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @param MediumMetadataParser $metadata_parser
     * @param Container            $dic
     * @param ilViMPPlugin         $plugin
     */
    public function __construct(MediumMetadataParser $metadata_parser, Container $dic, ilViMPPlugin $plugin)
    {
        $this->dic = $dic;
        $this->plugin = $plugin;
        $this->metadata_parser = $metadata_parser;
    }

    /**
     * @throws ilTemplateException
     * @throws xvmpException
     */
    public function render(PlayerContainerDTO $playerContainerDTO, bool $async, bool $show_unavailable = false) : string
    {
        $tpl = new ilTemplate(self::TEMPLATE_PATH, true, true);
        $is_available = $playerContainerDTO->getMediumMetadata()->isAvailable() | $show_unavailable;
        $tpl->setVariable('VIDEO_PLAYER', $is_available ?
            $playerContainerDTO->getVideoPlayer()->getHTML()
            : $this->renderUnavailablePlayer($playerContainerDTO));

        $this->renderInfoMessage($playerContainerDTO, $tpl, $show_unavailable);

        if ($playerContainerDTO->getMediumMetadata()->hasAvailability()) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $this->plugin->txt('available') . ': ' .
                $this->metadata_parser->parseAvailability(
                    $playerContainerDTO->getMediumMetadata()->getAvailabilityStart(),
                    $playerContainerDTO->getMediumMetadata()->getAvailabilityEnd(),
                    true));
            $tpl->parseCurrentBlock();
        }

        foreach ($playerContainerDTO->getMediumMetadata()->getMediumAttributes() as $mediumAttribute) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $mediumAttribute->getTitle() ?
                $mediumAttribute->getTitle() . ': ' . $mediumAttribute->getValue() :
                $mediumAttribute->getValue());
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('info_paragraph');
        $tpl->setVariable('INFO', nl2br($playerContainerDTO->getMediumMetadata()->getDescription(), false));
        $tpl->parseCurrentBlock();

        foreach ($playerContainerDTO->getButtons() as $button) {
            $tpl->setCurrentBlock('button');
            $tpl->setVariable('BUTTON', $this->renderComponents($button, $async));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     * @param PlayerContainerDTO $playerContainerDTO
     * @param ilTemplate         $tpl
     */
    protected function renderInfoMessage(PlayerContainerDTO $playerContainerDTO, ilTemplate $tpl, bool $available)
    {
        if ($available) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $this->plugin->txt('info_not_available'));
            $tpl->setVariable('INFO_STYLE', 'color:red;');
            $tpl->parseCurrentBlock();
        } elseif ($playerContainerDTO->getMediumMetadata()->isTranscoding()) {
            $msg = ConfigAR::getConfig(ConfigAR::F_EMBED_PLAYER) ? $this->plugin->txt('info_transcoding_full')
                : $this->plugin->txt('info_transcoding_possible_full');
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $msg);
            $tpl->setVariable('INFO_STYLE', 'color:red;');
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @throws ilTemplateException
     */
    private function renderUnavailablePlayer(PlayerContainerDTO $playerContainerDTO) : string
    {
        $tpl = new ilTemplate(self::TEMPLATE_PATH_UNAVAILABLE, true, true);
        $tpl->setVariable('THUMBNAIL', $playerContainerDTO->getMediumMetadata()->getThumbnailUrl());
        return $tpl->get();
    }

    /**
     * @param Component|Component[] $component
     * @param bool $async
     * @return string
     */
    protected function renderComponents($component, bool $async) : string
    {
        return $async ? $this->dic->ui()->renderer()->renderAsync($component)
            : $this->dic->ui()->renderer()->render($component);
    }

}
