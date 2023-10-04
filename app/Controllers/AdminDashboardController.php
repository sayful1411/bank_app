<?php 

namespace App\Controllers;

use App\Controllers\CustomerController;
use App\Models\Storage;
use App\Controllers\CustomerDashboardController;

class AdminDashboardController{
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

    public function __construct(Storage $storage,$admin)
    {
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
}