<?php
require_once '../config/Database.php';
require_once '../Models/evaluationModel.php';

use Models\EvaluationModel;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = $_POST['category'] ?? '';
    $criteria = $_POST['criteria'] ?? '';
    $percentage = $_POST['percentage'] ?? '';

    if (empty($category) || empty($criteria) || empty($percentage)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $evaluationModel = new EvaluationModel();
    $result = $evaluationModel->addEvaluationCriteria($category, $criteria, (float)$percentage);

    if ($result === true) {
        echo json_encode(["success" => true, "message" => "Evaluation criteria added successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => $result]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
