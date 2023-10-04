<?php 

namespace App\Controllers;

use App\Models\Storage;
use App\Controllers\CustomerDashboardController;
use App\Controllers\AdminController;
use App\Controllers\AdminDashboardController;

class CustomerController{
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

    /**
     * Pages
     */

    public static function loginPage(){
        return view('login');
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

}