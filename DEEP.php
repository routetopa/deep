<?php
/*
@license
    The MIT License (MIT)

    Copyright (c) 2015 Dipartimento di Informatica - Università di Salerno - Italy

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

/**
 * Developed by :
 * ROUTE-TO-PA Project - grant No 645860. - www.routetopa.eu
 */

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'Slim/Slim.php';

/**
 * Class DEEP
 */
class DEEP {
    private static $instance = null;
    private $app;
    private $all_datalets;
    private $all_controllets;

    private $controllet_repository_url;
    private $datalet_repository_url;

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

        $this->loadRepositoryUrl("configuration.xml");

        $this->all_datalets = $this->loadServices("datalets.xml", $this->datalet_repository_url);
        $this->app->get('/datalets-list', function(){
            $this->app->response()->header("Content-Type", "application/json");
            $this->app->response()->header("Access-Control-Allow-Origin", "*");
            echo json_encode($this->all_datalets);

        });

        $this->all_controllets = $this->loadServices("controllets.xml", $this->controllet_repository_url);
        $this->app->get('/controllets-list', function(){
            $this->app->response()->header("Content-Type", "application/json");
            $this->app->response()->header("Access-Control-Allow-Origin", "*");
            echo json_encode($this->all_controllets);

        });

        //main service
        $this->app->get('/', function(){
            echo "Hello from web compoments RESTful service, call /datalets-list to get datalets list";
        });
    }

    public function loadRepositoryUrl($source)
    {
        $handler_configuration = simplexml_load_file($source) or die("ERROR: cant read Components configuration \n");
        $this->datalet_repository_url = $handler_configuration->deep_datalet_configuration->components_repository_url_reference;
        $this->controllet_repository_url = $handler_configuration->deep_controllets_configuration->components_repository_url_reference;
    }

    /**
     * @param $source
     * @return array
     */
    public function loadServices($source, $repository_url){
        $components_array = array();
        $handler_configuration = simplexml_load_file($source) or die("ERROR: cant read Components configuration \n");

        foreach($handler_configuration->components->children() as $component){
            //array_push($components_array, $component->name."");
            $component->url = $repository_url . $component->name . "/";
            array_push($components_array, $component);
            $this->app->get('/'.$component->name, function() use($component, $repository_url){
                $response = array(
                    "name"           => $component->name."",
                    "bridge_link"    => $repository_url."",
                    "component_link" => $component->name."/".$component->name.".html",
                    "idm"            => $component->idm
                );

                if(isset($component->attributes)) {
                    $response['attributes'] = array();
                    foreach ($component->attributes->children() as $attribute) {
                        array_push($response['attributes'], $attribute->name."");
                    }
                }

                $this->app->response()->header("Content-Type", "application/json");
                $this->app->response()->header("Access-Control-Allow-Origin", "*");
                echo json_encode($response);
            });
        }
        return $components_array;
    }

    public function run(){
        //run the Slim app
        $this->app->run();
    }


}