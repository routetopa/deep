<?php

require '../Slim/Slim.php';
require_once dirname(__FILE__) . '/PHPRtfLite-1.3.1/lib/PHPRtfLite.php';

/**
* Class DeepExport
*/
class DeepExport
{
    private static $instance = null;
    private $app;

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    private function __construct()
    {
        \Slim\Slim::registerAutoloader();
        $this->app = new \Slim\Slim();

        $this->app->post('/export-datalet-as-img', function(){
            if(!empty($this->app->request()->params('svg_data'))) {
                try {
                    echo ($this->createImage($this->app->request()->params('svg_data')));
                }catch (Exception $e){}
            }
        });

        $this->app->post('/export-datalet-as-rtf', function(){
            if(!empty($this->app->request()->params('svg_data'))) {
                try {
                    // register PHPRtfLite class loader
                    PHPRtfLite::registerAutoloader();

                    // rtf document
                    $rtf = new PHPRtfLite();

                    $sect = $rtf->addSection();
                    $sect->writeText($_REQUEST["datalet"], new PHPRtfLite_Font(14), new PHPRtfLite_ParFormat('center'));
                    $sect->addImageFromString($this->createImage($this->app->request()->params('svg_data')), PHPRtfLite_Image::TYPE_PNG);
                    $sect->writeText($_REQUEST["dataset"], new PHPRtfLite_Font(14), new PHPRtfLite_ParFormat('center'));
                    // save rtf document
                    
                    $rtf->sendRtf('datalet.rtf');
                }catch (Exception $e){}
            }
        });
    }

    private function createImage($svg_data)
    {
        $svg = $svg_data;

        $chart = new Imagick();
        $chart->setFormat('SVG');
        $chart->readImageBlob($svg);
        $chart->setFormat("png24");

        $logo = new Imagick();
        $logo->readImage("pbrtpa.bmp");

        $image = new Imagick();
        $image->setFormat("png24");
        $image->newImage($chart->getImageWidth() > $logo->getImageHeight() ? $chart->getImageWidth() : $logo->getImageWidth(),
            $chart->getImageHeight()+$logo->getImageHeight(),
            "white");

        $image->compositeImage($chart, Imagick::COMPOSITE_COPY, 0, 0);
        $image->compositeImage($logo, Imagick::COMPOSITE_COPY, 20, $chart->getImageHeight());

        return $image;
    }

    public function run(){
        //run the Slim app
        $this->app->run();
    }
}