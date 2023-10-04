<?php 

require_once __DIR__ . "/../vendor/autoload.php";

// get all routes

$routes = require_once __DIR__ . "/../routes/web.php";

Router::run();