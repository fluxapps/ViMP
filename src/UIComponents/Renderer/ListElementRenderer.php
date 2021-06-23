<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilTemplateException;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ListElementRenderer extends ContentElementRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_list.html';


    protected function getInnerTemplate() : ilTemplate
    {
        return new ilTemplate(self::TEMPLATE_PATH, true, true);
    }

    /**
     * @throws ilTemplateException
     */
    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getContainerTemplate();
        if ($mediumMetadataDTO->isAvailable()) {
            $tpl->setCurrentBlock('play_async');
            $tpl->setVariable('MID', $mediumMetadataDTO->getMid());
        } else {
            $tpl->setCurrentBlock('not_available');
        }
        $tpl->setVariable('ELEMENT', $this->buildInnerTemplate($mediumMetadataDTO)->get());
        $tpl->parseCurrentBlock();
        return $tpl;
    }
}
