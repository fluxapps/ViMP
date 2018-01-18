<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpOwnVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpOwnVideosTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.own_videos_row.html';

	protected $js_files = array('xvmp_search_videos.js');
	protected $css_files = array('xvmp_video_table.css');


	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'title' => array(
			'sort_field' => 'title',
			'width' => 210
		),
		'published' => array(
			'sort_field' => 'published'
		),
		'status' => array(
			'sort_field' => 'status'
		),
		'created_at' => array(
			'sort_field' => 'unix_time'
		)
	);

	/**
	 * @var xvmpOwnVideosGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * xvmpOwnVideosTableGUI constructor.
	 *
	 * @param int    $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->user = $ilUser;
		$this->setId('own_' . $_GET['ref_id']);
		parent::__construct($parent_gui, $parent_cmd);
		$this->setDisableFilterHiding(true);
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');
		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpSearch.base_link = "'.$base_link.'";');
	}


	/**
	 *
	 */
	protected function initColumns() {
		$this->addColumn('', '', 75, true);
		$this->addColumn('', '', 210, true);

		parent::initColumns();

		$this->addColumn('', '', 75, true);
	}


	public function initFilter() {
		$filter_item = new ilTextInputGUI($this->pl->txt('title'), 'title');
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

		// custom filters
		foreach (xvmpConf::getConfig(xvmpConf::F_FILTER_FIELDS) as $field) {
			$filter_item = new ilTextInputGUI($field[xvmpConf::F_FILTER_FIELD_TITLE], $field[xvmpConf::F_FILTER_FIELD_ID]);
			$this->addAndReadFilterItem($filter_item);
		}
	}


	/**
	 *
	 */
	public function parseData() {
		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			$postvar = $filter_item->getPostVar();
			switch ($postvar) {
				case 'created':
					$filter[$postvar.'_min'] = $value['start'];
					$filter[$postvar.'_max'] = $value['end'];
					break;
				default:
					$filter[$postvar] = is_array($value) ? implode(',', $value) : $value;
					break;
			}
		}

		$filter['userid'] = xvmpUser::getVimpUser($this->user)->getId();

		$videos = xvmpMedium::getFilteredAsArray(array_filter($filter));

		$data = array();

		foreach ($videos as $video) {
			$data[$video['mid']] = $video;
		}


		foreach (xvmpUploadedMedia::where(array('user_id' => $this->user->getId()))->get() as $uploaded_media) {
			if (!in_array($uploaded_media->getMid(), array_keys($data))) {
				$data[$uploaded_media->getMid()] = xvmpMedium::getObjectAsArray($uploaded_media->getMid());
			}
		}

		$this->tpl_global->addOnLoadCode('VimpSearch.videos = ' . json_encode($data) . ';');
		$this->setData($data);
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

		if ($a_set['status'] == 'error') {
			$this->tpl->setVariable('VAL_DISABLED', 'disabled');
		}

		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$checked = xvmpSelectedMedia::isSelected($a_set['mid'], $this->parent_obj->getObjId());
		if ($checked) {
			$this->tpl->setVariable('VAL_CHECKED', 'checked');
		}

		$this->tpl->setVariable('VAL_STATUS_TEXT', $this->pl->txt('status_' . $a_set['status']));
		$this->tpl->setVariable('VAL_VISIBLE', (int) $transcoded);


		foreach ($this->available_columns as $title => $props)
		{
			$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
		}

		$this->tpl->setVariable('VAL_ACTIONS', $this->buildActionList($a_set));
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @param $a_set
	 *
	 * @return string
	 */
	protected function buildActionList($a_set) {
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($this->lng->txt('actions'));
		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);
//		if ($a_set['status'])
		$actions->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_EDIT_VIDEO));
		$actions->addItem($this->lng->txt('change_owner'), 'change_owner', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_CHANGE_OWNER));
		$actions->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_DELETE_VIDEO));
		return $actions->getHTML();
	}
}