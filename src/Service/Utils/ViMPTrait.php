<?php

namespace srag\Plugins\ViMP\Service\Utils;

use srag\Plugins\ViMP\Service\Utils\Repository;

trait ViMPTrait
{

    protected static function viMP(): Repository {
        return Repository::getInstance();
    }

}