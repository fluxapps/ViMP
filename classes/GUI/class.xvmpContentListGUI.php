<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * Class xvmpContentListGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentListGUI {

	/**
	 * @var xvmpContentGUI
	 */
	protected $parent_gui;
    /**
     * @var Container
     */
	protected $dic;
    private ilGlobalTemplateInterface $tpl;

    /**
	 * xvmpContentTilesGUI constructor.
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$this->dic = $DIC;
		$this->pl = ilViMPPlugin::getInstance();
		$this->parent_gui = $parent_gui;
        $this->tpl = $DIC->ui()->mainTemplate();

		$this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/content_list.css'));
		$this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getAssetURL('js/xvmp_content.js'));
		$this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getAssetURL('js/waiter.js'));
		$this->dic->ui()->mainTemplate()->addCss($this->pl->getAssetURL('default/waiter.css'));
	}


    /**
     * @return string|void
     * @throws arException
     * @throws ilTemplateException
     */
	public function getHTML() {
		$selected_media = xvmpSelectedMedia::where(array('obj_id' => $this->parent_gui->getObjId(), 'visible' => 1))->orderBy('sort');
		if (!$selected_media->hasSets()) {
            $this->tpl->setOnScreenMessage("info", $this->pl->txt('msg_no_videos'));
			return;
		}

		$tpl = new ilTemplate('tpl.content_tiles_waiting.html', true, true, $this->pl->getDirectory());

		$json_array = array();
		/** @var xvmpSelectedMedia $media */
		foreach ($selected_media->get() as $media) {
			$json_array[] = $media->getMid();
			$tpl->setCurrentBlock('block_box');
			$tpl->setVariable('MID', $media->getMid());
			$tpl->parseCurrentBlock();
		}

		$this->dic->ui()->mainTemplate()->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
		$this->dic->ui()->mainTemplate()->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->dic->ctrl()->getLinkTarget($this->parent_gui, '', '', true) . "';");
		$this->dic->ui()->mainTemplate()->addOnLoadCode("VimpContent.template = 'ListItem';");
		$this->dic->ui()->mainTemplate()->addOnLoadCode('VimpContent.loadTilesInOrder(0);');
		return $tpl->get();
	}
}
