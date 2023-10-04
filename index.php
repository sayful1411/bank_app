#! /usr/bin/env php
<?php 

use App\Controllers\BankCLIAppController;

// autoloading classes

require_once __DIR__ . "/vendor/autoload.php";

$BankCLIApp = new BankCLIAppController();
$BankCLIApp->run();