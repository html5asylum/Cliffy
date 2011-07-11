<?php

class Cliffy_Client {

    public $id = null;
    public $lastaction = true;

    public $socket = null;

    public $handshake = false;

    public $ip = null;

    public $lastAction = null;

    public $data = array();

    private $userRoles = array('guest');
    public $isAuthenticated = false;

    public function __construct($socket) {
        $this->id = uniqid();
        $this->lastaction = microtime(true);
        $this->socket = $socket;

        socket_getpeername($socket, $ip);
        $this->ip = $ip;

        $this->data = array(
            'name' => $this->id,
            'roles' => $this->userRoles,
        );
    }

    public function setName($sNewName) {
        $this->data['name'] = htmlspecialchars($sNewName, ENT_QUOTES);
    }

    public function setAuthenticated($bAuth) {
        if($bAuth) {
            $this->isAuthenticated = true;
        }
    }

    public function setUserRole($sNewRole) {
        $this->userRoles[$sNewRole];
    }
    public function getUserRoles() {
        return $this->userRoles;
    }

    public function doHandshake($buffer) {

        list($resource, $headers, $securityCode) = $this->handleRequestHeader($buffer);

        $securityResponse = '';
        if (isset($headers['Sec-WebSocket-Key1']) && isset($headers['Sec-WebSocket-Key2'])) {
            $securityResponse = $this->getHandshakeSecurityKey($headers['Sec-WebSocket-Key1'], $headers['Sec-WebSocket-Key2'], $securityCode);
        } else {
            return false;
        }

        if ($securityResponse) {
            $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: WebSocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Origin: " . $headers['Origin'] . "\r\n" .
                "Sec-WebSocket-Location: ws://" . $headers['Host'] . $resource . "\r\n" .
                "\r\n".$securityResponse;
        } else {
            $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: WebSocket\r\n" .
                "Connection: Upgrade\r\n" .
                "WebSocket-Origin: " . $headers['Origin'] . "\r\n" .
                "WebSocket-Location: ws://" . $headers['Host'] . $resource . "\r\n" .
                "\r\n";
        }

        socket_write($this->socket, $upgrade.chr(0), strlen($upgrade.chr(0)));

        $this->handshake = true;
        return true;
    }

    private function handleSecurityKey($key) {
        preg_match_all('/[0-9]/', $key, $number);
        preg_match_all('/ /', $key, $space);
        if ($number && $space) {
            return implode('', $number[0]) / count($space[0]);
        }
        return '';
    }

    private function getHandshakeSecurityKey($key1, $key2, $code) {
        return md5(
            pack('N', $this->handleSecurityKey($key1)).
            pack('N', $this->handleSecurityKey($key2)).
            $code,
            true
        );
    }

    private function handleRequestHeader($request) {
        $resource = $code = null;
        preg_match('/GET (.*?) HTTP/', $request, $match) && $resource = $match[1];
        preg_match("/\r\n(.*?)\$/", $request, $match) && $code = $match[1];
        $headers = array();
        foreach(explode("\r\n", $request) as $line) {
            if (strpos($line, ': ') !== false) {
                list($key, $value) = explode(': ', $line);
                $headers[trim($key)] = trim($value);
            }
        }
        return array($resource, $headers, $code);
    }
}

