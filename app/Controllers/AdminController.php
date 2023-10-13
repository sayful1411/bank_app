<?php 

namespace App\Controllers;

use App\Models\Storage;
use App\Models\AdminModel;
use App\Controllers\BankCLIAppController;

class AdminController extends BankCLIAppController implements ModelController{

    /**
     * CLI Part
     */

    protected array $adminInfo = [];
    protected Storage $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public static function getModelName(): string
    {
        return 'admin';
    }
    
    public function run(){
        // Load existing admin data
        $existingAdmin = $this->storage->load(AdminController::getModelName());

        $email = trim(readline("Enter your email: "));
        $password = (int)trim(readline("Enter your password: "));

        // Check if the email already exists
        foreach ($existingAdmin as $admin) {
            if ($admin['email'] === $email) {
                echo "Email already exists.\n";
                return; // Exit the registration process
            }
        }

        // Add the new customer to the array
        $info = [
            'email' => $email, 
            'password' => $password,
        ];
        $this->adminInfo[] = $info;

        // Save admin data
        $this->saveAdmin();

        printf("Admin created successfully!\n");

    }

    private function saveAdmin(): void
    {
        $this->storage->save(AdminController::getModelName(), $this->adminInfo);
    }

    /**
     * Web part
     */

    //  login page
    public static function adminLoginPage(){
        return view('admin/login');
    }

    //  registration page
    public static function adminRegisterPage(){
        return view('admin/register');
    }

    //  logout page
    public static function adminLogoutPage(){
        return view('logout');
    }

    // admin register
    public static function adminRegister(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Method not accepted. Accepted method is POST");
            exit;
        }

        if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
            $dbCall = new AdminModel();
            $dbCall->store($name, $email, $password);
        
        }else{
            die("Name, Email & Password is required");
            exit;
        }

    }

    // admin login
    public static function adminLogin(){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['email']) && isset($_POST['password'])){
                $email = htmlspecialchars($_POST['email']);
                $password = $_POST['password'];

                try{
                    $dbCall = new AdminModel();
                    $dbCall->login($email,$password);

                }catch(\PDOException $e){
                    die("Databse error: {$e->getMessage()}");
                }
            }
        }
    }

}