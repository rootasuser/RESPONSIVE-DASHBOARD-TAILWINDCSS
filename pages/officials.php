<?php
require_once __DIR__ . '/../Config/database.php';

use Config\Database;

try {
    $db = new Database();
    $conn = $db->connect();
} catch (PDOException $e) {
    die("Db err: " . $e->getMessage());
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_official'])) {
        $fullname = trim($_POST['fullname']);
        $position = trim($_POST['position']);

        $checkStmt = $conn->query("SELECT COUNT(*) FROM officials_tbl");
        if ($checkStmt->fetchColumn() > 0) {
            $message = "Only one official can be added!";
            $alertClass = "alert-warning";
        } elseif (!empty($fullname) && !empty($position)) {
            $stmt = $conn->prepare("INSERT INTO officials_tbl (fullname, position) VALUES (:fullname, :position)");
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':position', $position);
            if ($stmt->execute()) {
                $message = "Official added successfully!";
                $alertClass = "alert-success";
            } else {
                $message = "Error adding official!";
                $alertClass = "alert-danger";
            }
        } else {
            $message = "All fields are required!";
            $alertClass = "alert-danger";
        }
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM officials_tbl WHERE id = :id");
        $stmt->bindParam(':id', $deleteId);
        if ($stmt->execute()) {
            $message = "Official deleted successfully!";
            $alertClass = "alert-success";
        } else {
            $message = "Error deleting official!";
            $alertClass = "alert-danger";
        }
    }
}

$officials = $conn->query("SELECT * FROM officials_tbl ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officials Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
<?php if (isset($message)): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <div id="toastMessage" class="toast align-items-center bg-gradient-success text-white <?= $alertClass; ?> border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($message); ?>
                </div>
                <button type="button" class="btn-close btn-close-dark me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script>
        setTimeout(() => {
            let toast = document.getElementById("toastMessage");
            if (toast) {
                toast.classList.remove("show");
            }
        }, 2000);
    </script>
<?php endif; ?>

        <div class="d-flex align-items-end justify-content-end">
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOfficialModal"><i class="fas fa-plus"></i> New</button>
        </div>

   
    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th style="display: none;">ID</th>
                <th>Full Name</th>
                <th>Position</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($officials as $official): ?>
                <tr>
                    <td style="display: none;"><?= htmlspecialchars($official['id']); ?></td>
                    <td><?= htmlspecialchars($official['fullname']); ?></td>
                    <td><?= htmlspecialchars($official['position']); ?></td>
                    <td><?= htmlspecialchars($official['created_at']); ?></td>
                    <td>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="delete_id" value="<?= $official['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<!-- Add Official Modal -->
<div class="modal fade" id="addOfficialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="text" id="position" name="position" class="form-control" required>
                    </div>
                    <button type="submit" name="add_official" class="btn btn-success w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>