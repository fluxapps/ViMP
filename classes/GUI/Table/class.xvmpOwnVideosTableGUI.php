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
	 * xvmpOwnVideosTableGUI constructor.
	 *
	 * @param     $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$ilCtrl = $DIC['ilCtrl'];
		$id = 'xvmp_own_' . $_GET['ref_id'] . '_' . $ilUser->getId();
		$this->setId($id);
		$this->setPrefix($id);
		$this->setFormName($id);
		$ilCtrl->saveParameter($parent_gui, $this->getNavParameter());

		parent::__construct($parent_gui, $parent_cmd);

		$this->setDisableFilterHiding(true);
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');
		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpSearch.base_link = "'.$base_link.'";');

		if ($parent_cmd !== xvmpOwnVideosGUI::CMD_SHOW_FILTERED) {
			$this->tpl = new ilTemplate("tpl.own_videos_table.html", true, true, $this->pl->getDirectory());
			$this->tpl->setVariable('TABLE_CONTENT_HIDDEN', 'hidden');
			$this->tpl->setCurrentBlock('xvmp_show_videos_button');
			$this->tpl->setVariable('SHOW_VIDEOS_LINK', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_SHOW_FILTERED));
			$this->tpl->setVariable('SHOW_VIDEOS_LABEL', $this->pl->txt('btn_show_own_videos'));
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	 *
	 */
	protected function initColumns() {
		$this->addColumn($this->pl->txt('added'), '', 75, false);
		$this->addColumn('', '', 210, true);

		parent::initColumns();

		$this->addColumn('', '', 75, true);
	}


	public function initFilter() {
		$filter_item = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$this->addAndReadFilterItem($filter_item);

		$filter_item = new ilMultiSelectSearchInputGUI($this->pl->txt('category'), 'category');
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
			if (!$field[xvmpConf::F_FILTER_FIELD_ID]) {
				continue;
			}
			$filter_item = new ilTextInputGUI($field[xvmpConf::F_FILTER_FIELD_TITLE], $field[xvmpConf::F_FILTER_FIELD_ID]);
			$this->addAndReadFilterItem($filter_item);
		}
	}


	/**
	 *
	 */
	public function parseData() {
	    $pre_filter = array();
	    $post_filter = array();

		foreach ($this->filters as $filter_item) {
			$value = $filter_item->getValue();
			$postvar = $filter_item->getPostVar();
			switch ($postvar) {
                case 'title':
                    $pre_filter['filterbyname'] = $post_filter[$postvar] = is_array($value) ? implode(',', $value) : $value;
                    break;
                case 'category[]':
                    $pre_filter['filterbycategory'] = is_array($value) ? implode(',', $value) : $value;
                    $post_filter['category'] = $value;
                    break;
				case 'created':
                    $post_filter[$postvar.'_min'] = $value['start'];
                    $post_filter[$postvar.'_max'] = $value['end'];
					break;
                case 'tags':
                    $post_filter[$postvar] = array_map('trim', array_filter(explode(',', $value)));
                    break;
				default:
                    $post_filter[$postvar] = is_array($value) ? implode(',', $value) : $value;
					break;
			}
		}

		// fetch data with pre filter
        $pre_filter = array_filter($pre_filter);
		$videos = xvmpMedium::getUserMedia($this->user, $pre_filter);

		$data = array();

		foreach ($videos as $video) {
			$data[$video['mid']] = xvmpMedium::formatResponse($video);
		}

		foreach (xvmpUploadedMedia::where(array('email' => $this->user->getEmail()))->get() as $uploaded_media) {
			if (!in_array($uploaded_media->getMid(), array_keys($data))) {
				try {
					$data[$uploaded_media->getMid()] = xvmpMedium::getObjectAsArray($uploaded_media->getMid());
				} catch (xvmpException $e) {
					if ($e->getCode() == 404 && strpos($e->getMessage(), "Medium not exist") !== false) {
						$uploaded_media->delete();
					}
					continue;
				}
			}
		}

		// post filter data
        $data = $this->postFilterData($post_filter, $data);

        $this->tpl_global->addOnLoadCode('VimpSearch.videos = ' . json_encode($data) . ';');
		$this->setData($data);
		$this->setMaxCount(count($data));
	}

	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
        $transcoded = ($a_set['status'] === 'legal');
        $transcoding = ($a_set['status'] === 'converting');

        if ($transcoded) {
            $this->tpl->touchBlock('transcoded');
        } else {
            $this->tpl->touchBlock('transcoding');
            $this->tpl->setVariable('PLAY_OVERLAY_ATTRIBUTES', 'hidden');
            if ($transcoding) {
                $this->tpl->setVariable('PROGRESS_BAR',
                    (new xvmpProgressBarUI($a_set['mid'], $this->pl, $this->dic))->getHTML());
            }
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


        foreach ($this->available_columns as $title => $props) {
            if ($title == 'published') {
                $this->tpl->setVariable('VAL_' . strtoupper($title),  $this->pl->txt($a_set[$title]));
            } else {
                $this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
            }
        }

        foreach ($this->getSelectableColumns() as $title => $props) {
            if ($this->isColumnSelected($title)) {
                $this->tpl->setCurrentBlock('generic');
                $this->tpl->setVariable('VAL_GENERIC', $this->parseColumnValue($title, $a_set[$title]));
                $this->tpl->parseCurrentBlock();
            }
        }

        // for some reason, we have to do this a second time, because of the blocks i guess
        $this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$this->tpl->setVariable('VAL_ACTIONS', $this->buildActionList($a_set));
	}

    /**
     * @return array
     */
    function getSelectableColumns() {
        $selectable_columns = array(
            'categories' => array(
                'sort_field' => 'categories',
                'txt' => $this->pl->txt('categories')
            )
        );
        foreach (xvmpConf::getConfig(xvmpConf::F_FILTER_FIELDS) as $filter_field) {
            $selectable_columns[$filter_field[xvmpConf::F_FILTER_FIELD_ID]] = array(
                'sort_field' => $filter_field[xvmpConf::F_FILTER_FIELD_ID],
                'txt' => $filter_field[xvmpConf::F_FILTER_FIELD_TITLE]
            );
        }
        return $selectable_columns;
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
		if ($a_set['status'] == 'legal') {
			$actions->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_EDIT_VIDEO));
			$actions->addItem($this->lng->txt('change_owner'), 'change_owner', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_CHANGE_OWNER));
		}
		$actions->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_DELETE_VIDEO));
		return $actions->getHTML();
	}

    /**
     * @param $post_filter
     * @param $data
     * @return array
     */
    protected function postFilterData($post_filter, $data) {
        if (!empty($post_filter['title'])) {
            $data = array_filter($data, function ($video) use ($post_filter) {
                return strpos(strtolower($video['title']), strtolower($post_filter['title'])) !== false;
            });
        }

        if (!empty(array_filter($post_filter['category']))) {
            $data = array_filter($data, function ($video) use ($post_filter) {
                $categories = array_keys($video['categories']);
                return !empty(array_intersect($categories, $post_filter['category']));
            });
        }

        if (!empty($post_filter['tags'])) {
            $data = array_filter($data, function ($video) use ($post_filter) {
                $tags = array_map('trim', array_filter(explode(',', $video['tags'])));
                return !empty(array_intersect($tags, $post_filter['tags']));
            });
        }

        if ($post_filter['created_min']) {
            $data = array_filter($data, function ($video) use ($post_filter) {
                return strtotime($video['created_at']) > $post_filter['created_min'];
            });
        }

        if ($post_filter['created_max']) {
            $data = array_filter($data, function ($video) use ($post_filter) {
                return strtotime($video['created_at']) < $post_filter['created_max'];
            });
        }

        foreach (xvmpConf::getConfig(xvmpConf::F_FILTER_FIELDS) as $custom_filter_field) {
            $field_id = $custom_filter_field[xvmpConf::F_FILTER_FIELD_ID];
            if ($post_filter[$field_id]) {
                $data = array_filter($data, function ($video) use ($post_filter, $field_id) {
                    return strpos(strtolower($video[$field_id]), strtolower($post_filter[$field_id])) !== false;
                });
            }
        }
        return $data;
    }
}
