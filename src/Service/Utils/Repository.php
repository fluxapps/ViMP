<?php

namespace srag\Plugins\ViMP\Service\Utils;

use srag\DIC\ViMP\DICTrait;
use srag\Plugins\ViMP\Service\Utils\ViMPTrait;
use ilViMPPlugin;
use srag\Plugins\ViMP\Database\Config\ConfigRepository;

final class Repository
{
    use DICTrait;
    use ViMPTrait;
    const PLUGIN_CLASS_NAME = ilViMPPlugin::class;

    /** @var self */
    protected static $instance = null;

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Repository constructor
     */
    private function __construct()
    {

    }

    /**
     * @return ConfigRepository
     */
    public function config() : ConfigRepository
    {
        return ConfigRepository::getInstance();
    }



}