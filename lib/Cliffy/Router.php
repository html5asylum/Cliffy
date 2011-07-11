<?php

class Cliffy_Router extends Cliffy_app {
    
    private static $instance;
    private $app = null;
    private $routes;

    private function __construct() {
        $this->app = Cliffy_App::getInstance();
        $this->routes = array();
    }

    public function getInstance() {
        if(!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }


    /**
     * @param: $array( 
     *      'original' => array (
     *          'controller' => 'newcontroller',
     *          )
     *      'action' => array(
     *          'action' => 'index'
     *      )
     **/
    public function setRouter($aRouter) {
        $this->routes = array_merge($this->routes, $aRouter);
    }

    public function routeMessage($incoming) {
        if(isset($this->routes[$incoming['type']])) {
            // reroute type
            $incoming['routed'] = $incoming['type'];
            $incoming['controller']   = $this->routes[$incoming['type']];
        } else {
            $incoming['controller'] = $incoming['type'];
        }

        // Todo: Throw exception

        return $incoming;
    }

}

