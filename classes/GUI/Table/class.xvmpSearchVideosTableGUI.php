<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class xvmpSearchVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSearchVideosTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.search_videos_row.html';

	protected $js_files = array('xvmp_search_videos.js', 'xvmp_content.js');
	protected $css_files = array('xvmp_video_table.css');


	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'title' => array(
			'sort_field' => 'title',
			'width' => 180,
		),
		'description' => array(
			'sort_field' => 'description'
		),
		'username' => array(
			'sort_field' => 'user'
		),
		'copyright' => array(
			'sort_field' => 'copyright'
		),
		'created_at' => array(
			'sort_field' => 'unix_time'
		)
	);

	protected $available_filters = array(
		'title' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbyname'
		),
		'user' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbyuser'
		),
		'copyright' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbycopyright'
		),
		'category' => array(
			'input_gui' => 'ilMultiSelectInputGUI',
			'post_var' => 'filterbycategory'
		),
		'tags' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbytags'
		),
		'created_at' => array(
			'input_gui' => 'ilDateDurationInputGUI',
			'post_var' => 'filterbycreatedate'
		),
		'duration' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbyduration'
		),
		'views' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbyviews'
		),
	);

	/**
	 * @var xvmpSearchVideosGUI
	 */
	protected $parent_obj;


	/**
	 * xvmpSearchVideosTableGUI constructor.
	 *
	 * @param int    $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		$this->setPrefix(ilViMPPlugin::XVMP . '_search_');
		$this->setId('search_' . $_GET['ref_id']);
		parent::__construct($parent_gui, $parent_cmd);
		$this->setDisableFilterHiding(true);
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');
	}


	/**
	 *
	 */
	protected function initColumns() {
		$this->addColumn('', '', 75, true);
		$this->addColumn('', '', 210, true);

		parent::initColumns();
	}


	/**
	 *
	 */
	public function parseData() {
		$filter = array('thumbsize' => self::THUMBSIZE);
		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			$filter[$filter_item->getPostVar()] = is_array($value) ? implode(',', $value) : $value;
		}
		$data = xvmpMedium::getFilteredAsArray(array_filter($filter));
		$this->setData(array_filter($data));
	}


	public function initFilter() {
		$filter_item = new ilTextInputGUI($this->pl->txt('title'), 'filterbyname');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilTextInputGUI($this->pl->txt('username'), 'filterbyuser');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilTextInputGUI($this->pl->txt('copyright'), 'filterbycopyright');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilMultiSelectInputGUI($this->pl->txt('category'), 'filterbycategory');
		$categories = xvmpCategory::getAll();
		$options = array();
		/** @var xvmpCategory $category */
		foreach ($categories as $category) {
			$options[$category->getId()] = $category->getName();
		}
		$filter_item->setOptions($options);
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilTextInputGUI($this->pl->txt('tags'), 'filterbytags');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilDateDurationInputGUI($this->pl->txt('create_date'), 'filterbycreate');
		$filter_item->setShowTime(false);
		$filter_item->setStart(new ilDateTime(time(), IL_CAL_UNIX));
		$filter_item->setStartText($this->pl->txt('from'));
		$filter_item->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
		$filter_item->setEndText($this->pl->txt('to'));

		$this->addAndReadFilterItem($filter_item);


		$filter_item = new ilTextInputGUI($this->pl->txt('duration'), 'filterbyduration');
		$this->addAndReadFilterItem($filter_item);


		$filter_item = new ilTextInputGUI($this->pl->txt('views'), 'filterbyviews');
		$this->addAndReadFilterItem($filter_item);
	}


	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$hide_button = xvmpSelectedMedia::isSelected($a_set['mid'], $this->parent_obj->getObjId()) ? 'ADD' : 'REMOVE';
		$this->tpl->setVariable('VAL_ACTION_' . $hide_button, 'hidden');

		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);
		$this->tpl->setVariable('VAL_LINK_ADD', $this->ctrl->getLinkTarget($this->parent_obj, xvmpSearchVideosGUI::CMD_ADD_VIDEO, '', true));
		$this->tpl->setVariable('VAL_LINK_REMOVE', $this->ctrl->getLinkTarget($this->parent_obj, xvmpSearchVideosGUI::CMD_REMOVE_VIDEO, '', true));

		foreach ($this->available_columns as $title => $props)
		{
			if ($title == 'thumbnail') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title] . 'size=' . self::THUMBSIZE);
				continue;
			}

			$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
		}
	}
}