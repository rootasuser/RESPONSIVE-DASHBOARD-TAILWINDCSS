<?php
require_once __DIR__ . '/../Config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = "CSRF token mismatch!";
            $messageType = "error";
        } else {
            if (isset($_POST['add_event'])) {
                $event_name = trim(filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_SPECIAL_CHARS));
                $event_logo = $_FILES['event_logo'] ?? null;
                $event_banner = $_FILES['event_banner'] ?? null;

                if (empty($event_name)) {
                    $message = "Invalid input values!";
                    $messageType = "error";
                } else {
                    $status = 'Active';
                    $stmt = $conn->prepare("INSERT INTO events (event_name, status, event_logo, event_banner) VALUES (:event_name, :status, :event_logo, :event_banner)");
                    $stmt->bindParam(":event_name", $event_name);
                    $stmt->bindParam(":status", $status);
                    $stmt->bindParam(":event_logo", $event_logo_path);
                    $stmt->bindParam(":event_banner", $event_banner_path);

                    $event_logo_path = null;
                    $event_banner_path = null;

                    if ($event_logo && $event_logo['error'] == UPLOAD_ERR_OK) {
                        $upload_dir = '../eventbanner/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $event_logo_path = $upload_dir . basename($event_logo['name']);
                        if (!move_uploaded_file($event_logo['tmp_name'], $event_logo_path)) {
                            $message = "Failed to upload event logo!";
                            $messageType = "error";
                        }
                    }

                    if ($event_banner && $event_banner['error'] == UPLOAD_ERR_OK) {
                        $upload_dir = '../eventbanner/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $event_banner_path = $upload_dir . basename($event_banner['name']);
                        if (!move_uploaded_file($event_banner['tmp_name'], $event_banner_path)) {
                            $message = "Failed to upload event banner!";
                            $messageType = "error";
                        }
                    }

                    if ($stmt->execute()) {
                        $message = "Added Successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Failed to add event!";
                        $messageType = "error";
                    }
                }
            }

            if (isset($_POST['delete_event_id'])) {
                $delete_event_id = filter_input(INPUT_POST, 'delete_event_id', FILTER_VALIDATE_INT);

                if (!$delete_event_id) {
                    $message = "Invalid ID!";
                    $messageType = "error";
                } else {
                    $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
                    $stmt->bindParam(":id", $delete_event_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $message = "Deleted Successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Failed to delete event!";
                        $messageType = "error";
                    }
                }
            }

            if (isset($_POST['toggle_event_id'])) {
                $toggle_event_id = filter_input(INPUT_POST, 'toggle_event_id', FILTER_VALIDATE_INT);

                if (!$toggle_event_id) {
                    $message = "Invalid ID!";
                    $messageType = "error";
                } else {
                    $stmt = $conn->prepare("SELECT status FROM events WHERE id = :id");
                    $stmt->bindParam(":id", $toggle_event_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $current_status = $stmt->fetchColumn();

                    $new_status = $current_status === 'Active' ? 'Inactive' : 'Active';
                    $action = $new_status === 'Active' ? 'enabled' : 'disabled';

                    $stmt = $conn->prepare("UPDATE events SET status = :status WHERE id = :id");
                    $stmt->bindParam(":status", $new_status);
                    $stmt->bindParam(":id", $toggle_event_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $message = "$action";
                        $messageType = "success";
                    } else {
                        $message = "Failed to update event status!";
                        $messageType = "error";
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    $message = "An error occurred: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Settings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .toast {
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .wave-text span {
            display: inline-block;
            animation: wave 1.5s infinite ease-in-out;
        }
        .wave-text span:nth-child(1) { animation-delay: 0s; }
        .wave-text span:nth-child(2) { animation-delay: 0.1s; }
        .wave-text span:nth-child(3) { animation-delay: 0.2s; }
        .wave-text span:nth-child(4) { animation-delay: 0.3s; }
        .wave-text span:nth-child(5) { animation-delay: 0.4s; }
        .wave-text span:nth-child(6) { animation-delay: 0.5s; }
        .wave-text span:nth-child(7) { animation-delay: 0.6s; }
        .wave-text span:nth-child(8) { animation-delay: 0.7s; }
        .wave-text span:nth-child(9) { animation-delay: 0.8s; }
        .wave-text span:nth-child(10) { animation-delay: 0.9s; }
        .wave-text span:nth-child(11) { animation-delay: 1s; }
        .wave-text span:nth-child(12) { animation-delay: 1.1s; }
        .wave-text span:nth-child(13) { animation-delay: 1.2s; }

        /**
            Upload File Icon
        */
        .input-group {
    display: flex;
    align-items: center;
}

.input-group-append {
    display: flex;
    align-items: center;
}

.input-group-text {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
    cursor: pointer;
}

.input-group-text i {
    font-size: 1.25rem;
    color: #6c757d;
}
    </style>
</head>
<body>

<?php if (!empty($message)): ?>
    <div class="toast-container">
        <div class="toast text-white p-3" id="toastMessage" style="background-color: hsla(228,100%,10%,0.71);">
            <strong class="wave-text">
                <?php 
                    $text = htmlspecialchars($message);
                    $letters = str_split($text);
                    foreach ($letters as $letter) {
                        echo "<span>$letter</span>";
                    }
                ?>
            </strong>
        </div>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('toastMessage').style.opacity = '0';
            setTimeout(function() {
                document.getElementById('toastMessage').remove();
            }, 500);
        }, 3000);
    </script>
<?php endif; ?>

<div class="container mt-5">
    <div class="card border-0">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: hsla(227,89%,15%,0.41);">
            <h3></h3>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addEventModal">
                <i class="fa fa-plus"></i> Add Event
            </button>
        </div>
        <div class="card-body">

            <div class="table-responsive">
            <table id="eventsTable" class="table table">
                <thead>
                    <tr>
                        <th class='d-none'>ID</th>
                        <th>Event Name</th>
                        <th>Status</th>
                        <th>Event Logo</th>
                        <th>Event Banner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                try {
                    $stmt = $conn->query("SELECT * FROM events");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $statusButtonLabel = $row['status'] === 'Active' ? 'Disable' : 'Enable';
                        $statusIcon = $row['status'] === 'Active' ? 'fa-power-off' : 'fa-check';
                        $statusBadgeClass = $row['status'] === 'Active' ? 'badge-success' : 'badge-secondary';
                        $eventLogoPath = $row['event_logo'] ? '../eventbanner/' . basename($row['event_logo']) : 'no-logo.png'; // Default image if no logo
                        $eventBannerPath = $row['event_banner'] ? '../eventbanner/' . basename($row['event_banner']) : 'no-banner.png'; // Default image if no banner
                        echo "<tr>
                                <td class='d-none'>{$row['id']}</td>
                                <td>{$row['event_name']}</td>
                                <td><span class='badge {$statusBadgeClass}'>{$row['status']}</span></td>
                                <td>
                                    <img src='{$eventLogoPath}' alt='Event Logo' style='width: 50px; height: 50px;'>
                                </td>
                                <td>
                                    <img src='{$eventBannerPath}' alt='Event Banner' style='width: 100px; height: 50px;'>
                                </td>
                                <td>
                                    <form method='POST' style='display:inline;'                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='toggle_event_id' value='{$row['id']}'>
                                        <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
                                        <button type='submit' class='btn btn-sm btn-primary'>
                                            <i class='fas {$statusIcon}'></i> {$statusButtonLabel}
                                        </button>
                                    </form>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='delete_event_id' value='{$row['id']}'>
                                        <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
                                        <button type='submit' class='btn btn-sm btn-danger'>
                                            <i class='fas fa-trash-alt'></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>";
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='6'>Error loading data</td></tr>";
                }
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addEventModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Event</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEventForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="add_event" value="1">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" class="form-control" name="event_name" required>
                    </div>
                    <div class="form-group">
                        <label>Event Logo</label>
                        <div class="input-group">
                            <input type="file" class="form-control" name="event_logo">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-upload"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Event Banner</label>
                        <div class="input-group">
                            <input type="file" class="form-control" name="event_banner">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-upload"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Move these scripts to BOTTOM of body before closing tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        $('#eventsTable').DataTable({
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "zeroRecords": "Nothing found - sorry",
                "info": "Showing page _PAGE_ of _PAGES_",
                "infoEmpty": "No records available",
                "infoFiltered": "(filtered from _MAX_ total records)"
            }
        });
    });
</script>

</body>
</html>