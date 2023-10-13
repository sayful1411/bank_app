<?php 

namespace App\Models;

use App\Traits\AvatarGeneratorTrait;
use PDO;
use PDOException;

class CustomerModel extends Model{

    use AvatarGeneratorTrait;

    // store customer data
    public function store($name,$email,$password){
        try{
            
            // Check if the email already exists in the database
            if($this->isAdminEmailRegistered($email)){
                $_SESSION['error_message'] = 'The email is already registered as an admin';
                return redirect('register');
            }

            // Check if customer and admin email is same
            if($this->isCustomerEmailRegistered($email)){
                $_SESSION['error_message'] = 'Email already exists';
                return redirect('register');
            }

            // Insert Customer Information
            $insertCustomerStatement  = $this->db->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            $customerInsertResult =  $insertCustomerStatement ->execute([$name, $email, $password]);

            if (!$customerInsertResult) {
                throw new PDOException('Failed to insert customer data');
            }

            // Get the ID of the newly inserted customer
            $customer_id = $this->db->lastInsertId();

            $insertAccountStatement = $this->db->prepare("INSERT INTO accounts (customer_id, balance) VALUES (?, 0)");
            $accountInsertResult  = $insertAccountStatement->execute([$customer_id]);

            if (!$accountInsertResult) {
                throw new PDOException('Failed to create customer account');
            }

            $_SESSION['success_message'] = 'Registration was successful! You can now log in.';
            return redirect('login');
        }catch (PDOException $e) {
            // Handle exceptions
            return redirect('register?error=' . $e->getMessage());
        }
    }

    // check if customer already exist
    public function isCustomerEmailRegistered($email){
        // Check if the email exists in the customers table
        $customerEmail = $this->db->prepare("SELECT email FROM customers WHERE email = ?");
        $customerEmail->execute([$email]);
    
        return !empty($customerEmail->fetch(PDO::FETCH_ASSOC));
    }
    
    // check if admin already exist
    public function isAdminEmailRegistered($email){
        // Check if the email exists in the admins table
        $adminEmail = $this->db->prepare("SELECT email FROM admins WHERE email = ?");
        $adminEmail->execute([$email]);
    
        return !empty($adminEmail->fetch(PDO::FETCH_ASSOC));
    }

