<?php 

namespace App\Models;

use App\Traits\AvatarGeneratorTrait;
use PDO;
use PDOException;

class AdminModel extends Model{

    use AvatarGeneratorTrait;

    // new admin record
    public function store($name, $email, $password){
        try{
            // Check if the email already exists in the database
            if($this->isAdminEmailRegistered($email)){
                $_SESSION['error_message'] = 'Email already exists';
                return redirect('admin/register');
            }

            // Check if customer and admin email is same
            if($this->isCustomerEmailRegistered($email)){
                $_SESSION['error_message'] = 'The email is already registered as a customer';
                return redirect('admin/register');
            }

            // Insert Customer Information
            $insertAdminData  = $this->db->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
            $adminInsertResult =  $insertAdminData ->execute([$name, $email, $password]);

            if (!$adminInsertResult) {
                throw new PDOException('Failed to insert admin data');
            }

            $_SESSION['success_message'] = 'Registration was successful! You can now log in.';
            return redirect('admin/login');

        }catch(PDOException $e){
            return redirect('register?error=' . $e->getMessage());
        }
    }

    // new customer record
    public function customerDataStore($name,$email,$password){
        try{
            // Check if the email already exists in the database
            if($this->isAdminEmailRegistered($email)){
                $_SESSION['error_message'] = 'The email is already registered as an Admin';
                return redirect('admin/add-customer');
            }

            // Check if customer and admin email is same
            if($this->isCustomerEmailRegistered($email)){
                $_SESSION['error_message'] = 'Email already exists';
                return redirect('admin/add-customer');
            }

            // Insert Customer Information
            $insertCustomerStatement  = $this->db->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            $customerInsertResult =  $insertCustomerStatement ->execute([$name, $email, $password]);

            if (!$customerInsertResult) {
                throw new PDOException('Failed to insert customer data');
            }

            // Get the ID of the newly inserted customer
            $customer_id = $this->db->lastInsertId();

            // Insert Account balance for specific customer
            $insertAccountStatement = $this->db->prepare("INSERT INTO accounts (customer_id, balance) VALUES (?, 0)");
            $accountInsertResult  = $insertAccountStatement->execute([$customer_id]);

            if (!$accountInsertResult) {
                throw new PDOException('Failed to create customer account');
            }

            $_SESSION['success_message'] = 'Successfully added a customer';
            return redirect('admin/add-customer');
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
    
    // admin login
    public function  login($email,$password){
        $getAdminEmail = $this->db->prepare("SELECT * FROM admins WHERE email = ?");
        $getAdminEmail->execute([$email]);

        $admin = $getAdminEmail->fetch(PDO::FETCH_ASSOC);

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            // generate avatar
            $name = $_SESSION['admin_name'];
            $_SESSION['avatar_name'] = $this->generateAvatar($name);

            return redirect('admin/dashboard');
            exit;
        }else{
            $_SESSION['error_message'] = 'Sorry, wrong credentials';
            return redirect('admin/login');
            exit;
        }
    }

    // fetch customer data
    public function customerData(){
        
        try {
            // Prepare the SQL query with JOIN operations
            $customerData = $this->db->prepare("SELECT id,name,email FROM customers");

            // Execute the query
            $customerData->execute();

            // Fetch the result as an associative array
            return $customerData->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    // fetch transaction data
    public function transactionData(){
        try {
            // Prepare the SQL query with JOIN operations
            $transactionData = $this->db->prepare("
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
        ");

        // Execute the query
        $transactionData->execute();

        // Fetch the result as an associative array
        return $transactionData->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        } 
    }

    // fetch specific customer transaction data
    public function transactionByCustomer($customer_id){

        try {
            // Prepare the SQL query with JOIN operations
            $customerTransactions = $this->db->prepare("
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
        $customerTransactions->execute([$customer_id]);

        // Fetch the result as an associative array
        return $customerTransactions->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        } 
    }

    // get customer by id
    public function getCustomerById($customer_id) {
        try {
            $customer = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
            $customer->execute([$customer_id]);
            return $customer->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

}