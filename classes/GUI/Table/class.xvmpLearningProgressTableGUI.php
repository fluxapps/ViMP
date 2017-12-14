<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpLearningProgressTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpLearningProgressTableGUI extends xvmpTableGUI {
	const ROW_TEMPLATE = 'tpl.learning_progress_row.html';


	protected $js_files = array('xvmp_lp_table.js');
	protected $css_files = array('xvmp_video_table.css');

	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'title' => array(
			'sort_field' => ''
		),
		'description' => array(
			'sort_field' => ''
		),
		'duration' => array(
			'sort_field' => ''
		),
		'username' => array(
			'sort_field' => ''
		),
		'created_at' => array(
			'sort_field' => ''
		),
		'required' => array(
			'sort_field' => '',
			'width' => 10
		),
		'required_percentage' => array(
			'sort_field' => '',
			'width' => 10
		),
	);

	/**
	 * @var xvmpSelectedVideosGUI
	 */
	protected $parent_obj;

	/**
	 * xvmpSelectedVideosTableGUI constructor.
	 *
	 * @param int    $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		parent::__construct($parent_gui, $parent_cmd);

		$this->setTitle($this->pl->txt('selected_videos'));
		$this->setExternalSorting(true);
		$this->setEnableNumInfo(false);
		$this->setLimit(0);
		$this->setShowRowsSelector(false);

		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpLP.ajax_base_url = "'.$base_link.'";');
		$this->tpl_global->addOnLoadCode('VimpContent.ajax_base_url = "'.$base_link.'";');
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');

		$this->parseData();
		$this->addCommandButton(xvmpLearningProgressGUI::CMD_SAVE, $this->pl->txt('save_settings'));
	}

	protected function initColumns() {

		$this->addColumn('', '', 210, true);
		parent::initColumns();

	}


	public function parseData() {
		$this->setData(xvmpMedium::getSelectedAsArray($this->parent_obj->getObjId()));
	}

	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$transcoded = ($a_set['status'] == 'legal');
		if ($transcoded) {
			$this->tpl->setCurrentBlock('transcoded');
		} else {
			$this->tpl->setCurrentBlock('transcoding');
		}

		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		/** @var xvmpSelectedMedia $selected_medium */
		$selected_medium = xvmpSelectedMedia::where(array('obj_id' => $this->parent_obj->getObjId(), 'mid' => $a_set['mid']))->first();


		foreach ($this->available_columns as $title => $props)
		{

			if ($title == 'required') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $selected_medium->getLpIsRequired() == 1 ? 'checked' : '');
				//			} elseif ($title == 'thumbnail' && $transcoding) {
				//				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			} else if ($title == 'required_percentage') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $selected_medium->getLpReqPercentage());
				//			} elseif ($title == 'thumbnail' && $transcoding) {
				//				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			} else if ($title == 'duration') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set['duration_formatted']);
			} elseif ($title == 'description' && strlen($a_set[$title]) > 95) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 90) . '...');
			} elseif ($title == 'title' && strlen($a_set[$title]) > 50) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 45) . '...');
			} else {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			}
		}

		$this->tpl->parseCurrentBlock();
	}
}