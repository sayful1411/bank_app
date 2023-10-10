<?php


require_once __DIR__ . '/Router.php';

/**
 * Admin Page Route
 * Customer Page Route
*/

Router::get('', [App\Controllers\Controller::class, 'index']);


// authentication route
Router::get('register', [App\Controllers\CustomerController::class,'registerPage']);
Router::get('login', [App\Controllers\CustomerController::class,'loginPage']);
Router::get('logout', [App\Controllers\CustomerController::class,'logoutPage']);

Router::post('customer_register', [App\Controllers\CustomerController::class,'customerRegister']);
Router::post('customer_login', [App\Controllers\CustomerController::class,'customerLogin']);

// dashboard
Router::get('dashboard', [App\Controllers\CustomerDashboardController::class,'dashboard']);


// deposit
Router::get('deposit', [App\Controllers\DepositController::class,'depositPage']);
Router::post('depositMoney', [App\Controllers\DepositController::class,'depositMoney']);

// withdraw
Router::get('withdraw', [App\Controllers\WithdrawController::class,'withdrawPage']);
Router::post('withdrawMoney', [App\Controllers\WithdrawController::class,'withdrawMoney']);

// withdraw
Router::get('transfer', [App\Controllers\TransferController::class,'transferPage']);
Router::post('transferMoney', [App\Controllers\TransferController::class,'transferMoney']);

Router::get('test', [App\Controllers\TestController::class,'test']);