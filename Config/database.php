<?php 

namespace Config;

use PDO;
use PDOException;

class Database {
    private $DBHOST;
    private $DBNAME;
    private $DBUSER;
    private $DBPASS;
    private $conn;

    public function __construct() {
        $this->DBHOST = getenv('DB_HOST') ?: 'localhost';
        $this->DBNAME = getenv('DB_NAME') ?: 'tabulation_system';
        $this->DBUSER = getenv('DB_USER') ?: 'root';
        $this->DBPASS = getenv('DB_PASS') ?: '';
    }

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->DBHOST};dbname={$this->DBNAME};charset=utf8mb4",
                    $this->DBUSER,
                    $this->DBPASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_PERSISTENT => true
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                return null;
            }
        }
        return $this->conn;
    }
}
?>
