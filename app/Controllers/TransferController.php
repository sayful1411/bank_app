<?php 

namespace App\Controllers;

use App\Models\DBStorage;

class TransferController{

    public static function transferPage(){
        $balance = CustomerController::getBalance();
        return view('customer/transfer', ['balance'=>$balance]);
    }

    public static function transferMoney(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }

        if(isset($_POST['email'])){
            session_start();
            $customer_id = $_SESSION['customer_id'];
            $email = $_POST['email'];

            if(isset($_POST['amount']) && $_POST['amount'] > 0){
                $amount = $_POST['amount'];
    
                require_once __DIR__ . "/../Models/DBStorage.php";
                $dbCall = new DBStorage();
                $dbCall->transfer($customer_id, $email, $amount);
            }else{
                session_start();
                $_SESSION['error_message'] = "Invalid amount";
                return redirect('deposit');
                exit;
            }
        }else{
            session_start();
            $_SESSION['error_message'] = "Recipient Not found";
            return redirect('deposit');
            exit;
        }
    }

}