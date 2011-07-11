<?php

set_include_path('../lib');
function autoloader($class) {
    $sFile = '../lib/'.str_replace('_','/',$class).'.php';
    require_once($sFile);
}

spl_autoload_register('autoloader');

