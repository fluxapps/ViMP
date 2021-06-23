<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilViMPPlugin;
use ILIAS\DI\Container;
use ilTemplateException;
use xvmpException;
use srag\Plugins\ViMP\UIComponents\PlayerModal\PlayerContainerDTO;
use ILIAS\UI\Component\Component;
use ilTemplate;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class PlayerInSiteRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.player_in_site.html';
    const TEMPLATE_PATH_UNAVAILABLE = __DIR__ . '/../../../templates/default/tpl.video_not_available.html';

    /**
     * @var ilViMPPlugin
     */
    private $plugin;

    /**
     * @var Container
     */
    protected $dic;

    public function __construct(Container $dic, ilViMPPlugin $plugin)
    {
        $this->dic = $dic;
        $this->plugin = $plugin;
    }

    /**
     * @throws ilTemplateException
     * @throws xvmpException
     */
    public function render(PlayerContainerDTO $playerContainerDTO, bool $deleted) : string
    {
        $tpl = new ilTemplate(self::TEMPLATE_PATH, true, true);
        $tpl->setVariable('VIDEO_PLAYER', $playerContainerDTO->getMediumMetadata()->isAvailable() ?
            $playerContainerDTO->getVideoPlayer()->getHTML()
            : $this->renderUnavailablePlayer($playerContainerDTO));
        $tpl->setVariable('TITLE', $playerContainerDTO->getMediumMetadata()->getTitle());
        $tpl->setVariable('DESCRIPTION', nl2br($playerContainerDTO->getMediumMetadata()->getDescription(0), false));

        if (!$playerContainerDTO->getMediumMetadata()->isAvailable()) {
            $tpl->setCurrentBlock('info_message');
            $tpl->setVariable('INFO_MESSAGE', $this->plugin->txt('info_not_available'));
            $tpl->parseCurrentBlock();
        } elseif ($playerContainerDTO->getMediumMetadata()->isTranscoding()) {
            $tpl->setCurrentBlock('info_message');
            $tpl->setVariable('INFO_MESSAGE', $this->plugin->txt('info_transcoding_full'));
            $tpl->parseCurrentBlock();
        }

        if (!$deleted) {
            foreach ($playerContainerDTO->getMediumMetadata()->getMediumAttributes() as $mediumAttribute) {
                $tpl->setCurrentBlock('medium_info');
                $tpl->setVariable('VALUE', $mediumAttribute->getTitle() ?
                    $mediumAttribute->getTitle() . ': ' . $mediumAttribute->getValue() :
                    $mediumAttribute->getValue());
                $tpl->parseCurrentBlock();
            }

            if ($playerContainerDTO->getMediumMetadata()->isAvailable()) {
                foreach ($playerContainerDTO->getButtons() as $button) {
                    $tpl->setCurrentBlock('button');
                    $tpl->setVariable('BUTTON', $this->renderComponent($button, false));
                    $tpl->parseCurrentBlock();
                }
            }
        }

        return $tpl->get();
    }

    /**
     * @param Component|Component[] $component
     * @param bool $async
     * @return string
     */
    protected function renderComponent($component, bool $async) : string
    {
        return $async ? $this->dic->ui()->renderer()->renderAsync($component)
            : $this->dic->ui()->renderer()->render($component);
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
}
