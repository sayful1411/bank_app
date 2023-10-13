<?php 

namespace App\Controllers;

use App\Controllers\CustomerController;
use App\Models\Storage;
use App\Controllers\CustomerDashboardController;
use App\Models\AdminModel;
use App\Traits\AvatarGeneratorTrait;

class AdminDashboardController{

    /**
     * CLI Part
     */

    private $admin;
    private Storage $storage;

    private const ALL_TRANSACTIONS = 1;
    private const TRANSACTIONS_BY_CUSTOMER = 2;
    private const ALL_CUSTOMERS = 3;
    private const LOGOUT = 4;

    private array $options = [
        self::ALL_TRANSACTIONS => 'All Transactions',
        self::TRANSACTIONS_BY_CUSTOMER => 'Transactions by Customer',
        self::ALL_CUSTOMERS => 'All Customers',
        self::LOGOUT => 'Log out',
    ];

    public function __construct(Storage $storage,$admin){
        $this->storage = $storage;
        $this->admin = $admin;
    }

    public function run(){
        while(true){
            foreach($this->options as $option => $label){
                printf("%d. %s\n", $option, $label);
            }
    
            $choice = intval(readline("Enter your choice: "));
    
            switch($choice){
                case self::ALL_TRANSACTIONS:
                    $this->showAllTransactions();
                    break;
    
                case self::TRANSACTIONS_BY_CUSTOMER:
                    $this->showTransactionsByCustomer();
                    break;
    
                case self::ALL_CUSTOMERS:
                    $this->showAllCustomers();
                    break;
    
                case self::LOGOUT:
                    echo "Logging out...\n";
                    return;
    
                default:
                    echo "Invalid option.\n";
            }
        }
    }

    public function showAllTransactions(){
        // Load all transactions from the customer transaction file
        $transactionHistory = $this->storage->load(CustomerDashboardController::getModelName());

        if (empty($transactionHistory)) {
            echo "No transactions found.\n";
        } else {
            echo "Transaction History:\n";
            foreach ($transactionHistory as $transaction) {
                printf("Date: %s, Type: %s, Amount: %s taka\n", $transaction['date'], $transaction['type'], $transaction['amount']);
            }
        }
    }

    public function showTransactionsByCustomer(){
        $customerEmail = readline("Enter the customer email address: ");

        // Load existing customer data
        $transactionHistory = $this->storage->load(CustomerDashboardController::getModelName());

        echo "Transaction History:\n";
        foreach($transactionHistory as $transaction){
            if($transaction['customer'][1] === $customerEmail){
                printf("Date: %s, Type: %s, Amount: %s taka\n", $transaction['date'], $transaction['type'], $transaction['amount']);
            }
        }
        
    }

    public function showAllCustomers(){
        // Load all customers
        $customers = $this->storage->load(CustomerController::getModelName());

        if (empty($customers)) {
            echo "No customer found.\n";
        } else {
            echo "All Customers:\n";
            foreach ($customers as $customer) {
                printf("Name: %s, Email: %s, Balance: %s taka\n", $customer['name'], $customer['email'], $customer['balance']);
            }
        }
    }

    /**
     * Web Part
     */

    public static function adminDashboard(){
        return view('admin/dashboard');
    }

    public static function addCustomer(){
        return view('admin/add-customer');
    }

    // get specific customer transaction
    public static function customerTransaction(){

        if (!isset($_GET['id'])) {
            echo "Invalid Id";
            return;
        }

        // get customer id from url
        $customerID = $_GET['id'];

        $dbCall = new AdminModel();

        // Retrieve the customer's name before the loop
        $customer = $dbCall->getCustomerById($customerID);

        if (!$customer) {
            echo "Customer not found.";
            return;
        }

        $customerName = $customer['name'];

        // Fetch the transactions for the customer
        $transactions = $dbCall->transactionByCustomer($customerID);

        return view('admin/customer-transaction', [
            'customerName' => $customerName,
            'transactions' => $transactions,
        ]);
    }

    // get all customer
    public static function getCustomerData(){
        $dbCall = new AdminModel();
        $customers = $dbCall->customerData();

        $customerData = [];
        foreach ($customers as $customer) {
            $id = $customer['id'];
            $name = $customer['name'];
            $email = $customer['email'];
            $avatar = $dbCall->generateAvatar($name);

            // Associate the customer's name with their avatar
            $customerData[] = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
            ];
        }

        return view('admin/customers',['customerData'=>$customerData]);
    }

    // get all transaction
    public static function getAllTransaction(){
        $dbCall = new AdminModel();
        $transactions = $dbCall->transactionData();

        return view('admin/transactions', ['transactions' => $transactions]);
    }

    //  registar customer
    public static function customerRegisterByAdmin(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }
        
        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
            $dbCall = new AdminModel();
            $dbCall->customerDataStore($name,$email,$password);
        
        }else{
            die("Name, Email & Password is required");
            exit;
        }
    }

}