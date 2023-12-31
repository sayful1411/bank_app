<?php 

namespace App\Controllers;

use App\Models\Storage;
use App\Models\CustomerModel;
use App\Controllers\AdminController;
use App\Controllers\AdminDashboardController;
use App\Controllers\CustomerDashboardController;

class CustomerController{
    /**
     * CLI Part
     */

    protected array $customerInfo = [];
    protected Storage $storage;
    protected float $balance;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->balance = 0.0;
    }

    public function getFormattedPrice(){
        return number_format($this->balance,2);
    }

    public static function getModelName(): string
    {
        return 'customer';
    }

    public function register(string $name, string $email, int $password)
    {
        // Load existing customer data
        $existingCustomers = $this->storage->load(CustomerController::getModelName());

        // Check if the email already exists
        foreach ($existingCustomers as $customer) {
            if ($customer['email'] === $email) {
                echo "Email already exists. Registration declined.\n";
                return; // Exit the registration process
            }
        }

        // Add the new customer to the array
        $info = [
            'name' => $name, 
            'email' => $email, 
            'password' => $password,
            'balance' => $this->getFormattedPrice(),
        ];
        $this->customerInfo[] = $info;

        // Save customer data
        $this->saveCustomer();

        printf("Customer registration successfully!\n");

    }

    public function login(string $email, int $password)
    {
        // Load existing customer and admin data
        $existingCustomers = $this->storage->load(CustomerController::getModelName());

        $existingAdmins = $this->storage->load(AdminController::getModelName());

        // Check if it's an admin login
        foreach ($existingAdmins as $admin) {
            if ($admin['email'] === $email && $admin['password'] === $password) {
                echo "Welcome to admin dashboard!\n";
                $adminDashboard = new AdminDashboardController($this->storage, $admin);
                $adminDashboard->run();
                return;  // Exit the login process
            }
        }

        // Assume no matching customer is found initially
        $matchingCustomer = null;

        foreach ($existingCustomers as $customer) {
            if ($customer['email'] === $email && $customer['password'] === $password) {
                $matchingCustomer = $customer;
                break;  // Exit the loop once a matching customer is found
            }
        }

        // Check if it's a customer
        if ($matchingCustomer !== null) {
            echo "Welcome to customer dashboard!\n";
            $dashboard = new CustomerDashboardController($this->storage, $matchingCustomer);
            $dashboard->run();
        } else {
            echo "Incorrect login credentials.\n";
        }

    }

    protected function saveCustomer(): void
    {
        $this->storage->save(CustomerController::getModelName(), $this->customerInfo);
    }

    /**
     * Web Part
     */

    //  login page
     public static function loginPage(){
        return view('login');
    }

    //  registration page
    public static function registerPage(){
        return view('register');
    }

    //  logout page
    public static function logoutPage(){
        return view('logout');
    }

    // static property and method to store the CustomerModel instance
    private static $dbCallInstance;

    private static function getDBCallInstance(){
        if (!isset(self::$dbCallInstance)) {
            self::$dbCallInstance = new CustomerModel();
        }
        return self::$dbCallInstance;
    }

    //  get balance
    public static function getBalance(){
        $dbCall = self::getDBCallInstance();
        return $dbCall->balance();
    }

    // get transaction data
    public static function getTransactionData(){
        $dbCall = self::getDBCallInstance();
        return $dbCall->transactionData();
    }

    //  registar customer
    public static function customerRegister(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
            $dbCall = new CustomerModel();
            $dbCall->store($name,$email,$password);
        
        }else{
            die("Name, Email & Password is required");
            exit;
        }
    }

    // login customer
    public static function customerLogin(){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['email']) && isset($_POST['password'])){
                $email = htmlspecialchars($_POST['email']);
                $password = $_POST['password'];

                try{
                    $dbCall = new CustomerModel();
                    $dbCall->login($email,$password);

                }catch(\PDOException $e){
                    die("Databse error: {$e->getMessage()}");
                }
            }
        }
    }

    public static function test(){
        return view('test');
    }

}