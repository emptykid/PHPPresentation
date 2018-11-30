<?php
/**
 * Created by PhpStorm.
 * User: xiaoqiang
 * Date: 2018/11/29
 * Time: 11:59 AM
 */

namespace PhpOffice\PhpPresentation\Shape;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\ComparableInterface;


class FlatShape extends AbstractShape implements ComparableInterface
{
    private $type;
    public function __construct() {
        parent::__construct();
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

}