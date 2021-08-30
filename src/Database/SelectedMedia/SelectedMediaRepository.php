<?php

namespace srag\Plugins\ViMP\Database\SelectedMedia;

use ILIAS\DI\Container;

class SelectedMediaRepository
{
    /**
     * @var Container
     */
    protected $dic;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    public function getByObjId(int $obj_id): array {
        return SelectedMediaAR::getSelected($obj_id);
    }

}