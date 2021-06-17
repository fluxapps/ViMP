<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use xvmpMedium;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TileRenderer extends ContentElementRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_tiles.html';

    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = parent::buildTemplate($mediumMetadataDTO);
        $duration_array = array_filter($mediumMetadataDTO->getMediumInfos(), function ($mediumAttribute) {
            return $mediumAttribute->getTitle() === $this->plugin->txt(xvmpMedium::F_DURATION);
        });
        $tpl->setVariable('DURATION', end($duration_array)->getValue());
        return $tpl;
    }

    protected function getTemplate() : ilTemplate
    {
        return new ilTemplate(self::TEMPLATE_PATH, true, true);
    }
}
