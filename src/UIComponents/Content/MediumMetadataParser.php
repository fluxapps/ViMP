<?php

namespace srag\Plugins\ViMP\Content;

use ilViMPPlugin;
use ILIAS\DI\Container;
use DateTime;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MediumMetadataParser
{
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
     */
    public function parseAvailability(/*?DateTime*/ $availability_start, /*?DateTime*/ $availability_end, bool $short) : string
    {
        if (!is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_between' . ($short ? '_short' : '')),
                $availability_start->format(self::DATE_FORMAT),
                $availability_end->format(self::DATE_FORMAT));
        }
        if (!is_null($availability_start) && is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_from' . ($short ? '_short' : '')),
                $availability_start->format(self::DATE_FORMAT));
        }
        if (is_null($availability_start) && !is_null($availability_end)) {
            return sprintf($this->plugin->txt('availability_to' .($short ? '_short' : '')),
                $availability_end->format(self::DATE_FORMAT));
        }
        return '';
    }
}
