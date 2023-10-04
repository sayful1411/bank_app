#! /usr/bin/env php
<?php 

use App\Controllers\AdminController;
use App\Models\CustomerStorage;

require_once __DIR__ . "/vendor/autoload.php";


$admin = new AdminController(new CustomerStorage);
$admin->run();