<?php

class SelectedMedium
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $obj_id;
    /**
     * @var int
     */
    protected $mid;
    /**
     * @var bool
     */
    protected $visible;
    /**
     * @var bool
     */
    protected $lp_is_required;
    /**
     * @var int
     */
    protected $lp_req_percentage;
    /**
     * @var int
     */
    protected $sort;

    public function __construct(
        int $id,
        int $obj_id,
        int $mid,
        bool $visible,
        bool $lp_is_required,
        int $lp_req_percentage,
        int $sort
    ) {
        $this->id = $id;
        $this->obj_id = $obj_id;
        $this->mid = $mid;
        $this->visible = $visible;
        $this->lp_is_required = $lp_is_required;
        $this->lp_req_percentage = $lp_req_percentage;
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return int
     */
    public function getMid() : int
    {
        return $this->mid;
    }

    /**
     * @return bool
     */
    public function isVisible() : bool
    {
        return $this->visible;
    }

    /**
     * @return bool
     */
    public function isLpRequired() : bool
    {
        return $this->lp_is_required;
    }

    /**
     * @return int
     */
    public function getLpReqPercentage() : int
    {
        return $this->lp_req_percentage;
    }

}