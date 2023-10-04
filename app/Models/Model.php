<?php 

namespace App\Models;

use PDO;

class Model{
    protected PDO $db;
    
    public function __construct() {

        $config = require_once __DIR__ . "/../../config/database.php";
        
        try{
            $this->db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",$config['username'],$config['password']);

            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // echo "Connected successfully";

        }catch(\PDOException $e){
            echo "Connection failed: " . $e->getMessage();
        }
    }

}