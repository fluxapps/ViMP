<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpVideoPlayer
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpVideoPlayer {

	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var xvmpMedium
	 */
	protected $video;

	/**
	 * @var array
	 */
	protected $options = array(
		"controls" => true,
		"autoplay" => false,
		"preload" => "auto",
		"fluid" => true,
		"playbackRates" => [0.5, 1.0, 1.25, 1.5]
	);

	protected static $count = 1;



	public function __construct($video) {
		global $tpl;
		$this->tpl = $tpl;
		$this->pl = ilViMPPlugin::getInstance();
		if (is_int($video)) {
			$video = xvmpMedium::find($video);
		}
		$this->video = $video;
	}

	public static function loadVideoJSAndCSS($load_observer) {
		global $tpl;
		if ($load_observer) {
			$tpl->addJavaScript(ilViMPPlugin::getInstance()->getDirectory() . '/templates/default/xvmp_observer.js');

		}
		$tpl->addJavaScript(ilViMPPlugin::getInstance()->getDirectory() . '/vendor/video-js-6.4.0/video.min.js');
		$tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/vendor/video-js-6.4.0/video-js.min.css');
		$tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/templates/default/video.css');
	}

	public function getHTML() {
		$template = $this->pl->getTemplate('default/tpl.video.html');

		$medium = $this->video->getMedium();
		if (is_array($medium)) {
			$medium = $medium[0];
		}
		$id = ilUtil::randomhash();
		$pathinfo = pathinfo($medium);

		$template->setVariable('ID', $id);
		$template->setVariable('SOURCE', $medium);
		$template->setVariable('THUMBNAIL', $this->video->getThumbnail());
		$template->setVariable('TYPE', $pathinfo['extension']);

		if (!isset($this->options['width']) && !isset($this->options['width'])) {
			$template->setVariable('CSS_CLASS', 'vjs-4-3');
			$this->setOption('fluid', true);
		} else {
			$this->setOption('fluid', false);
		}

		$options = json_encode($this->options);
		$videojs_script = "videojs('xvmp_video_{$id}', {$options}, function () { $('#xvmp_video_{$id}').on('contextmenu', function(e) { e.preventDefault(); });});";


		$template->setCurrentBlock('script');
		$template->setVariable('SCRIPT', $videojs_script);
		$template->parseCurrentBlock();

		return $template->get();
	}


	public function setOption($option, $value) {
		if ($value === null) {
			unset($this->options[$option]);
		} else {
			$this->options[$option] = $value;
		}
	}


}