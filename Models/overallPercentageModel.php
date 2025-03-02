<?php
require_once '../config/Database.php';

use Config\Database;

header('Content-Type: application/json'); 

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "SELECT COALESCE(SUM(percentage), 0) AS total_percentage FROM evaluation_criteria";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total = number_format((float)$row['total_percentage'], 2); 
    echo json_encode(['total_percentage' => $total]);
} else {
    echo json_encode(["error" => "Query execution failed"]);
}

$conn->close();
?>
