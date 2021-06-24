<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilTemplate;
use xvmpContentGUI;
use DateTime;
use xvmpException;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TileSmallRenderer extends TileRenderer
{
    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getContainerTemplate();
        if ($mediumMetadataDTO->isAvailable()) {
            $tpl->setCurrentBlock('play_sync');
            $this->dic->ctrl()->setParameterByClass(xvmpContentGUI::class, 'mid', $mediumMetadataDTO->getMid());
            $tpl->setVariable('HREF',
                $this->dic->ctrl()->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_PLAY_VIDEO));
        } else {
            $tpl->setCurrentBlock('not_available');
        }
        $tpl->setVariable('ELEMENT', $this->buildInnerTemplate($mediumMetadataDTO)->get());
        $tpl->parseCurrentBlock();
        return $tpl;
    }

    protected function fillAvailabilityOverlay(MediumMetadataDTO $mediumMetadataDTO, ilTemplate $tpl)
    {
        $tpl->setCurrentBlock('not_available_overlay');
        $tpl->setVariable('AVAILABILITY', $this->metadata_parser->parseAvailability(
            $mediumMetadataDTO->getAvailabilityStart(),
            $mediumMetadataDTO->getAvailabilityEnd(),
            true
        ));
        $tpl->parseCurrentBlock();
    }
}
