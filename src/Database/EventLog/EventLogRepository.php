<?php

namespace srag\Plugins\ViMP\Database\EventLog;

use srag\Plugins\ViMP\Database\EventLog\EventLogAR;
use Matrix\Exception;

class EventLogRepository
{
    const ACTION_UPLOAD = EventLogAR::ACTION_UPLOAD;
    const ACTION_ADD = EventLogAR::ACTION_ADD;
    const ACTION_REMOVE = EventLogAR::ACTION_REMOVE;


    public function addEntry(int $obj_id, array $data, int $action) {
        if (self::isDataValid($data)) {
            self::createLogFromData($obj_id, $data, $action);
        }
        else {
            throw new Exception('Invalid data');
        }
    }

    public static function updateEntry(int $obj_id, array $new_data, array $old_data)
    {
        // Update data
        $event_log_data = array();
        foreach (EventLogAR::getFields() as $field) {
            if ($old_data[$field] != $new_data[$field]) {
                $event_log_data[$field] = array($old_data[$field], $new_data[$field]);
            }
        }
        if (empty($event_log_data)) {
            throw new Exception('Invalid data');
        }
        self::createLogFromData($obj_id, $new_data, EventLogAR::ACTION_EDIT);

    }

    public static function changeOwnerEntry(int $obj_id, array $new_data) {
        $event_log_data = array('owner' => $new_data['owner']);
        if (!empty($event_log_data)) {
            self::createLogFromData($obj_id, $event_log_data, EventLogAR::ACTION_CHANGE_OWNER);
        }
    }

    protected static function createLogFromData(int $obj_id, array $data, int $action)
    {
        $event_log = new EventLogAR();
        $event_log->setMid($data['mid']);
        $event_log->setObjId($obj_id);
        $event_log->setAction($action);
        $event_log->setTitle($data['title']);
        $event_log->setData($data);
        $event_log->create();
    }

    protected static function isDataValid(array $data)
    {
        $event_log_data = array();
        foreach (EventLogAR::getFields() as $field) {
            $event_log_data[$field] = $data[$field];
        }
        if (empty($event_log_data)) {
            return false;
        }
        return true;

    }
}