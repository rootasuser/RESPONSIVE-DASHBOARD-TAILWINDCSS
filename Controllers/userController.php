<?php
namespace Controllers;
session_start();
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/userModel.php';

use Models\UserModel;

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function loginUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['message'] = "Invalid CSRF token!";
                $_SESSION['message_type'] = "error";
                header('Location: ../index.php');
                exit();
            }

            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

            // Ensure no special characters in username
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $_SESSION['message'] = "Invalid username! No special characters allowed.";
                $_SESSION['message_type'] = "error";
                header('Location: ../index.php');
                exit();
            }

            $user = $this->userModel->getUserByUsername($username);

            if ($user) {
                // Check if the user is active
                if ($user['status'] === 'Inactive') {
                    $_SESSION['message'] = "Your account is inactive. Please contact support.";
                    $_SESSION['message_type'] = "error";
                    header('Location: ../index.php');
                    exit();
                }

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user'] = $user;
                    $_SESSION['message'] = "Login successful!";
                    $_SESSION['message_type'] = "success";

                    if ($user['role'] === 'Admin') {
                        $_SESSION['dashboard_url'] = '../Views/admin/dashboard.php?token=' . bin2hex(random_bytes(16));
                        header('Location: ' . $_SESSION['dashboard_url']);
                    } elseif ($user['role'] === 'Judge') {
                        $_SESSION['dashboard_url'] = '../Views/dashboard.php?token=' . bin2hex(random_bytes(16));
                        header('Location: ' . $_SESSION['dashboard_url']);
                    } else {
                        header('Location: ../index.php');
                    }
                    exit();
                } else {
                    $this->userModel->logFailedAttempt($username, $password); // Log failed password
                    $_SESSION['message'] = "Incorrect password!";
                    $_SESSION['message_type'] = "error";
                    header('Location: ../index.php');
                    exit();
                }
            } else {
                $this->userModel->logFailedAttempt($username, $password); // Log non-existent user
                $_SESSION['message'] = "User not found!";
                $_SESSION['message_type'] = "error";
                header('Location: ../index.php');
                exit();
            }
        }
    }
}

$userController = new UserController();
$userController->loginUser();
?>
