<?php

class Your_Handler_Generic extends Your_Handler {
    private $parent;
    private $app;

    public function __construct() {
    }

    public function indexAction($args) {
        $aReturn = array();
        $aReturn['ping'] = 'pong';
        // internal route
        $aReturn['init'] = $this->initAction();
        $aReturn['data'] = $this->getData();
        return $aReturn;
    }

    public function initAction($args) {
        return 'blah';
        
    }
    public function pingAction($args) {
        return 'TEST PING!';
    }

    private function getData() {
        return array('secret' => 'data');
    }

