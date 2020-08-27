<?php

namespace srag\Plugins\ViMP\Cron;

use ilViMPPlugin;
use ilCronJob;
use ilCronJobResult;
use srag\DIC\ViMP\DICTrait;
use xvmpCron;

/**
 * Class ViMPJob
 *
 * @package srag\Plugins\ViMP\Cron
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ViMPJob extends ilCronJob
{

    use DICTrait;

    const CRON_JOB_ID = ilViMPPlugin::XVMP;
    const PLUGIN_CLASS_NAME = ilViMPPlugin::class;


    /**
     * ViMPJob constructor
     */
    public function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return self::CRON_JOB_ID;
    }


    /**
     * @inheritDoc
     */
    public function hasAutoActivation() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function hasFlexibleSchedule() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }


    /**
     * @inheritDoc
     */
    public function getDefaultScheduleValue()/* : ?int*/
    {
        return 1;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return ilViMPPlugin::PLUGIN_NAME . ": " . self::plugin()->translate("cron_title");
    }


    /**
     * @inheritDoc
     */
    public function getDescription() : string
    {
        return self::plugin()->translate("cron_description");
    }


    /**
     * @inheritDoc
     */
    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();

        $srViMPCronjob = new xvmpCron();
        $srViMPCronjob->run();

        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
