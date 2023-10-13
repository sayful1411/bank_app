<?php 

namespace App\Controllers;

use App\Models\CustomerModel;

class WithdrawController{

    // withdraw page
    public static function withdrawPage(){
        $balance = CustomerController::getBalance(); // get current balance
        return view('customer/withdraw', ['balance'=>$balance]);
    }

    // store withdraw money method
    public static function withdrawMoney(){
        // if method is not post request die request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if(isset($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0){
            $amount = $_POST['amount'];
            // call storage to store withdraw amount
            $dbCall = new CustomerModel();
            $dbCall->withdraw($amount);
        }else{
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('deposit');
            exit;
        }
    }
}