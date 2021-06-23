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

    /**
     * @param DateTime|null $availability_start
     * @param DateTime|null $availability_end
     * @throws xvmpException
     */
    protected function parseAvailability(/*?DateTime*/ $availability_start, /*?DateTime*/ $availability_end) : string
    {
        if (!is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_between_short'),
                $availability_start->format(self::DATE_FORMAT),
                $availability_end->format(self::DATE_FORMAT));
        }
        if (!is_null($availability_start) && is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_from_short'),
                $availability_start->format(self::DATE_FORMAT));
        }
        if (is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_to_short'),
                $availability_end->format(self::DATE_FORMAT));
        }
        throw new xvmpException(xvmpException::INTERNAL_ERROR, 'error parsing availability');
    }
}
