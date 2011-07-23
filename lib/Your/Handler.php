<?php 

class Your_Handler {

    public $request;
    public $response;
    protected $user;
    private $app;
    private static $instance;

    private function __construct() {
        $this->user = null;

    }

    public function disconnect($socket) {
        // Do something
    }

    public static function getInstance() {
        if(!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    public function Dispatch($request) {
        $this->request = $request;
        $controller = 'Your_Handler_'.ucfirst($this->request['controller']);
        $controller = new $controller();
        if(!isset($request['action'])) {
            $request['action'] = 'indexAction';
        }
        
        $this->response = $controller->$request['action']($request['msg']);
        return $this->response;
    }

    public function getResponse() {
        if(!isset($this->response)) {
            return array();
        }

        return $this->response;
    }
    public function setUser(Cliffy_Client $user) {
        $this->user = $user;
    }
}
