<?php

namespace srag\Plugins\ViMP\UIComponents\PlayerModal;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MediumAttribute
{

    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $value;

    /**
     * VideoInfo constructor.
     * @param string $title
     * @param string $value
     */
    public function __construct(string $value, string $title = '')
    {
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

}
