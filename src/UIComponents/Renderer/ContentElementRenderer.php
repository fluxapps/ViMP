<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilViMPPlugin;
use ILIAS\DI\Container;
use xvmpMedium;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ContentElementRenderer
{

    /**
     * @var ilViMPPlugin
     */
    protected $plugin;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @param Container    $dic
     * @param ilViMPPlugin $plugin
     */
    public function __construct(Container $dic, ilViMPPlugin $plugin)
    {
        $this->dic = $dic;
        $this->plugin = $plugin;
    }

    /**
     * @param MediumMetadataDTO $mediumMetadataDTO
     * @return ilTemplate
     */
    protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getTemplate();
        if ($mediumMetadataDTO->isAvailable()) {
            $tpl->setCurrentBlock('playable');
        } else {
            $tpl->setCurrentBlock('not_playable');
        }

        $tpl->setVariable('MID', $mediumMetadataDTO->getMid());
        $tpl->setVariable('THUMBNAIL', $mediumMetadataDTO->getThumbnailUrl());
        $tpl->parseCurrentBlock();

        $tpl->setVariable('TITLE', $mediumMetadataDTO->getTitle());
        $tpl->setVariable('DESCRIPTION', nl2br(strip_tags($mediumMetadataDTO->getDescription(50)), false));
        $tpl->setVariable('LABEL_TITLE', $this->plugin->txt(xvmpMedium::F_TITLE) . ':');
        $tpl->setVariable('LABEL_DESCRIPTION', $this->plugin->txt(xvmpMedium::F_DESCRIPTION) . ':');

        if ($mediumMetadataDTO->isTranscoding()) {
            $tpl->setCurrentBlock('info_transcoding');
            $tpl->setVariable('INFO_TRANSCODING', $this->plugin->txt('info_transcoding_short'));
            $tpl->parseCurrentBlock();
        }

        foreach ($mediumMetadataDTO->getMediumInfos() as $mediumInfo) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $mediumInfo->getTitle() ?
                $mediumInfo->getTitle() . ': ' . $mediumInfo->getValue() :
                $mediumInfo->getValue());
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    public function render(MediumMetadataDTO $mediumMetadataDTO) : string
    {
        $tpl = $this->buildTemplate($mediumMetadataDTO);
        return $tpl->get();
    }

    abstract protected function getTemplate() : ilTemplate;
}
