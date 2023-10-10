<?php 

namespace App\Controllers;

use App\Models\DBStorage;

class WithdrawController{
    public static function withdrawPage(){
        $balance = CustomerController::getBalance();
        return view('customer/withdraw', ['balance'=>$balance]);
    }

    public static function withdrawMoney(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if(isset($_POST['amount']) && $_POST['amount'] > 0){
            $amount = $_POST['amount'];

            require_once __DIR__ . "/../Models/DBStorage.php";
            $dbCall = new DBStorage();
            $dbCall->withdraw($amount);
        }else{
            session_start();
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('deposit');
            exit;
        }
    }
}