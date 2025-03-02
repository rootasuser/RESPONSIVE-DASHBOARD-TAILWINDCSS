<?php
namespace Models;

require_once __DIR__ . '/../Config/database.php';

use Config\Database;
use PDO;

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Validate username format (only letters and numbers allowed)
     */
    private function isValidUsername($username) {
        return preg_match('/^[a-zA-Z0-9]+$/', $username);
    }

    /**
     * Fetch user by username
     */
    public function getUserByUsername($username) {
        if (!$this->isValidUsername($username)) {
            return null; // Invalid username format
        }

        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Admin Passcode
     */
    public function getAdminPasscode($username) {
        if (!$this->isValidUsername($username)) {
            return null;
        }

        $sql = "SELECT passcode FROM users WHERE username = :username AND role = 'Admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['passcode'] : null;
    }

    /**
     * Check if user is active
     */
    public function isUserActive($username) {
        if (!$this->isValidUsername($username)) {
            return false;
        }

        $sql = "SELECT status FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $status = $stmt->fetchColumn();
        return $status === 'Active';
    }

    /**
     * Log failed login attempts
     */
    public function logFailedAttempt($username, $password) {
        $sql = "INSERT INTO admin_logs (username, attempted_password) VALUES (:username, :password)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        return $stmt->execute();
    }
    
    /**
     * Attempt user login
     */
    public function loginUser($username, $password) {
        if (!$this->isValidUsername($username)) {
            $this->logFailedAttempt($username, $password);
            return false;
        }

        $user = $this->getUserByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->logFailedAttempt($username, $password);
            return false;
        }

        // Check if the user is active
        if ($user['status'] === 'Inactive') {
            return false; // User is inactive, deny login
        }

        return $user; // Successful login
    }
}

?>
