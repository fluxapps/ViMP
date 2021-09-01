<?php

namespace srag\Plugins\ViMP\Database\Settings;


class SettingsRepository
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


}