<?php
/**
 * Created by PhpStorm.
 * User: xiaoqiang
 * Date: 2018/11/28
 * Time: 9:20 PM
 */

namespace PhpOffice\PhpPresentation\Shape;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\ComparableInterface;


class Rectangle extends AbstractShape implements ComparableInterface
{

    public function __construct() {
        $this->type = "shape";
        parent::__construct();
    }

}