    // customer login
    public function  login($email,$password){
        $customerStmt = $this->db->prepare("SELECT * FROM customers WHERE email = ?");
        $customerStmt->execute([$email]);

        $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        if($customer && password_verify($password, $customer['password'])){
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];

            // generate avatar
            $name = $_SESSION['customer_name'];
            $_SESSION['avatar_name'] = $this->generateAvatar($name);

            return redirect('dashboard');
            exit;
        }else{
            $_SESSION['error_message'] = 'Sorry, wrong credentials';
            return redirect('login');
            exit;
        }
    }

    // deposit record
    public function deposit($amount){
        $customer_id = $_SESSION['customer_id'];
        
        try{
            // Create a deposit transaction record
            $depositTransactionStmt = $this->db->prepare("INSERT INTO transactions (account_id, transaction_type, amount) VALUES (?, ?, ?)");
            $depositTransactionStmt->execute([$customer_id, "Deposit", $amount]);

            // Update account balance
            $updateBalanceStmt = $this->db->prepare("UPDATE accounts SET balance = balance + ? WHERE customer_id = ?");
            $updateBalanceStmt->execute([$customer_id, $amount]);

            $_SESSION['success_message'] = "Deposit successful.";
            return redirect('deposit');
        }catch(\PDOException $e){
            echo $e->getMessage();
        }
    }

    // withdraw record
    public function withdraw($amount){
        $customer_id = $_SESSION['customer_id'];
        
        // get balance data
        $balanceStmt = $this->db->prepare("SELECT balance FROM accounts WHERE customer_id = ?");
        $balanceStmt->execute([$customer_id]);
        $row = $balanceStmt->fetch(PDO::FETCH_ASSOC);

        if($row && $row['balance'] >= $amount){
            try{
                // Create a withdraw transaction record
                $withdrawTransactionStmt = $this->db->prepare("INSERT INTO transactions (account_id, transaction_type, amount) VALUES (?, ?, ?)");
                $withdrawTransactionStmt->execute([$customer_id, "Withdraw", $amount]);
    
                // Update account balance
                $updateBalanceStmt = $this->db->prepare("UPDATE accounts SET balance = balance - ? WHERE customer_id = ?");
                $updateBalanceStmt->execute([$customer_id, $amount]);
    
                $_SESSION['success_message'] = "Withdraw successful.";
                return redirect('withdraw');

            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        }else{
            $_SESSION['error_message'] = "Insufficient balance";
            return redirect('withdraw');
        }
    }

    // balance record
    public function balance(){
        $customer_id = $_SESSION['customer_id'];

        // get balance
        $balanceStmt = $this->db->prepare("SELECT balance FROM accounts WHERE customer_id = ?");
        $balanceStmt->execute([$customer_id]);
        $row = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $result = $row['balance'];
        return $result;
    }

    // check if sender and receiver exist
    public function validateSenderReceiver($senderId, $receiverId) {
        // Check if the sender and receiver exist in the database
        $stmtSender = $this->db->prepare("SELECT COUNT(*) FROM customers WHERE id = ?");
        $stmtSender->execute([$senderId]);
        $senderExists = (bool) $stmtSender->fetchColumn();
    
        $stmtReceiver = $this->db->prepare("SELECT COUNT(*) FROM customers WHERE id = ?");
        $stmtReceiver->execute([$receiverId]);
        $receiverExists = (bool) $stmtReceiver->fetchColumn();
    
        if (!$senderExists || !$receiverExists) {
            // return "Sender or receiver does not exist.";
            $_SESSION['error_message'] = "Sender or receiver does not exist";
            return redirect('transfer');
            exit;
        }
    
        // Ensure sender and receiver are different customers
        if ($senderId === $receiverId) {
            // return "Sender and receiver cannot be the same customer.";
            $_SESSION['error_message'] = "Sender and receiver cannot be the same customer";
            return redirect('transfer');
            exit;
        }
        
    } 

    // get id, email, amount to transfer proccess
    public function transfer($customer_id, $email, $amount){
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $receiver_id = $row['id'];

        $this->validateSenderReceiver($customer_id, $receiver_id);
        $this->transferTransaction($customer_id, $receiver_id, $amount);
    }

    // transfer transactions
    public function transferTransaction($senderId, $receiverId, $amount){
        // Check if the sender has a sufficient balance
        $senderBalance = $this->balance(); // Implement a function to retrieve the sender's balance
        if ($senderBalance < $amount) {
            $_SESSION['error_message'] = "Invalid amount";
            return redirect('transfer');
            exit;
        }

        // Start a database transaction
        $this->db->beginTransaction();

        // Debit the sender's account
        $debitStmt = $this->db->prepare("UPDATE accounts SET balance = balance - :amount WHERE customer_id = :sender_id");
        $debitStmt->bindParam(":amount", $amount);
        $debitStmt->bindParam(":sender_id", $senderId);
        $debitResult = $debitStmt->execute();

        // Credit the receiver's account
        $creditStmt = $this->db->prepare("UPDATE accounts SET balance = balance + :amount WHERE customer_id = :receiver_id");
        $creditStmt->bindParam(":amount", $amount);
        $creditStmt->bindParam(":receiver_id", $receiverId);
        $creditResult = $creditStmt->execute();

        if ($debitResult && $creditResult) {
            // Both operations succeeded, commit the transaction
            $this->db->commit();

            // Create transaction records for sender and receiver
            $debitTransactionStmt = $this->db->prepare("INSERT INTO transactions (account_id, transaction_type, amount) VALUES (?, ?, ?)");
            $debitTransactionStmt->execute([$senderId, "Withdraw", $amount]);
            
            $creditTransactionStmt = $this->db->prepare("INSERT INTO transactions (account_id, transaction_type, amount) VALUES (?, ?, ?)");
            $creditTransactionStmt->execute([$receiverId, "Deposit", $amount]);
            
            $_SESSION['success_message'] = "Transfer Successfuull";
            return redirect('transfer');
            exit;
        } else {
            // One or both operations failed, rollback the transaction
            $this->db->rollBack();

            $_SESSION['error_message'] = "Transfer failed. Insufficient balance or other error";
            return redirect('transfer');
            exit;
        }
    }

    // get transaction data
    public function transactionData(){
        $customer_id = $_SESSION['customer_id'];

        try {
            // Prepare the SQL query with JOIN operations
            $transactionsData = $this->db->prepare("
            SELECT 
                t.transaction_type, 
                t.created_at,
                c.name AS customer_name,
                c.email AS customer_email,
                t.amount
            FROM 
                transactions AS t
            JOIN 
                accounts AS a ON t.account_id = a.id
            JOIN 
                customers AS c ON a.customer_id = c.id
            WHERE
                c.id = ?
        ");

        // Execute the query
        $transactionsData->execute([$customer_id]);

        // Fetch the result as an associative array
        return $transactionsData->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }   
    }

}