<?php 

namespace App\Controllers;

use App\Models\CustomerModel;

class DepositController{

    // deposit page
    public static function depositPage(){
        $balance = CustomerController::getBalance(); // get current balance
        return view('customer/deposit', ['balance'=>$balance]);
    }

    // store deposit money method
    public static function depositMoney(){
        // if method is not post request die request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if(isset($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0){
            $amount = $_POST['amount'];
            // call storage to store deposit amount
            $dbCall = new CustomerModel();
            $dbCall->deposit($amount);
        }else{
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('deposit');
            exit;
        }
    }
    
}