<?php 

namespace App\Controllers;

use App\Models\CustomerModel;

class TransferController{

    // transfer money page
    public static function transferPage(){
        $balance = CustomerController::getBalance(); // get current balance
        return view('customer/transfer', ['balance'=>$balance]);
    }

    // store transfer money method
    public static function transferMoney(){
        // if method is not post request die request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        // check email not match db record
        if(!isset($_POST['email'])){
            $_SESSION['error_message'] = "Recipient Not found";
            return redirect('deposit');
            exit;
        }

        $customer_id = $_SESSION['customer_id'];
        $email = $_POST['email'];

        if(isset($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0){
            $amount = $_POST['amount'];
            // call storage to transfer amount
            $dbCall = new CustomerModel();
            $dbCall->transfer($customer_id, $email, $amount);
        }else{
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('deposit');
            exit;
        }
    }

}