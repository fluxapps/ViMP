<?php

use ILIAS\DI\Container;

/**
 * Class xvmpProgressBarUI
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpProgressBarUI
{
    /**
     * @var ilPlugin
     */
    private $plugin;
    /**
     * @var int
     */
    protected $mid;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var Container
     */
    protected $dic;

    protected static $js_loaded = false;

    /**
     * xvmpProgressBarUI constructor.
     * @param int      $mid
     * @param ilPlugin $plugin
     * @param          $dic
     */
    public function __construct(int $mid, ilPlugin $plugin, Container $dic)
    {
        $this->dic = $dic;
        $this->mid = $mid;
        $this->tpl = $plugin->getTemplate('default/tpl.progress_bar.html');
        $this->plugin = $plugin;
        $this->addJS();
    }

    protected function addJS()
    {
        if (!self::$js_loaded) {
            $this->dic->ui()->mainTemplate()->addJavaScript($this->plugin->getDirectory() . '/js/xvmp_progress_bar.min.js');
            self::$js_loaded = true;
        }
        $this->dic->ctrl()->setParameterByClass(ilObjViMPGUI::class, ilObjViMPGUI::GET_VIDEO_ID, $this->mid);
        $url = $this->dic->ctrl()->getLinkTargetByClass(ilObjViMPGUI::class, ilObjViMPGUI::CMD_TRANSCODING_PROGRESS);
        $this->dic->ui()->mainTemplate()->addOnLoadCode('VimpProgressBar.init(' . $this->mid . ', "' . $url . '");');
    }

    /**
     * @return string
     * @throws ilTemplateException
     */
    public function getHTML() : string
    {
        $this->tpl->setVariable('TEXT_TRANSCODING', $this->plugin->txt('transcoding'));
        $this->tpl->setVariable('MID', $this->mid);
        try {
            $progress = xvmpRequest::getTranscodingProgress($this->mid, 1);
        } catch (xvmpException $e) {
            xvmpCurlLog::getInstance()->logError($e->getCode(), $e->getMessage());
            $progress = '...';
        }
        $this->tpl->setVariable('PROGRESS', $progress);

        return $this->tpl->get();
    }
}
