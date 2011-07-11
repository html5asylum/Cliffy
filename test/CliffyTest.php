<?php

class CliffyTests extends PHPUnit_Framework_TestCase {

    public function testSingletonPatternCliffy() {
        $cliffy = Cliffy_App::getInstance();

        $this->assertType('object', $cliffy);
        $this->assertEquals(Cliffy_App::getInstance(), $cliffy);
    }

    public function testSetServer() {
        $ip = '0.0.0.0';
        $port = '1337';
        $server = new Server_Websocket($ip, $port);
        $cliffy = Cliffy_App::getInstance();
        $cliffy->setServer('Server_Websocket', $ip, $port);

        $this->assertEquals($server, $cliffy->server);
    }

    public function testCliffyRouter() {
        $cliffy = Cliffy_Router_Mock::getInstance();
        $aWrongRoute = array('test' => 'reroute');
        $aCorrectRoute = array('ping2' => 'reroute');

        $cliffy->installRoute($aWrongRoute);
        $cliffy->installRoute($aCorrectRoute);

        $aDefaultMessage = array(
            'type' => 'ping',
            'msg' => 'pong'
            );
        $aDefaultMessage2 = array(
            'type' => 'ping2',
            'msg' => 'pong'
            );

        $sBadJsonMessage = json_encode($aDefaultMessage);
        $sGoodJsonMessage = json_encode($aDefaultMessage2);
        $aRouterReturn = $cliffy->preDispatch($sBadJsonMessage);


        $this->assertType('array', $aRouterReturn);
        $sExpectedKey = 'controller';
        $sExpectedValue = 'reroute';

        $this->assertFalse(
            $this->checkKeyAndValueExists(
                $sExpectedKey,
                $sExpectedValue,
                $aRouterReturn
                )
            );

        $aRouterReturn = $cliffy->preDispatch($sGoodJsonMessage);
        $this->assertTrue(
            $this->checkKeyAndValueExists(
                $sExpectedKey,
                $sExpectedValue,
                $aRouterReturn
                )
            );
    
    }

    public function checkKeyAndValueExists($key,$value,$arr){ 
        return array_key_exists($key, $arr) && array_search($value,$arr)!==false;

    }

}

class Cliffy_Router_Mock extends Cliffy_App {
    public function preDispatch($sJsonMessage) {
        return Cliffy_Router::getInstance()->routeMessage($sJsonMessage);
    }

}


