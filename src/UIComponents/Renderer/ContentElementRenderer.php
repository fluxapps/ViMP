<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilViMPPlugin;
use ILIAS\DI\Container;
use xvmpMedium;
use DateTime;
use xvmpException;
use srag\Plugins\ViMP\Content\MediumMetadataParser;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ContentElementRenderer
{
    const CONTAINER_TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_element.html';
    const DATE_FORMAT = 'd.m.Y';
    const DESCRIPTION_LENGTH = 30;
    /**
     * @var MediumMetadataParser
     */
    protected $metadata_parser;
    /**
     * @var ilViMPPlugin
     */
    protected $plugin;

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @param MediumMetadataParser $metadata_parser
     * @param Container            $dic
     * @param ilViMPPlugin         $plugin
     */
    public function __construct(MediumMetadataParser $metadata_parser, Container $dic, ilViMPPlugin $plugin)
    {
        $this->dic = $dic;
        $this->plugin = $plugin;
        $this->metadata_parser = $metadata_parser;
    }

    /**
     * @throws xvmpException
     */
    protected function buildInnerTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getInnerTemplate();

        if ($mediumMetadataDTO->isAvailable()) {
            $tpl->touchBlock('play_overlay');
            if ($mediumMetadataDTO->hasAvailability()) { // is available but has availability
                $this->fillAvailabilityInfo($mediumMetadataDTO, $tpl);
            }
        } else {
            $this->fillAvailabilityOverlay($mediumMetadataDTO, $tpl);
        }

        $tpl->setVariable('MID', $mediumMetadataDTO->getMid());
        $tpl->setVariable('THUMBNAIL', $mediumMetadataDTO->getThumbnailUrl());
        $tpl->parseCurrentBlock();

        if (!$mediumMetadataDTO->isAvailable()) {
            $tpl->setCurrentBlock('info_message');
            $tpl->setVariable('INFO_MESSAGE', $this->plugin->txt('info_not_available'));
            $tpl->parseCurrentBlock();
        } elseif ($mediumMetadataDTO->isTranscoding()) {
            $tpl->setCurrentBlock('info_message');
            $tpl->setVariable('INFO_MESSAGE', $this->plugin->txt('info_transcoding_short'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('TITLE', $mediumMetadataDTO->getTitle());
        $tpl->setVariable('DESCRIPTION', nl2br(strip_tags($mediumMetadataDTO->getDescription(static::DESCRIPTION_LENGTH)), false));
        $tpl->setVariable('LABEL_TITLE', $this->plugin->txt(xvmpMedium::F_TITLE));
        $tpl->setVariable('LABEL_DESCRIPTION', $this->plugin->txt(xvmpMedium::F_DESCRIPTION));

        $this->fillMediumInfos($mediumMetadataDTO, $tpl);
        return $tpl;
    }

    /**
     * @param MediumMetadataDTO $mediumMetadataDTO
     * @param ilTemplate        $tpl
     */
    protected function fillMediumInfos(MediumMetadataDTO $mediumMetadataDTO, ilTemplate $tpl)
    {
        foreach ($mediumMetadataDTO->getMediumAttributes() as $mediumAttribute) {
            $tpl->setCurrentBlock('info_paragraph');
            $tpl->setVariable('INFO', $mediumAttribute->getTitle() ?
                $mediumAttribute->getTitle() . ': ' . $mediumAttribute->getValue() :
                $mediumAttribute->getValue());
            $tpl->parseCurrentBlock();
        }
    }

    protected function fillAvailabilityInfo(MediumMetadataDTO $mediumMetadataDTO, ilTemplate $tpl)
    {
        $tpl->setCurrentBlock('info_paragraph');
        $tpl->setVariable('INFO', $this->metadata_parser->parseAvailability(
            $mediumMetadataDTO->getAvailabilityStart(),
            $mediumMetadataDTO->getAvailabilityEnd(),
            false
        ));
        $tpl->parseCurrentBlock();
    }

    protected function fillAvailabilityOverlay(MediumMetadataDTO $mediumMetadataDTO, ilTemplate $tpl)
    {
        $tpl->setCurrentBlock('not_available_overlay');
        $tpl->setVariable('AVAILABILITY', $this->metadata_parser->parseAvailability(
            $mediumMetadataDTO->getAvailabilityStart(),
            $mediumMetadataDTO->getAvailabilityEnd(),
            false
        ));
        $tpl->parseCurrentBlock();
    }

    public function render(MediumMetadataDTO $mediumMetadataDTO) : string
    {
        $tpl = $this->buildTemplate($mediumMetadataDTO);
        return $tpl->get();
    }

    protected function getContainerTemplate() : ilTemplate
    {
        return new ilTemplate(self::CONTAINER_TEMPLATE_PATH, true, true);
    }

    abstract protected function buildTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate;

    abstract protected function getInnerTemplate() : ilTemplate;

}
