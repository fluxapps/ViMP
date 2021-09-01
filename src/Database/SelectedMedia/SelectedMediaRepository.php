<?php

namespace srag\Plugins\ViMP\Database\SelectedMedia;

use ILIAS\DI\Container;
use Matrix\Exception;

class SelectedMediaRepository
{
    /**
     * @var Container
     */
    protected $dic;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    public function getByObjId(int $obj_id) : array
    {
        return SelectedMediaAR::getSelected($obj_id);
    }

    /**
     * called to fetch a single SelectedMediaAR
     * @param array $where
     * @return SelectedMediaAR
     */
    public function getSelectedMedium(array $where) : SelectedMediaAR
    {
        return SelectedMediaAR::where($where)->first();
    }

    public function addVideo(int $mid, int $obj_id, bool $visible = true)
    {
        if (self::videoIsSelected($obj_id, $mid))
            throw new Exception("Video already added");

        $ar = new SelectedMediaAR();
        $ar->setMid($mid);
        $ar->setObjId($obj_id);
        $ar->setVisible($visible);
        $sort = SelectedMediaAR::where(['obj_id' => $obj_id])->count() + 1;
        $ar->setSort($sort * 10);
        $ar->create();

    }

    public function removeVideo(int $obj_id, int $mid) {
        if(!$ar = self::videoIsSelected($obj_id, $mid))
            throw new Exception("Video is not selected");
        $ar->delete();
        SelectedMediaAR::reSort($obj_id);


    }

    /**
     * called to fetch several SelectedMediaAR from the database
     * @param array $where
     * @return array of SelectedMediaAR
     */
    public function getSelectedMedia(array $where) : array
    {
        return SelectedMediaAR::where($where)->get();
    }

    protected static function videoIsSelected(int $obj_id, int $mid)
    {
        $ar = SelectedMediaAR::where(array('obj_id' => $obj_id, 'mid' => $mid))->first();
        return $ar;

    }

}