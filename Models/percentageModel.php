<?php

require_once '../config/Database.php';

use Config\Database;

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "DB Failed."]);
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); 

    try {
        $sql = "SELECT category FROM evaluation_criteria WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $category = $row['category'];

            $sql = "SELECT id, category, criteria, percentage AS total_percentage 
                    FROM evaluation_criteria 
                    WHERE category = :category";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":category", $category, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No matching row found for this ID."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Database Query Error: " . $e->getMessage());
        echo json_encode(["error" => "Q Failed."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid ID"]);
}
?>
