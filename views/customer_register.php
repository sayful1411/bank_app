<?php

use App\Models\DBStorage;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Method not accepted. Accepted method is POST");
    exit;
}

if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    require_once __DIR__ . "/../app/Models/DBStorage.php";
    $dbCall = new DBStorage();
    $dbCall->store($name,$email,$password);

}else{
    die("Name, Email & Password is required");
    exit;
}
?>
