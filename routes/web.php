<?php

use App\Controllers\Controller;
use App\Controllers\CustomerController;

require_once __DIR__ . '/Router.php';

/**
 * Admin Page Route
 * Customer Page Route
*/

Router::get('', [App\Controllers\Controller::class, 'index']);


// authentication route
Router::get('login', [App\Controllers\CustomerController::class,'loginPage']);
Router::get('register', []);

Router::post('customer_register', []);