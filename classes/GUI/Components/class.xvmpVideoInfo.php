<?php

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpVideoInfo
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
     * @var string
     */
    protected $style = '';
    /**
     * @var bool
     */
    protected $ellipsis = false;

    /**
     * xvmpVideoInfo constructor.
     * @param string $title
     * @param string $value
     */
    public function __construct(string $value, string $title = '')
    {
        $this->title = $title;
        $this->value = $value;
    }

    public function withStyle(string $style) : self
    {
        $new = clone $this;
        $new->style = $style;
        return $new;
    }

    public function withEllipsis(bool $ellipsis) : self
    {
        $new = clone $this;
        $new->ellipsis = $ellipsis;
        return $new;
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

    /**
     * @return string
     */
    public function getStyle() : string
    {
        return $this->style;
    }

    /**
     * @return bool
     */
    public function isEllipsis() : bool
    {
        return $this->ellipsis;
    }

}
