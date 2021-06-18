<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ilTemplate;
use xvmpException;
use ilTemplateException;
use xvmpConf;
use ilViMPPlugin;
use srag\Plugins\ViMP\UIComponents\PlayerModal\PlayerContainerDTO;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class PlayerModalRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.player_modal.html';
    /**
     * @var ilViMPPlugin
     */
    private $plugin;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * PlayModalRenderer constructor.
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
    public function render(PlayerContainerDTO $playModalDTO, bool $async) : string
    {
        $tpl = new ilTemplate(self::TEMPLATE_PATH, true, true);
        $tpl->setVariable('VIDEO_PLAYER', $playModalDTO->getVideoPlayer()->getHTML());

        if ($playModalDTO->getMediumMetadata()->isTranscoding()) {
            $msg = xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER) ? $this->plugin->txt('info_transcoding_full')
                : $this->plugin->txt('info_transcoding_possible_full');
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $msg);
            $tpl->setVariable('INFO_STYLE', 'color:red;');
            $tpl->parseCurrentBlock();
        }

        foreach ($playModalDTO->getMediumMetadata()->getMediumAttributes() as $mediumAttribute) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $mediumAttribute->getTitle() ?
                $mediumAttribute->getTitle() . ': ' . $mediumAttribute->getValue() :
                $mediumAttribute->getValue());
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('info_paragraph_ellipsis');
        $tpl->setVariable('INFO', nl2br($playModalDTO->getMediumMetadata()->getDescription(), false));
        $tpl->parseCurrentBlock();

        if ($playModalDTO->getPermLinkHtml()) {
            $tpl->setVariable('PERM_LINK', $playModalDTO->getPermLinkHtml());
        }

        foreach ($playModalDTO->getButtons() as $button) {
            $tpl->setCurrentBlock('button');
            $tpl->setVariable('BUTTON', $this->renderComponent($button, $async));
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    protected function renderComponent(Component $component, bool $async) : string
    {
        return $async ? $this->dic->ui()->renderer()->renderAsync($component)
            : $this->dic->ui()->renderer()->render($component);
    }

}
