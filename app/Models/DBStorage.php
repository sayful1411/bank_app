<?php 

namespace App\Models;

use PDO;
class DBStorage extends Model{
    public function store($name,$email,$password){
        // Check if the email already exists in the database
        if ($this->customerEmailExists($email)) {
            // Email already exists, set an error message
            session_start();
            $_SESSION['error_message'] = 'Email already exists';
            return redirect('register');
        }

        // Email is unique, proceed with insertion
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
        $result =  $stmt->execute([$name, $email, $password]);
        
        if ($result) {
            session_start();
            $_SESSION['success_message'] = 'Registration was successful! You can now log in.';
            return redirect('login');
            exit;
        } else {
            // Handle other errors if needed
            return redirect('login?error=registration_failed');
            exit;
        }
    }

    public function customerEmailExists($email){
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
        $stmt->execute([$email]);

        return $stmt->fetchColumn() > 0;
    }

    public function  login($email,$password){

    }
}