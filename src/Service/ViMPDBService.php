<?php

namespace srag\Plugins\ViMP\Service;

use ILIAS\DI\Container;
use srag\Plugins\ViMP\Database\EventLog\EventLogRepository;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaRepository;
use srag\Plugins\ViMP\Database\Settings\SettingsRepository;
use srag\Plugins\ViMP\Database\UploadedMedia\UploadedMediaRepository;
use srag\Plugins\ViMP\Database\UserLPStatus\UserLPStatusRepository;
use srag\Plugins\ViMP\Database\UserProgress\UserProgressRepository;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;
use xvmpMedium;
use SelectedMedium;
use Matrix\Exception;

/**
 * Class ViMPDBService
 * Defines the Interface to the Database-Layer
 * @author Sophie Pfister <sophie@fluxlabs.ch>
 */
class ViMPDBService
{
    protected $dic;

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

    /**
     * called to fetch several SelectedMedium objects from the database
     * @param array $params
     * @return array of SelectedMedium objects matching the params
     */
    public function getSelectedMedia(array $params) : array
    {
        $ar_array = $this->selected_media->getSelectedMedia($params);
        $result = array();
        foreach ($ar_array as $selectedMediumAr) {
            array_push($result, $this->buildSelectedMediumFromAR($selectedMediumAr));
        }
        return $result;

    }

    /**
     * called to fetch one SelectedMedium from the database
     * @param array $params
     * @return SelectedMedium
     */
    public function getSelectedMedium(array $params) : SelectedMedium
    {
        $selected_medium_ar = $this->selected_media->getSelectedMedium($params);
        return $this->buildSelectedMediumFromAR($selected_medium_ar);
    }

    public function addVideoToSelected(int $obj_id, int $mid, bool $visible)
    {
        try {
            $this->selected_media->addVideo($mid, $obj_id, $visible);
            $video = xvmpMedium::getObjectAsArray($mid);
            $this->event_log->logAdd($obj_id, $video);
        } catch (Exception $e) {
            // ToDo
        }

    }

    public function removeVideoFromSelected(int $obj_id, int $mid) {
        try {
            $this->selected_media->removeVideo($obj_id, $mid);
            $video = xvmpMedium::getObjectAsArray($mid);
            $this->event_log->logRemove($obj_id, $video);
        } catch (Exception $e) {
            //ToDo
        }


    }

    protected function buildSelectedMediumFromAR(SelectedMediaAR $ar) : SelectedMedium
    {
        $id = $ar->getId();
        $obj_id = $ar->getObjId();
        $mid = $ar->getMid();
        $visible = $ar->getVisible();
        $lp_is_required = $ar->getLpIsRequired();
        $lp_req_percentage = $ar->getLpReqPercentage();
        $sort = $ar->getSort();

        return new SelectedMedium($id, $obj_id, $mid, $visible, $lp_is_required, $lp_req_percentage, $sort);
    }

}