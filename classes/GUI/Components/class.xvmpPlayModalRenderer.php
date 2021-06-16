<?php

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpPlayModalRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.play_modal.html';

    /**
     * @var Container
     */
    protected $dic;

    /**
     * xvmpPlayModalRenderer constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    public function render(xvmpPlayModalDTO $playModalDTO, bool $async) : string
    {
        $tpl = new ilTemplate(self::TEMPLATE_PATH, true, true);
        $tpl->setVariable('VIDEO_PLAYER', $playModalDTO->getVideoPlayer()->getHTML());
        foreach ($playModalDTO->getInfos() as $videoInfo) {
            $tpl->setCurrentBlock('info_paragraph' . ($videoInfo->isEllipsis() ? '_ellipsis' : ''));
            if ($videoInfo->getTitle()) {
                $tpl->setVariable('INFO', $videoInfo->getTitle() . ': ' . $videoInfo->getValue());
            } else {
                $tpl->setVariable('INFO', $videoInfo->getValue());
            }
            if ($videoInfo->getStyle()) {
                $tpl->setVariable('INFO_STYLE', $videoInfo->getStyle());
            }
            $tpl->parseCurrentBlock();
        }
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
