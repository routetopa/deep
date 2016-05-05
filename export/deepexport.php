<?php

require '../Slim/Slim.php';

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
                    $svg = $this->app->request()->params('svg_data');

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

                    echo $image;
                }catch (Exception $e){}
            }
        });
    }
    
    public function run(){
        //run the Slim app
        $this->app->run();
    }
}