<?php

namespace srag\Plugins\ViMP\Database\Config;

use srag\Plugins\ViMP\Database\Config\ConfigAR;
use ILIAS\DI\Container;

class ConfigRepository
{
    /**
     * @var self
     */
    protected static $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function embedPlayer()
    {
        return ConfigAR::getConfig(ConfigAR::F_EMBED_PLAYER);
    }

    public static function getAPIUser()
    {
        return ConfigAR::getConfig(ConfigAR::F_API_USER);
    }

    public static function getAPIPassword()
    {
        return ConfigAR::getConfig(ConfigAR::F_API_PASSWORD);
    }

    public static function getAPIKey()
    {
        return ConfigAR::getConfig(ConfigAR::F_API_KEY);
    }

    public static function getAPIUrl()
    {
        return ConfigAR::getConfig(ConfigAR::F_API_URL);
    }

    public static function getCacheTokenConfig()
    {
        return ConfigAR::getConfig(ConfigAR::F_CACHE_TTL_TOKEN);
    }

    public static function allowsPublic()
    {
        return ConfigAR::getConfig(ConfigAR::F_ALLOW_PUBLIC);
    }

    public static function allowsPublicUpload()
    {
        return ConfigAR::getConfig(ConfigAR::F_ALLOW_PUBLIC_UPLOAD);
    }


    public static function isCacheEnabled() {
    //    return ConfigAR::getConfig(F_ACTIVATE_CACHE); ToDo: Does not exist
    }

}