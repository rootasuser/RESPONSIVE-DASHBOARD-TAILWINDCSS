<?php
session_start();  // Start session to modify it
session_unset();  // Unset all session variables
session_destroy(); // Destroy the session completely

// Ensure no session message persists
session_start();
$_SESSION = []; 

header("Location: ../index.php");
exit();
?>
