<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilTemplate;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;
use ilViMPPlugin;
use ILIAS\DI\Container;
use xvmpMedium;
use DateTime;
use xvmpException;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ContentElementRenderer
{
    const CONTAINER_TEMPLATE_PATH = __DIR__ . '/../../../templates/default/tpl.content_element.html';
    const DATE_FORMAT = 'd.m.Y';
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
     * @param DateTime|null $availability_start
     * @param DateTime|null $availability_end
     * @throws xvmpException
     */
    protected function parseAvailability(/*?DateTime*/ $availability_start, /*?DateTime*/ $availability_end) : string
    {
        if (!is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_between'),
                $availability_start->format(self::DATE_FORMAT),
                $availability_end->format(self::DATE_FORMAT));
        }
        if (!is_null($availability_start) && is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_from'),
                $availability_start->format(self::DATE_FORMAT));
        }
        if (is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_to'),
                $availability_end->format(self::DATE_FORMAT));
        }
        throw new xvmpException(xvmpException::INTERNAL_ERROR, 'error parsing availability');
    }

    /**
     * @throws xvmpException
     */
    protected function buildInnerTemplate(MediumMetadataDTO $mediumMetadataDTO) : ilTemplate
    {
        $tpl = $this->getInnerTemplate();
        if ($mediumMetadataDTO->isAvailable()) {
            $tpl->touchBlock('play_overlay');
        } else {
            $tpl->setCurrentBlock('not_available_overlay');
            $tpl->setVariable('AVAILABILITY', $this->parseAvailability(
                $mediumMetadataDTO->getAvailabilityStart(),
                $mediumMetadataDTO->getAvailabilityEnd()));
            $tpl->parseCurrentBlock();
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
        $tpl->setVariable('DESCRIPTION', nl2br(strip_tags($mediumMetadataDTO->getDescription(50)), false));
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
