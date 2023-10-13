<?php 

namespace App\Controllers;

use DateTime;
use App\Models\Storage;
use App\Controllers\CustomerController;

class CustomerDashboardController extends CustomerController
{
    /**
     * CLI Part
     */

    private $customer;

    private const SHOW_TRANSACTIONS = 1;
    private const DEPOSIT_MONEY = 2;
    private const WITHDRAW_MONEY = 3;
    private const SHOW_BALANCE = 4;
    private const TRANSFER_MONEY = 5;
    private const LOGOUT = 6;

    private array $options = [
        self::SHOW_TRANSACTIONS => 'Show transactions',
        self::DEPOSIT_MONEY => 'Deposit money',
        self::WITHDRAW_MONEY => 'Withdraw money',
        self::SHOW_BALANCE => 'Show balance',
        self::TRANSFER_MONEY => 'Transfer money',
        self::LOGOUT => 'Logout',
    ];

    public function __construct(Storage $storage,$customer)
    {
        $this->storage = $storage;
        $this->customer = $customer;
        $this->balance = 0.0;
    }

    public static function getModelName(): string
    {
        return 'customer-transaction';
    }

    public function run()
    {
        while(true){
            foreach ($this->options as $option => $label) {
                printf("%d. %s\n", $option, $label);
            }

            $choice = intval(readline("Enter your choice: "));

            switch($choice){
                case self::SHOW_TRANSACTIONS:
                    $this->showTransactions();
                    break;

                case self::DEPOSIT_MONEY:
                    $this->depositMoney();
                    break;
                
                case self::WITHDRAW_MONEY:
                    $this->withdrawMoney();
                    break;

                case self::SHOW_BALANCE:
                    $this->showBalance();
                    break;

                case self::TRANSFER_MONEY:
                    $this->transferMoney();
                    break;

                case self::LOGOUT:
                    echo "Logging out...\n";
                    return;
                    
                default:
                    echo "Invalid option.\n";
            }
        }
    }

    public function showTransactions(){
        $customerEmail = $this->customer['email'];

        // Load all transactions from the customer transaction file
        $transactionHistory = $this->storage->load(CustomerDashboardController::getModelName());

        if (empty($transactionHistory)) {
            echo "No transactions found.\n";
        } else {
            echo "Transaction History:\n";
            foreach ($transactionHistory as $transaction) {
                // Check if the transaction belongs to the logged-in customer
                if ($transaction['customer'][1] === $customerEmail) {
                    printf("Date: %s, Type: %s, Amount: %s taka\n", $transaction['date'], $transaction['type'], $transaction['amount']);
                }
            }
        }
    }

    public function depositMoney()
    {
        $amount = floatval(readline("Enter the deposit amount: "));

        if ($amount <= 0) {
            echo "Invalid amount. Please enter a positive value.\n";
            return;
        }

        // Load existing customer data
        $existingCustomers = $this->storage->load(CustomerController::getModelName());

        // Find the customer to update by email
        foreach ($existingCustomers as &$customer) {
            if ($customer['email'] === $this->customer['email']) {
                $customer['balance'] += $amount; // Update the balance for the specific customer
                break;
            }
        }

        // Serialize the updated customer data and save it back to the file
        $this->storage->save(CustomerController::getModelName(), $existingCustomers);

        // Load existing customer transaction data
        $existingTransactions = $this->storage->load(CustomerDashboardController::getModelName());

        // Add the customer transaction
        $currentDateTime = new DateTime('now'); 
        $currentDate = $currentDateTime->format('Y-m-d');

        $transaction = [
            'date' => $currentDate, 
            'customer' => [$this->customer['name'],$this->customer['email']], 
            'type' => "deposit",
            'amount' => $amount, 
        ];

        $existingTransactions[] = $transaction;

        // Save the updated customer transaction data
        $this->saveCustomerTransaction($existingTransactions);

        echo "Deposit of $amount taka successful.\n";
    }

