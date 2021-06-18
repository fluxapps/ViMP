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
    /**
     * @var ilViMPPlugin
     */
    private $plugin;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @param Container    $dic
     * @param ilViMPPlugin $plugin
     */
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
        $tpl->setVariable('VIDEO_PLAYER', $playerContainerDTO->getVideoPlayer()->getHTML());

        $tpl->setVariable('VIDEO', $playerContainerDTO->getVideoPlayer()->getHTML());
        $tpl->setVariable('TITLE', $playerContainerDTO->getMediumMetadata()->getTitle());
        $tpl->setVariable('DESCRIPTION', nl2br($playerContainerDTO->getMediumMetadata()->getDescription(50), false));

        if ($playerContainerDTO->getMediumMetadata()->isTranscoding()) {
            $tpl->setCurrentBlock('info_transcoding');
            $tpl->setVariable('INFO_TRANSCODING', $this->plugin->txt('info_transcoding_full'));
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

            if ($playerContainerDTO->getPermLinkHtml()) {
                $tpl->setVariable('PERMANENT_LINK', $playerContainerDTO->getPermLinkHtml());
            }
            foreach ($playerContainerDTO->getButtons() as $button) {
                $tpl->setCurrentBlock('button');
                $tpl->setVariable('BUTTON', $this->renderComponent($button, false));
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    protected function renderComponent(Component $component, bool $async) : string
    {
        return $async ? $this->dic->ui()->renderer()->renderAsync($component)
            : $this->dic->ui()->renderer()->render($component);
    }
}
