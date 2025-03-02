<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/tabulation/Config/database.php';

use Config\Database;

header('Content-Type: application/json');  // Ensure the response is in JSON format

$response = ['success' => false, 'message' => '']; // Default response structure

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Validate the ID
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id === false) {
        $response['message'] = 'Invalid ID.';
    } else {
        try {
            $db = new Database();
            $conn = $db->connect();

            $sql = "DELETE FROM evaluation_criteria WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Deleted successfully.';
            } else {
                $response['message'] = 'Error deleting record.';
            }

            $conn = null;
        } catch (PDOException $e) {
            // Handle database connection errors
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);  // Return the response as JSON
exit;  // Stop further execution
?>
