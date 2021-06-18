<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use xvmpMedium;
use ilTemplateException;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TileRenderer extends ContentElementRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_tiles.html';

    /**
     * @throws ilTemplateException
     */
    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getContainerTemplate();
        $tpl->setCurrentBlock('play_async');
        $tpl->setVariable('MID', $mediumMetadataDTO->getMid());
        $tpl->setVariable('ELEMENT', $this->buildInnerTemplate($mediumMetadataDTO)->get());
        $tpl->parseCurrentBlock();
        return $tpl;
    }

    protected function buildInnerTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = parent::buildInnerTemplate($mediumMetadataDTO);
        $duration_array = array_filter($mediumMetadataDTO->getMediumAttributes(), function ($mediumAttribute) {
            return $mediumAttribute->getTitle() === $this->plugin->txt(xvmpMedium::F_DURATION);
        });
        $tpl->setVariable('DURATION', end($duration_array)->getValue());
        return $tpl;
    }

    protected function getInnerTemplate() : ilTemplate
    {
        return new ilTemplate(self::TEMPLATE_PATH, true, true);
    }
}
