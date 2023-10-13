<?php


require_once __DIR__ . '/Router.php';

/**
 * Customer Route
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

// Router::get('test', [App\Controllers\TestController::class,'test']);

/**
 * Admin Route
 */

// admin authentication route
Router::get('admin/register', [App\Controllers\AdminController::class,'adminRegisterPage']);
Router::get('admin/login', [App\Controllers\AdminController::class,'adminLoginPage']);
Router::get('admin/logout', [App\Controllers\AdminController::class,'adminLogoutPage']);

Router::post('admin_register', [App\Controllers\AdminController::class,'adminRegister']);
Router::post('admin_login', [App\Controllers\AdminController::class,'adminLogin']);

 // dashboard
Router::get('admin/dashboard', [App\Controllers\AdminDashboardController::class,'adminDashboard']);

// all customer
Router::get('admin/customers', [App\Controllers\AdminDashboardController::class,'getCustomerData']);

// add customer
Router::get('admin/add-customer', [App\Controllers\AdminDashboardController::class,'addCustomer']);
Router::post('customer_register_by_admin', [App\Controllers\AdminDashboardController::class,'customerRegisterByAdmin']);

// all transactions
Router::get('admin/transactions', [App\Controllers\AdminDashboardController::class,'getAllTransaction']);

// transaction by customer
Router::get('admin/customer', [App\Controllers\AdminDashboardController::class,'customerTransaction']);