<?php
/**
 * Created by PhpStorm.
 * User: xiaoqiang
 * Date: 2018/11/28
 * Time: 6:04 PM
 */

use PhpOffice\PhpPresentation\Autoloader;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\Shape\Drawing;
use PhpOffice\PhpPresentation\Shape\Group;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Bullet;
use PhpOffice\PhpPresentation\Style\Color;

require_once __DIR__ . '/../src/PhpPresentation/Autoloader.php';
Autoloader::register();
require_once __DIR__ .'/../src/Common/Autoloader.php';
\PhpOffice\Common\Autoloader::register();

$pptReader = IOFactory::createReader('PowerPoint2007');
$oPHPPresentation = $pptReader->load('/Users/xiaoqiang/Projects/WebSite/PHPPresentation/samples/resources/test11.pptx');


class Renderer {
    protected $presentation;
    protected $outputJson;

    public function __construct(PhpPresentation $ppt)
    {
        $this->presentation = $ppt;
        $this->outputJson = array();
    }

    public function render() {

        $this->outputJson["summary"] = $this->parseMain();
        $this->outputJson["pages"] = $this->parsePage();
        print_r($this->outputJson);
    }

    public function parseMain() {
        $summary = array(
            "count" => $this->presentation->getSlideCount(),
            "layoutName" => $this->presentation->getLayout()->getDocumentLayout(),
            "category" => $this->presentation->getDocumentProperties()->getCategory(),
            "width" => $this->presentation->getLayout()->getCX(DocumentLayout::UNIT_PIXEL),
            "height" => $this->presentation->getLayout()->getCY(DocumentLayout::UNIT_PIXEL)
        );
        return $summary;
    }

    public function parsePage() {
         $pages = array();
        foreach ($this->presentation->getAllSlides() as $page) {
            $pageInfo = array(
                "hash" => $page->getHashCode(),
                "left" => $page->getOffsetX(),
                "top" => $page->getOffsetY(),
            );
            $oBkg = $page->getBackground();
            if ($oBkg instanceof Slide\AbstractBackground) {
                if ($oBkg instanceof Slide\Background\Color) {
                    $pageInfo["backgroundColor"] = $page->getColor()->getRGB();
                }
                if ($oBkg instanceof Slide\Background\Image) {
                    $sBkgImgContents = file_get_contents($oBkg->getPath());
                    $pageInfo["backgroundColor"] = $page->getColor()->getRGB();
                }
            }

            $pageInfo["shapeCount"] = count($page->getShapeCollection());
            $pageInfo["shapes"] = array();
            foreach ($page->getShapeCollection() as $shape) {
                if($shape instanceof Group) {
                    foreach ($shape->getShapeCollection() as $oShapeChild) {
                        array_push($pageInfo["shapes"], $this->parseShape($oShapeChild));
                    }
                } else {
                    array_push($pageInfo["shapes"], $this->parseShape($shape));
                }
            }

            array_push($pages, $pageInfo);
        }
        return $pages;
    }

    public function parseShape(AbstractShape $shape) {
        $shapeInfo = array(
            "hash" => $shape->getHashCode(),
            "left" => $shape->getOffsetX(),
            "top" => $shape->getOffsetY(),
            "width" => $shape->getWidth(),
            "height" => $shape->getHeight(),
            "rotation" => $shape->getRotation()
        );
        if (!is_null($shape->getFill())) {
            switch($shape->getFill()->getFillType()) {
                case \PhpOffice\PhpPresentation\Style\Fill::FILL_NONE:
                    $shapeInfo["fillColor"] = "";
                    break;
                case \PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID:
                    $shapeInfo["fillColor"] = $shape->getFill()->getStartColor()->getRGB();
                    $shapeInfo["fillColorAlpha"] = $shape->getFill()->getStartColor()->getAlpha();
                    break;
            }
        }

        if ($shape instanceof Drawing\Gd) {
            $shapeInfo["type"] = "pic";
            $shapeInfo["name"] = $shape->getName();
            $shapeInfo["description"] = $shape->getDescription();
            ob_start();
            call_user_func($shape->getRenderingFunction(), $shape->getImageResource());
            $sShapeImgContents = ob_get_contents();
            ob_end_clean();
            $shapeInfo["mimeType"] = $shape->getMimeType();
            $shapeInfo["src"] = 'data:'.$shape->getMimeType().';base64,'.base64_encode($sShapeImgContents);
        } else if ($shape instanceof RichText) {
            $shapeInfo["type"] = "text";
            $shapeInfo["content"] = array();
            foreach ($shape->getParagraphs() as $paragraph) {
                foreach ($paragraph->getRichTextElements() as $text) {
                    $textInfo = array(
                        "text" => $text->getText(),
                        "fontFamily" => $text->getFont()->getName(),
                        "fontSize" => $text->getFont()->getSize(),
                        "fontColor" => $text->getFont()->getColor()->getARGB(),
                        "bold" => $text->getFont()->isBold(),
                        "italic" => $text->getFont()->isItalic()
                    );
                    array_push($shapeInfo['content'], $textInfo);
                }
            }
        }

        return $shapeInfo;
    }
}

$render = new Renderer($oPHPPresentation);
$render->render();
