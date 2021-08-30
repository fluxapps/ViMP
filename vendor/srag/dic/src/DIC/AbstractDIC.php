<?php

namespace srag\DIC\ViMP\DIC;

use ILIAS\DI\Container;
use srag\DIC\ViMP\Database\DatabaseDetector;
use srag\DIC\ViMP\Database\DatabaseInterface;

/**
 * Class AbstractDIC
 *
 * @package srag\DIC\ViMP\DIC
 */
abstract class AbstractDIC implements DICInterface
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container &$dic)
    {
        $this->dic = &$dic;
    }


    /**
     * @inheritDoc
     */
    public function database() : DatabaseInterface
    {
        return DatabaseDetector::getInstance($this->databaseCore());
    }
}
