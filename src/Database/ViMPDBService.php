<?php

namespace srag\Plugins\ViMP\Database;

use srag\Plugins\ViMP\Database\Config\ConfigRepository;
use ILIAS\DI\Container;

class ViMPDBService
{
    protected $dic;

    /** @var ConfigRepository */
    protected static $config;
    protected $event_log;
    protected $selected_media;
    protected $settings;
    protected $uploaded_media;
    protected $user_LP_status;
    protected $user_progress;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;

    }

    public static function getEmbedPlayer()
    {
        return self::$config->getEmbedPlayerConfig();
    }

    public static function lookUpAPIUser()
    {
        return self::$config->getAPIUser();

    }

    public static function lookUpAPIPassword()
    {
        return self::$config->getAPIPassword();
    }

    public static function lookUpAPIKey()
    {
        return self::$config->getAPIKey();
    }

    public static function lookUpAPIURL()
    {
        return self::$config->getAPIUrl();
    }

    public static function lookUpCacheTtlToken()
    {
        return self::$config->getCacheTokenConfig();
    }

    public static function lookUpAllowPublic()
    {
        return self::$config->allowsPublic();
    }

    public static function lookUpAllowPublicUpload()
    {
        return self::$config->allowsPublicUpload();
    }

    public static function lookUpCacheEnabled() {

    }

}