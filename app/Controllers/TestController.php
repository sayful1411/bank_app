<?php 
namespace App\Controllers;

class TestController extends CustomerController{
    public static function test(){
        $data = static::getTransactionData();
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}