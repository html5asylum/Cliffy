<?php

class Server_Websocket extends Cliffy_App {

    protected $address  = '127.0.0.1';

    protected $port     = '8080';

    protected $users    = array();
    protected $sockets  = array();
    protected $maxClients   = 20;
    protected $master   = null;
    protected $buffer   = null;
    public $requestHandler   = null;

    public function __construct( $address, $port ) {
        $this->address = $address;
        $this->port = $port;
        $this->app = parent::getInstance();
    }

    public function startListening() {
        var_dump('Creating master: '.$this->address);
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('couldnt create socket'); //throw new Server_Websocket_Exception('Couldnt create socket');

        $this->sockets[] = $this->master;

        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die('couldnt reuse address'); // throw new Server_Websocket_Exception('Couldnt reuse address');
        var_dump('Binding to: '.$this->port);
        socket_bind($this->master, $this->address, $this->port) or die('couldnt bind to address'); //throw new Server_Websocket_Exception('couldnt bind to address');
        socket_listen($this->master, 20);
        return $this->master;

    }


    // Run server
    public function run() {
    // Todo work magic on callback
    $test = $this->app->getRequestHandler();
    if(!$test) {
        echo "Warning! starting server->run() without request handler!\n";
    } else {
        $this->requestHandler = $test;
    }
        while(true) {
            // TODO Handle queue properly

            // Get changed sockets
            $changed = $this->sockets;
            if(count($this->getUsers()) <= 1 ) {
                // Poll for activity
                socket_select($changed, $write=NULL, $except=NULL,NULL);
            } else {
                // Buffer 1 sec
                socket_select($changed, $write=NULL, $except=NULL, 1, 0);
            }
            
            
            // TODO Move to request handle
            foreach($changed as $socket) {
                // Accept new connection
                if($socket == $this->master && count($this->getUsers()) < $this->maxClients ) {
                    $client = socket_accept($this->master);

                    if($client < 0 || $client == false) {
                        $this->say('Socket accept failed');
                        continue;
                    } else {
                        $this->connect($client);
                    }
                } else {
                    // Serve request
                    $bytes = @socket_recv($socket, $this->buffer, 2048, 0);
                    if($bytes == 0) {
                        $this->requestHandler->disconnect($socket);
                    } else {
                        $user = $this->getUserBySocket($socket);
                        $this->requestHandler->setUser($user);
                        if(!$user->handshake ) {
                            $user->doHandshake($this->buffer);
                        } else {
                            $this->buffer = $this->unwrap($this->buffer);
                            $this->app->setUser($user);
                            echo "RCV: ".$this->buffer."\n";
                            $request =$this->app->preDispatch($this->buffer);
                            $this->app->Dispatch($request);
                            $this->app->postDispatch($request, $user);
                        }
                    }
                }
            }
        }
    }

    public function getUserById($id) {
        $found=null;
        foreach($this->users as $user) {
            if( @ $user->id==$id) {
                $found=$user;
                break;
            }
        }
        return $found;
    }

    public function send($client, $msg){
        $msg = trim($msg);
        $this->say( '> ' . $msg );
        $msg = $this->wrap($msg);
        socket_write($client, $msg, strlen($msg));
    }

    public function sendToAll(Cliffy_Client $from, $sJsonMessage, $bSendToSelf) {
        foreach($this->getUsers() as $user) {
            if($from->id == $user->id && !$bSendToSelf) {
                continue;
            }
            $this->send($user->socket, $sJsonMessage);
        }
    }

    private function say($msg) {
        echo date('H:i]').$msg."\n";
    }

    public function getUser() {
        return $this->user;
    }
    public function getUsers() {
        return $this->users;
    }

    // Daemonize server
    public function daemonize() {
        // Todo Daemonize it!

    }

    public function connect($socket) {
        $this->users[] = new Cliffy_Client($socket);
        $this->sockets[] = $socket;
    }

    protected function getUserBySocket($socket) {
       foreach($this->users as $user) {
           if($user->socket == $socket) {
               return $user;
            }
        }
        return false;
    }

    private function wrap($msg="") { return chr(0).$msg.chr(255); }
    private function unwrap($msg="") { return substr($msg, 1, strlen($msg)-2); }



}
