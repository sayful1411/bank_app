<?php 

namespace App\Controllers;

use App\Models\DBStorage;

class DepositController{

    public static function depositPage(){
        $balance = CustomerController::getBalance();
        return view('customer/deposit', ['balance'=>$balance]);
    }

    public static function depositMoney(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if(isset($_POST['amount']) && $_POST['amount'] > 0){
            $amount = $_POST['amount'];

            require_once __DIR__ . "/../Models/DBStorage.php";
            $dbCall = new DBStorage();
            $dbCall->deposit($amount);
        }else{
            session_start();
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('deposit');
            exit;
        }
    }
    
}