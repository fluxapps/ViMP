<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ListElementRenderer extends ContentElementRenderer
{
    const TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_list.html';


    protected function getTemplate() : ilTemplate
    {
        return new ilTemplate(self::TEMPLATE_PATH, true, true);
    }
}
