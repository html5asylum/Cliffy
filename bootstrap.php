<?php
set_include_path('lib');
function autoloader($class) {
    $sFile = 'lib/'.str_replace('_','/',$class).'.php';
    require_once($sFile);
}

spl_autoload_register('autoloader');

$app = Cliffy_App::getInstance();
$app->setServer('Server_Websocket','127.0.0.1', 8180);
$app->setRequestHandler('Your_Handler');
$app->installRoute(array(
    'ping' => 'generic',
    'cookie' => 'generic')
);
$app->server->startListening();
// do initalisation stuff...
$app->server->run();

