<?php
namespace Models;

use PDO;
use PDOException;
use Config\Database;

class EvaluationModel {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addEvaluationCriteria(string $category, string $criteria, float $percentage): bool|string {
        try {
 
            if (strcasecmp($category, $criteria) === 0) {
                return "Category and criteria cannot be the same.";
            }

            $checkStmt = $this->conn->prepare("
                SELECT COUNT(*) FROM evaluation_criteria WHERE category = :category AND criteria = :criteria
            ");
            $checkStmt->bindParam(':category', $category, PDO::PARAM_STR);
            $checkStmt->bindParam(':criteria', $criteria, PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                return "Category and criteria already exist.";
            }

            $stmt = $this->conn->prepare("
                INSERT INTO evaluation_criteria (category, `criteria`, percentage) 
                VALUES (:category, :criteria, :percentage)
            ");
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':criteria', $criteria, PDO::PARAM_STR);
            $stmt->bindParam(':percentage', $percentage, PDO::PARAM_STR); 

            return $stmt->execute() ? true : "Failed to insert record.";
        } catch (PDOException $e) {
            error_log("DB Err: " . $e->getMessage());
            return "DB Err.";
        }
    }
}
?>
