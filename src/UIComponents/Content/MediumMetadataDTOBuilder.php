<?php

namespace srag\Plugins\ViMP\Content;

use ilViMPPlugin;
use xvmpMedium;
use srag\Plugins\ViMP\UIComponents\PlayerModal\MediumAttribute;
use ConfigAR;
use UserProgressAR;
use ILIAS\DI\Container;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MediumMetadataDTOBuilder
{
    /**
     * @var ilViMPPlugin
     */
    private $plugin;
    /**
     * @var Container
     */
    private $dic;

    /**
     * VideoMetadataDTOBuilder constructor.
     * @param ilViMPPlugin $plugin
     */
    public function __construct(Container $dic, ilViMPPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->dic = $dic;
    }

    /**
     * @param xvmpMedium $medium
     * @param bool       $short        short version is used e.g. for tiles or list elements
     * @param bool       $show_watched show watched percentage
     * @return MediumMetadataDTO
     */
    public function buildFromVimpMedium(xvmpMedium $medium, bool $short, bool $show_watched) : MediumMetadataDTO
    {
        return new MediumMetadataDTO(
            $medium->getMid(),
            $medium->getTitle(),
            $medium->getDescription(),
            $this->buildMediumInfos($medium, $short, $show_watched),
            !$medium->isTranscoded(),
            $medium->getThumbnail(),
            $medium->getStartdate(),
            $medium->getEnddate()
        );
    }

    /**
     * @param xvmpMedium $medium
     * @param bool       $short
     * @param bool       $show_watched
     * @return array
     */
    protected function buildMediumInfos(xvmpMedium $medium, bool $short, bool $show_watched) : array
    {
        $medium_infos = [];

        $medium_infos[] = new MediumAttribute($medium->getDurationFormatted(),
            $this->plugin->txt(xvmpMedium::F_DURATION));
        $medium_infos[] = new MediumAttribute($medium->getCreatedAt('d.m.Y, H:i'),
            $this->plugin->txt(xvmpMedium::F_CREATED_AT));

        if (!$short) {
            foreach (ConfigAR::getConfig(ConfigAR::F_FORM_FIELDS) as $field) {
                if ($field[ConfigAR::F_FORM_FIELD_SHOW_IN_PLAYER]
                    && ($value = $medium->getField($field[ConfigAR::F_FORM_FIELD_ID]))) {
                    $medium_infos[] = new MediumAttribute($value, $field[ConfigAR::F_FORM_FIELD_TITLE]);
                }
            }
        }

        if ($show_watched) {
            $medium_infos[] = new MediumAttribute(
                UserProgressAR::calcPercentage($this->dic->user()->getId(), $medium->getMid()) . '%',
                $this->plugin->txt('watched'));
        }

        return $medium_infos;
    }

}
