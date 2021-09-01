<?php

namespace srag\Plugins\ViMP\Database\EventLog;

use srag\Plugins\ViMP\Database\EventLog\EventLogAR;
use Matrix\Exception;

class EventLogRepository
{

    public function logAdd(int $obj_id, array $data)
    {
        $event_log_data = array();
        foreach (EventLogAR::getFields() as $field) {
            $event_log_data[$field] = $data[$field];
        }
        if (empty($event_log_data)) {
            throw new Exception('Empty Data Array');
        }
        self::createLogFromData($obj_id, $data, EventLogAR::ACTION_ADD);

    }

    public function logRemove(int $obj_id, array $data)
    {
        $event_log_data = array();
        foreach (EventLogAR::getFields() as $field) {
            $event_log_data[$field] = $data[$field];
        }
        if (empty($event_log_data)) {
            throw new Exception('Empty Data Array');
        }
        self::createLogFromData($obj_id, $data, EventLogAR::ACTION_REMOVE);

    }

    public static function createLogFromData(int $obj_id, array $data, int $action) {
        $event_log = new EventLogAR();
        $event_log->setMid($data['mid']);
        $event_log->setObjId($obj_id);
        $event_log->setAction($action);
        $event_log->setTitle($data['title']);
        $event_log->setData($data);
        $event_log->create();
    }
}