    public function withdrawMoney()
    {
        $amount = floatval(readline("Enter the withdrawal amount: "));

        if ($amount <= 0) {
            echo "Invalid amount. Please enter a positive value.\n";
            return;
        }
    
        $customerEmail = $this->customer['email'];
    
        // Load existing customer data
        $existingCustomers = $this->storage->load(CustomerController::getModelName());
    
        // Find the customer to update by email
        foreach ($existingCustomers as &$customer) {
            if ($customer['email'] === $customerEmail) {
                // Check if the customer has sufficient balance for the withdrawal
                if ($customer['balance'] >= $amount) {
                    $customer['balance'] -= $amount; // Update the balance for the specific customer
                    break;
                } else {
                    echo "Insufficient balance for withdrawal.\n";
                    return;
                }
            }
        }
    
        // Serialize the updated customer data and save it back to the file
        $this->storage->save(CustomerController::getModelName(), $existingCustomers);

        // Load existing customer transaction data
        $existingTransactions = $this->storage->load(CustomerDashboardController::getModelName());
    
        // Add the customer transaction for withdrawal
        $currentDateTime = new DateTime('now'); 
        $currentDate = $currentDateTime->format('Y-m-d');
    
        $transaction = [
            'date' => $currentDate, 
            'customer' => [$this->customer['name'], $customerEmail], 
            'type' => "withdrawal",
            'amount' => $amount, 
        ];

        $existingTransactions[] = $transaction;
    
        $this->saveCustomerTransaction($existingTransactions);
    
        echo "Withdrawal of $amount taka successful.\n";
    }

    public function showBalance()
    {
        $customerEmail = $this->customer['email'];

        // Load all transactions from the customer transaction file
        $customerHistory = $this->storage->load(CustomerController::getModelName());

        if (empty($customerHistory)) {
            echo "Empty.\n";
        } else {
            foreach ($customerHistory as $customer) {
                // Check if the transaction belongs to the logged-in customer
                if ($customer['email'] === $customerEmail) {
                    printf("Balance: %s taka\n",  $customer['balance']);
                }
            }
        }
    }

    public function transferMoney()
    {
        $recipientEmail = readline("Enter the recipient's email address: ");
        $transferAmount = floatval(readline("Enter the transfer amount: "));
    
        if ($transferAmount <= 0) {
            echo "Invalid amount. Please enter a positive value.\n";
            return;
        }
    
        // Load existing customer data
        $customers = $this->storage->load(CustomerController::getModelName());
    
        // Find the sender and recipient by email
        $senderEmail = $this->customer['email'];
        $sender = null;
        $recipient = null;
    
        foreach ($customers as &$customer) {
            if ($customer['email'] === $senderEmail) {
                $sender = &$customer;
            } elseif ($customer['email'] === $recipientEmail) {
                $recipient = &$customer;
            }
    
            if ($sender && $recipient) {
                break;  // Exit the loop once both sender and recipient are found
            }
        }
    
        if (!$sender) {
            echo "Sender not found.\n";
            return;
        }
    
        if (!$recipient) {
            echo "Recipient not found.\n";
            return;
        }
    
        // Check if the sender has sufficient balance
        if ($sender['balance'] >= $transferAmount) {
            // Deduct the transfer amount from the sender's balance
            $sender['balance'] -= $transferAmount;
            
            // Add the transfer amount to the recipient's balance
            $recipient['balance'] += $transferAmount;
    
            // Load existing customer transaction data
            $existingTransactions = $this->storage->load(CustomerDashboardController::getModelName());
        
            // Add the customer transaction for withdrawal
            $currentDateTime = new DateTime('now'); 
            $currentDate = $currentDateTime->format('Y-m-d');
        
            $senderTransaction = [
                'date' => $currentDate, 
                'customer' => [$this->customer['name'], $senderEmail], 
                'type' => "withdrawal",
                'amount' => $transferAmount, 
            ];

            $recipientTransaction = [
                'date' => $currentDate, 
                'customer' => [$this->customer['name'], $recipientEmail], 
                'type' => "deposit",
                'amount' => $transferAmount, 
            ];

            $existingTransactions[] = $senderTransaction;
            $existingTransactions[] = $recipientTransaction;
        
            $this->saveCustomerTransaction($existingTransactions);
    
            // Save the updated customer data
            $this->storage->save(CustomerController::getModelName(), $customers);
    
            echo "Transfer of $transferAmount taka to $recipientEmail successful.\n";
        } else {
            echo "Insufficient balance for the transfer.\n";
        }
    }

    public function saveCustomerTransaction(array $transactionData): void
    {
        $this->storage->save(CustomerDashboardController::getModelName(), $transactionData);
    }

    /**
     * Web Part
     */
    // dashboard page
     public static function dashboard(){
        $balance = static::getBalance(); // get current balance
        $data = static::getTransactionData();

        return view('customer/dashboard', ['balance'=>$balance, 'data'=>$data]);
        
    }
}