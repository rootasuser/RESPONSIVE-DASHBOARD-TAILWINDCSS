<?php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/evaluationModel.php';

use Models\EvaluationModel;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        exit(); 
    }

    $category = isset($_POST['category']) ? trim($_POST['category']) : null;
    $criteria = isset($_POST['criteria']) ? trim($_POST['criteria']) : null;
    $percentage = isset($_POST['percentage']) ? filter_var(trim($_POST['percentage']), FILTER_VALIDATE_FLOAT) : false;


    if (empty($category) || empty($criteria) || $percentage === false) {
        $_SESSION['error'] = "All fields are required and percentage must be a valid number!";
        exit();
    }

    if ($percentage <= 0 || $percentage > 100) {
        $_SESSION['error'] = "Percentage must be between 1 and 100.";
        exit();
    }

    if (strcasecmp($category, $criteria) === 0) {
        $_SESSION['error'] = "Category and Criteria cannot be the same.";
        exit();
    }

    $evaluationModel = new EvaluationModel();
    $result = $evaluationModel->addEvaluationCriteria($category, $criteria, $percentage);

    if ($result === true) {
        $_SESSION['success'] = "Successfully Added $category - $criteria - $percentage%";
    } else {
        $_SESSION['error'] = "Err: " . htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    }
}
?>
