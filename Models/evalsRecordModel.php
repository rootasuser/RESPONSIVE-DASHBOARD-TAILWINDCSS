<?php
require_once 'config/Database.php'; 

use Config\Database; 
try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception("Db fail.");
    }

    $stmt = $conn->prepare("SELECT category, criteria, percentage FROM evaluation_criteria");
    $stmt->execute();
    $criteriaData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($criteriaData);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
