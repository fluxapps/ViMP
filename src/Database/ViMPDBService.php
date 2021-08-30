<?php

namespace srag\Plugins\ViMP\Database;

use srag\Plugins\ViMP\Database\Config\ConfigRepository;
use ILIAS\DI\Container;
use srag\Plugins\ViMP\Database\EventLog\EventLogRepository;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaRepository;
use srag\Plugins\ViMP\Database\Settings\SettingsRepository;
use srag\Plugins\ViMP\Database\UploadedMedia\UploadedMediaRepository;
use srag\Plugins\ViMP\Database\UserLPStatus\UserLPStatusRepository;
use srag\Plugins\ViMP\Database\UserProgress\UserProgressRepository;

/**
 * Class ViMPDBService
 * Defines the Interface to the Database-Layer
 * @author Sophie Pfister <sophie@fluxlabs.ch>
 */
class ViMPDBService
{
    protected $dic;

    /** @var ConfigRepository */
    protected static $config;
    /** @var EventLogRepository */
    protected $event_log;
    /** @var SelectedMediaRepository */
    protected $selected_media;
    /** @var SettingsRepository */
    protected $settings;
    /** @var UploadedMediaRepository */
    protected $uploaded_media;
    /** @var UserLPStatusRepository */
    protected $user_LP_status;
    /** @var UserProgressRepository */
    protected $user_progress;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->event_log = new EventLogRepository($dic);
        $this->selected_media = new SelectedMediaRepository($dic);
        $this->settings = new SettingsRepository($dic);
        $this->uploaded_media = new UploadedMediaRepository($dic);
        $this->user_LP_status = new UserLPStatusRepository($dic);
        $this->user_progress = new UserProgressRepository($dic);

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

    public static function lookUpCacheEnabled()
    {
        //ToDo
    }

    public function getSelectedMediaByObjId(int $obj_id) : array
    {
        return $this->selected_media->getByObjId($obj_id);

    }

    public function getSelectedMediumByObjIdAndMID(): array {


    }

}