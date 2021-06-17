<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use xvmpContentGUI;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TileSmallRenderer extends ContentElementRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_tiles_small.html';

    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = parent::buildTemplate($mediumMetadataDTO);
        $this->dic->ctrl()->setParameterByClass(xvmpContentGUI::class, 'mid', $mediumMetadataDTO->getMid());
        $tpl->setVariable('PLAY_LINK',
            $this->dic->ctrl()->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD));
        return $tpl;
    }

    protected function getTemplate() : ilTemplate
    {
        return new ilTemplate(self::TEMPLATE_PATH, true, true);
    }
}
