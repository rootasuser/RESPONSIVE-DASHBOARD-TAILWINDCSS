<?php
require_once __DIR__ . '/../Config/database.php';

use Config\Database;

try {
    $db = new Database();
    $conn = $db->connect();
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}


// Fetch active event details
$queryEvent = "SELECT event_name, event_logo, event_banner FROM events WHERE status = 'Active' LIMIT 1";
$eventData = $conn->query($queryEvent)->fetch(PDO::FETCH_ASSOC);

// Fetch barangay captain's name
$queryCaptain = "SELECT fullname FROM officials_tbl WHERE position = 'Barangay Captain' ORDER BY created_at DESC LIMIT 1";
$captainData = $conn->query($queryCaptain)->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($eventData['event_name'] ?? 'Event Leaderboard'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { text-align: center; font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        .header { position: relative; padding: 20px; text-align: center; }
        .event-logo { height: 100px; position: absolute; left: 30px; top: 120px; transform: translateY(-50%); border-radius: 50%; }
        .event-banner { width: 100%; height: 200px; object-fit: cover; margin-bottom: 20px; }
        .table th { background-color: #343a40; color: white; }
        .footer { margin-top: 30px; font-weight: bold; }
        .print-button { margin: 20px 0; }

        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            .container, .container * { visibility: visible; }
            .container { position: absolute; left: 0; top: 0; width: 100%; }
            .print-button { display: none; }

            /* Ensure logo and header keep their styles */
            .header { position: relative !important; }
            .event-logo {
                display: block !important;
                position: absolute !important;
                left: 30px !important;
                top: 120px;
                transform: translateY(-50%) !important;
                height: 100px !important;
                border-radius: 50% !important;
            }
            .event-banner {
                display: block !important;
                width: 100% !important;
                height: 200px !important;
                object-fit: cover !important;
            }
        }
    </style>
    <script>
        function printContent() {
            window.print();
        }
    </script>
</head>
<body>

<div class="container">
    <div class="header">
        <?php if (!empty($eventData['event_banner'])): ?>
            <img src="<?php echo htmlspecialchars($eventData['event_banner']); ?>" alt="Event Banner" class="event-banner">
        <?php endif; ?>
        
        <?php if (!empty($eventData['event_logo'])): ?>
            <img src="<?php echo htmlspecialchars($eventData['event_logo']); ?>" alt="Event Logo" class="event-logo">
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($eventData['event_name'] ?? 'Masskara Festival'); ?></h1>
    </div>

    <div class="print-button">
        <button type="button" class="btn btn-success" onclick="printContent()"><i class="fas fa-print"></i> Print</button>
    </div>

    <!-- RANKING TABLE (Keep existing ranking logic) -->
    <?php include 'ranking_table.php'; ?>

    <div class="footer">
        <p><strong><?php echo htmlspecialchars($captainData['fullname'] ?? 'Juan Dela Cruz'); ?></strong><br>Barangay Captain</p>
    </div>
</div>

</body>
</html>
