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
		'title' => array(),
		'data' => array()
	);

	public function __construct($parent_gui, $parent_cmd) {
		$this->setId('log_' . $parent_gui->getObjId());
		parent::__construct($parent_gui, $parent_cmd);
		$this->setShowRowsSelector(true);
	}


	public function parseData() {
		$where = array('obj_id' => $this->parent_obj->getObjId());
		$operators = array('obj_id' => '=', 'action' => 'IN', 'title' => 'LIKE');

		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			if (!$value || empty($value)) {
				continue;
			}
			$where[$filter_item->getPostVar()] = $filter_item->getPostVar() == 'title' ? '%'.$value.'%' : $value;
		}

		$this->setData(xvmpEventLog::where($where, $operators)->orderBy('timestamp', 'DESC')->getArray());
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
		foreach (array(xvmpEventLog::ACTION_UPLOAD, xvmpEventLog::ACTION_EDIT, xvmpEventLog::ACTION_DELETE, xvmpEventLog::ACTION_ADD, xvmpEventLog::ACTION_REMOVE)
		         as $action_id) {
			$options[$action_id] = $this->pl->txt('log_action_' . $action_id);
		}
		$item->setOptions($options);
		$this->addAndReadFilterItem($item);

		$item = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$this->addAndReadFilterItem($item);

//		if ($this->isColumnSelected('created_at')) {
//			$item = new ilCombinationInputGUI($this->pl->txt('modified_between'), 'created_at');
//			$from = new ilDateTimeInputGUI("", 'created_at_from');
//			//$from->setMode(ilDateTimeInputGUI::MODE_INPUT);
//			$item->addCombinationItem("from", $from, '');
//			$to = new ilDateTimeInputGUI("", 'created_at_to');
//			//$to->setMode(ilDateTimeInputGUI::MODE_INPUT);
//			$item->addCombinationItem("to", $to, '');
//			$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
//			$this->addFilterItemWithValue($item);
//		}
//
//		if ($this->isColumnSelected('created_user_id')) {
//			$item = new ilTextInputGUI($this->pl->txt('mod_col_created_user_id'), 'created_user_id');
//			$this->addFilterItemWithValue($item);
//		}
	}

}