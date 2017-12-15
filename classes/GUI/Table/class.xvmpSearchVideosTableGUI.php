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
	 * @var xvmpSearchVideosGUI|ilVimpPageComponentPluginGUI
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
		$this->setId('xvmp_search_' . $_GET['ref_id']);

		parent::__construct($parent_gui, $parent_cmd);

		$this->setFilterCols(4);

		$this->lng->loadLanguageModule('form'); // some lang vars from the form module are used
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
		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			if (!$value) {
				continue;
			}
			$postvar = $filter_item->getPostVar();
			switch ($postvar) {
				case 'username':
					if (ilObjUser::_loginExists($value)) {
						$ilObjUser = new ilObjUser(ilObjUser::_lookupId($value));
						$xvmpUser = xvmpUser::getVimpUser($ilObjUser, true);
						if (!$xvmpUser) {
							// if the ilias user has no vimp user, then he/she certainly doesn't have any videos
							$this->setData(array());
							return;
						}
						$filter['userid'] = $xvmpUser->getId();
					} else {
						ilUtil::sendInfo($this->pl->txt('msg_username_not_found'), true);
						$this->setData(array());
						return;
					}
					break;
				case 'created':
					$filter[$postvar.'_min'] = $value['start'];
					$filter[$postvar.'_max'] = $value['end'];
					break;
				case 'duration':
					$filter[$postvar.'_min'] = $filter_item->getCombinationItem('min')->getValueInSeconds();
					$filter[$postvar.'_max'] = $filter_item->getCombinationItem('max')->getValueInSeconds();
					break;
				case 'views':
					$filter[$postvar.'_min'] = $filter_item->getCombinationItem('min')->getValue();
					$filter[$postvar.'_max'] = $filter_item->getCombinationItem('max')->getValue();
					break;
				default:
					$filter[$postvar] = is_array($value) ? implode(',', $value) : $value;
					break;
			}
		}

		$data = xvmpMedium::getFilteredAsArray(array_filter($filter));
		$this->setData(array_filter($data));
	}


	public function initFilter() {
		$filter_item = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilTextInputGUI($this->pl->txt('username'), 'username');
		$this->ctrl->setParameterByClass('ilViMPPlugin', 'ref_id', $_GET['ref_id']);
		$filter_item->setDataSource($this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilViMPPlugin'),
			'addUserAutoComplete', "", true));
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilMultiSelectSearchInputGUI($this->pl->txt('category'), 'categories');
		$categories = xvmpCategory::getAll();
		$options = array();
		/** @var xvmpCategory $category */
		foreach ($categories as $category) {
			$options[$category->getId()] = $category->getNameWithPath();
		}
		$filter_item->setOptions($options);
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilTextInputGUI($this->pl->txt('tags'), 'tags');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new srDateDurationInputGUI($this->pl->txt('create_date'), 'created');
		$filter_item->setShowTime(false);
		$filter_item->setStartText($this->pl->txt('from'));
		$filter_item->setEndText($this->pl->txt('to'));

		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilCombinationInputGUI($this->pl->txt('duration'), 'duration');
		$filter_subitem = new ilDurationInputGUI($this->pl->txt('min'), 'duration_min');
		$filter_subitem->setShowSeconds(true);
		$filter_item->addCombinationItem('min', $filter_subitem, 'Min');
		$filter_subitem = new ilDurationInputGUI($this->pl->txt('max'), 'duration_max');
		$filter_subitem->setShowSeconds(true);
		$filter_item->addCombinationItem('max', $filter_subitem, 'Max');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilCombinationInputGUI($this->pl->txt('views'), 'views');
		$filter_subitem = new ilNumberInputGUI($this->pl->txt('min'), 'views_min');
		$filter_item->addCombinationItem('min', $filter_subitem, 'Min');
		$filter_subitem = new ilNumberInputGUI($this->pl->txt('max'), 'views_max');
		$filter_item->addCombinationItem('max', $filter_subitem, 'Max');
		$this->addAndReadFilterItem($filter_item);

		// custom filters
		foreach (xvmpConf::getConfig(xvmpConf::F_FILTER_FIELDS) as $field) {
			$filter_item = new ilTextInputGUI($field[xvmpConf::F_FILTER_FIELD_TITLE], $field[xvmpConf::F_FILTER_FIELD_ID]);
			$this->addAndReadFilterItem($filter_item);
		}
	}


	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$checked = xvmpSelectedMedia::isSelected($a_set['mid'], $this->parent_obj->getObjId());
		if ($checked) {
			$this->tpl->setVariable('VAL_CHECKED', 'checked');
		}

		foreach ($this->available_columns as $title => $props)
		{
			if ($title == 'thumbnail') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
				continue;
			} elseif ($title == 'description' && strlen($a_set[$title]) > 95) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 90) . '...');
			} elseif ($title == 'title' && strlen($a_set[$title]) > 50) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 45) . '...');
			} else {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			}

		}
	}
}