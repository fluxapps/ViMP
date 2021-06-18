<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilViMPPlugin;
use ILIAS\DI\Container;
use xvmpMedium;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ContentElementRenderer
{
    const CONTAINER_TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_element.html';

    /**
     * @var ilViMPPlugin
     */
    protected $plugin;

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

    protected function buildInnerTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getInnerTemplate();
        $tpl->touchBlock($mediumMetadataDTO->isAvailable() ? 'icon_play' : 'icon_not_available');

        $tpl->setVariable('MID', $mediumMetadataDTO->getMid());
        $tpl->setVariable('THUMBNAIL', $mediumMetadataDTO->getThumbnailUrl());
        $tpl->parseCurrentBlock();

        $tpl->setVariable('TITLE', $mediumMetadataDTO->getTitle());
        $tpl->setVariable('DESCRIPTION', nl2br(strip_tags($mediumMetadataDTO->getDescription(50)), false));
        $tpl->setVariable('LABEL_TITLE', $this->plugin->txt(xvmpMedium::F_TITLE) . ':');
        $tpl->setVariable('LABEL_DESCRIPTION', $this->plugin->txt(xvmpMedium::F_DESCRIPTION) . ':');

        if ($mediumMetadataDTO->isTranscoding()) {
            $tpl->setCurrentBlock('info_transcoding');
            $tpl->setVariable('INFO_TRANSCODING', $this->plugin->txt('info_transcoding_short'));
            $tpl->parseCurrentBlock();
        }

        foreach ($mediumMetadataDTO->getMediumAttributes() as $mediumAttribute) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $mediumAttribute->getTitle() ?
                $mediumAttribute->getTitle() . ': ' . $mediumAttribute->getValue() :
                $mediumAttribute->getValue());
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    public function render(MediumMetadataDTO $mediumMetadataDTO) : string
    {
        $tpl = $this->buildTemplate($mediumMetadataDTO);
        return $tpl->get();
    }

    protected function getContainerTemplate() : ilTemplate
    {
        return new ilTemplate(self::CONTAINER_TEMPLATE_PATH, true, true);
    }

    abstract protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate;

    abstract protected function getInnerTemplate() : ilTemplate;

}
