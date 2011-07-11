<?php

class Cliffy_App {

    public $sSocketReceiveData;
    public $server;
    public $user;
    public $requestHandler;
    public $aRequestBody;
    private static $instance;
    private $router;

    private function __construct() {

    }

    public function getInstance() {
        if(!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    public function setServer($sServerName, $sHostname = '127.0.0.1', $iPortNo = 8080) {
        $this->server = new $sServerName($sHostname, $iPortNo);
    }

    public function setRequestHandler($sClassName) {
        $this->requestHandler = $sClassName::getInstance();
    }
    public function getRequestHandler() {
        return $this->requestHandler;
    }

    private function setRequestBody($aRequest) {
        $this->aRequestBody = $aRequest;
    }

    public function getRequestBody() {
        return $this->aRequestBody;
    }

    public function preDispatch($buffer){
        $aRequest = json_decode($buffer, true);
        if($aRequest === null) {
            return false;
        }
        $aRequest = $this->getRouter()->routeMessage($aRequest);
        $this->setRequestBody($aRequest);
        return $this->aRequestBody;

    }

    private function getRouter() {
        if(!isset($this->router)) {
            $this->router = Cliffy_Router::getInstance();
        }

        return $this->router;
    }

    public function Dispatch($buffer) {
        $this->requestHandler->setUser($this->user);
        $this->requestHandler->Dispatch($buffer);
    }

    public function postDispatch($buffer, $user) {
        $aReturn = $this->requestHandler->getResponse();
        if(is_array($aReturn)) {
            foreach($aReturn as $type => $sMessage) {
                $aMessage = array(
                    'type' => $type,
                    'msg' => $sMessage,
                    'uid' => $user->id
                );
                $sReturnJson = json_encode($aMessage, JSON_FORCE_OBJECT);
                unset($aMessage);
                $this->server->send($user->socket, $sReturnJson);
            
            }
        } else {
            $aMessage = array(
                'type' => $buffer['type'],
                'msg' => $aReturn,
                'uid' => $user->id
            );
            $sReturnJson = json_encode($aMessage, JSON_FORCE_OBJECT);
            unset($aMessage);
            $this->server->send($user->socket, $sReturnJson);
        }
        $sReturnJson = json_encode($aMessage, JSON_FORCE_OBJECT);
        unset($aMessage);
        // TODO make proper send function
        // $this->sendMessage($this->json);
        $this->server->send($user->socket, $sReturnJson);
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser(Cliffy_Client $user) {
        $this->user = $user;
    }

    public function installRoute($aRoute) {
        $this->getRouter()->setRouter($aRoute);
    }


}
