<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilTemplate;
use xvmpContentGUI;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TileSmallRenderer extends TileRenderer
{
    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getContainerTemplate();
        $tpl->setCurrentBlock('play_sync');
        $this->dic->ctrl()->setParameterByClass(xvmpContentGUI::class, 'mid', $mediumMetadataDTO->getMid());
        $tpl->setVariable('HREF',
            $this->dic->ctrl()->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_PLAY_VIDEO));
        $tpl->setVariable('ELEMENT', $this->buildInnerTemplate($mediumMetadataDTO)->get());
        $tpl->parseCurrentBlock();
        return $tpl;
    }

}
