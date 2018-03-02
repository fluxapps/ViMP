<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpEventLogTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEventLogTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.event_log_row.html';

	protected $available_columns = array(
		'datetime' => array(),
		'action' => array(),
		'user' => array(),
		'mid' => array(),
		'object_title' => array(),
		'video_title' => array(),
		'data' => array()
	);

	protected $is_global_log;

	public function __construct($parent_gui, $parent_cmd) {
		if ($parent_gui instanceof ilViMPConfigGUI) {
			$this->is_global_log = true;
			$this->setId('global_log');
		} else {
			unset($this->available_columns['object_title']);
			$this->setId('log_' . $parent_gui->getObjId());
		}
		parent::__construct($parent_gui, $parent_cmd);
		$this->setShowRowsSelector(true);
	}


	public function parseData() {
		if ($this->is_global_log) {
			$where = array();
			$operators = array('action' => 'IN', 'title' => 'LIKE');
		} else {
			$where = array('obj_id' => $this->parent_obj->getObjId());
			$operators = array('obj_id' => '=', 'action' => 'IN', 'title' => 'LIKE');
		}

		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			if (!$value || empty($value)) {
				continue;
			}
			$where[$filter_item->getPostVar()] = $filter_item->getPostVar() == 'title' ? '%'.$value.'%' : $value;
		}

		if (empty($where)) {
			$this->setData(xvmpEventLog::orderBy('timestamp', 'DESC')->getArray());
		} else {
			$this->setData(xvmpEventLog::where($where, $operators)->orderBy('timestamp', 'DESC')->getArray());
		}
	}


	protected function fillRow($a_set) {
		foreach ($a_set as $key => $value)
		{
			switch ($key) {
				case 'timestamp':
					$this->tpl->setVariable("VAL_DATETIME", date('d.m.Y, H:i', $value));
					break;
				case 'action':
					$this->tpl->setVariable("VAL_ACTION", $this->pl->txt('log_action_' . $value));
					break;
				case 'login':
					$user = new ilObjUser(ilObjUser::getUserIdByLogin($value));
					$this->tpl->setVariable("VAL_USER", $user->getFirstname() . ' ' . $user->getLastname() . ' [' . $value . ']');
					break;
				case 'data':
					$this->tpl->setVariable("VAL_DATA", $this->formatData($a_set['action'], $value));
					break;
				case 'obj_id':
					if ($this->is_global_log) {
						$this->tpl->setCurrentBlock('block_object_title');
						$this->tpl->setVariable('VAL_OBJECT_TITLE', ilObject2::_lookupTitle($value));

						$this->ctrl->setParameterByClass(ilObjViMPGUI::class, 'ref_id',array_shift(ilObject2::_getAllReferences($value)));
						$link = $this->ctrl->getLinkTargetByClass(array(ilObjPluginDispatchGUI::class, ilObjViMPGUI::class), ilObjViMPGUI::CMD_SHOW_CONTENT);
						$this->tpl->setVariable('VAL_OBJECT_LINK', $link);

						$this->tpl->parseCurrentBlock();
					}
					break;
				default:
					$this->tpl->setVariable("VAL_".strtoupper($key), $value);
					break;
			}
		}
	}

	protected function formatData($action, $data) {
		$string = '';
		switch ($action) {
			case xvmpEventLog::ACTION_ADD:
			case xvmpEventLog::ACTION_REMOVE:
			case xvmpEventLog::ACTION_DELETE:
			case xvmpEventLog::ACTION_UPLOAD:
				foreach ($data as $key => $value) {
					$string .= $this->pl->txt($key) . ': "' . (is_array($value) ? implode(', ', $value) : $value) . '"<br>';
				}
				break;
			case xvmpEventLog::ACTION_EDIT:
				foreach ($data as $key => $value) {
					$old = array_shift($value);
					$new = array_shift($value);
					$string .= $this->pl->txt($key) . ': "' . (is_array($old) ? implode(', ', $old) : $old) . '" -> "' . (is_array($new) ? implode(', ', $new) : $new) . '"<br>';
				}
				break;
			case xvmpEventLog::ACTION_CHANGE_OWNER:
				$new_owner = $data['owner'];
				$string .= $this->pl->txt('new_owner') . ': ' . $new_owner;
		}

		return $string;
	}

	/**
	 * Init filter items
	 *
	 */
	public function initFilter()
	{
		$item = new ilMultiSelectInputGUI($this->pl->txt('action'), 'action');
		$options = array();
		foreach (array(xvmpEventLog::ACTION_UPLOAD, xvmpEventLog::ACTION_EDIT, xvmpEventLog::ACTION_DELETE, xvmpEventLog::ACTION_ADD, xvmpEventLog::ACTION_REMOVE, xvmpEventLog::ACTION_CHANGE_OWNER)
		         as $action_id) {
			$options[$action_id] = $this->pl->txt('log_action_' . $action_id);
		}
		$item->setOptions($options);
		$this->addAndReadFilterItem($item);

		$item = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$this->addAndReadFilterItem($item);
	}

}