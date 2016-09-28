<?php

define('__ROOT__', dirname(__FILE__));
require_once 'vendor/autoload.php';

foreach(glob(__ROOT__.'/src/*/*.php') as $file) {
    require_once($file);
}