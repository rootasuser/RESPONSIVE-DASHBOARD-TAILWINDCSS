<?php
session_start();
require_once __DIR__ . '/../../Config/database.php';

use Config\Database;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

session_regenerate_id(true);

$user = $_SESSION['user'];
$userId = $user['id'];
$userPasscode = $user['passcode']; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reset_scores') {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("TRUNCATE TABLE score_results");
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit();
}



$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowedPages = ['dashboard', 'criteria', 'judges', 'finalresults', 'participants', 'judgescore', 'officials', 'ranking', 'event'];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Db failed.");
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData || $userData['passcode'] !== $userPasscode) {
    header("Location: ../../index.php");
    exit();
}

$updatedUsername = isset($userData['username']) ? $userData['username'] : '';
?>



<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Windel Navales">

    <title>Dashboard</title>

    <!-- Custom styles for this template-->
    <link href="../../public/assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="../../public/assets/css/style.min.css" rel="stylesheet" type="text/css">

     <!-- Custom style for sidebar toggle-->
    <link href="../../public/assets/css/toggle-sidebar.css" rel="stylesheet" type="text/css">

    <!-- Custom Icons for this template-->
    <link href="../../public/assets/css/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!--- Google Apis Font -->
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">


</head>

<body id="page-top">

       <!-- Page Wrapper -->
       <div id="wrapper">

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: hsla(231,82%,7%,0.9);">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="?page=dashboard">
        <div class="sidebar-brand-icon rotate-n-15">
        </div>
        <div class="sidebar-brand-text mx-3"><sup>ADMIN PANEL</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="?page=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Control Panel
    </div>

   
    <li class="nav-item">
        <a class="nav-link" href="?page=criteria">
            <i class="fas fa-fw fa-list"></i>
            <span>Criteria Manager</span></a>
    </li>

 
    <li class="nav-item">
        <a class="nav-link" href="?page=judges">
            <i class="fas fa-fw fa-user-tie"></i>
            <span>Judges</span></a>
    </li>


     <li class="nav-item">
        <a class="nav-link" href="?page=participants">
            <i class="fas fa-fw fa-users"></i>
            <span>Participants</span></a>
    </li>


    <li class="nav-item">
        <a class="nav-link" href="?page=judgescore">
            <i class="fas fa-fw fa-trophy"></i>
            <span>Judge Scores</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="?page=ranking">
            <i class="fas fa-fw fa-trophy"></i>
            <span>Overall Ranking</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="?page=event">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Event Settings</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="?page=officials">
            <i class="fas fa-fw fa-user-tie"></i>
            <span>Official Settings</span></a>
    </li>

    <!-- Reset Scores Button in Navbar -->
<li class="nav-item">
    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#resetScoresModal">
        <i class="fas fa-fw fa-undo"></i>
        <span>Reset Scores</span>
    </a>
</li>

<!-- Reset Scores Confirmation Modal -->
<div class="modal fade" id="resetScoresModal" tabindex="-1" aria-labelledby="resetScoresLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetScoresLabel">Confirm Reset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to reset all scores? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReset">Confirm</button>
            </div>
        </div>
    </div>
</div>


    <li class="nav-item">
        <a class="nav-link" href="../../Controllers/logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>




    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

          
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

           
             
               

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($updatedUsername); ?></span>
                        <img class="img-profile rounded-circle"
                            src="img/undraw_profile.svg">
                    </a>
                 
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

            
                        <?php
                            include '../../pages/' . $page . '.php';

                    ?>
        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

 

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
<i class="fas fa-angle-up"></i>
</a>



<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

<script>
    function showToast(message, bgColor) {
        let toastContainer = document.getElementById("toastContainer");

        // Create container if not found
        if (!toastContainer) {
            toastContainer = document.createElement("div");
            toastContainer.id = "toastContainer";
            toastContainer.className = "position-fixed bottom-0 end-0 p-3";
            toastContainer.style.zIndex = "1050";
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        let toastEl = document.createElement("div");
        toastEl.className = `toast align-items-center text-white ${bgColor} border-0`;
        toastEl.role = "alert";
        toastEl.ariaLive = "assertive";
        toastEl.ariaAtomic = "true";

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        // Append to container
        toastContainer.appendChild(toastEl);

        // Initialize and show toast
        let toast = new bootstrap.Toast(toastEl);
        toast.show();

        // Auto remove toast after animation completes
        toastEl.addEventListener("hidden.bs.toast", function () {
            toastEl.remove();
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("confirmReset").addEventListener("click", function () {
            fetch("<?= $_SERVER['PHP_SELF'] ?>", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=reset_scores"
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    showToast("All scores have been successfully reset!", "bg-success");
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast("Error resetting scores. Please try again.", "bg-danger");
                }
            })
            .catch(error => {
                showToast("An error occurred. Please check your connection.", "bg-danger");
            });
        });
    });
</script>


    <!-- Bootstrap core JavaScript-->
    <script src="../../public/assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../public/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../public/assets/js/script.js"></script>
    <script src="../../public/assets/js/script.min.js"></script>

    <!-- Custom scripts for sidebar toggle-->
    <script src="../../public/assets/js/toggle-sidebar.js"></script>

    <!-- Page level plugins -->
    <!-- <script src="../../public/assets/vendor/chart.js/Chart.min.js"></script> -->

    <!-- Page level custom scripts -->
    <!-- <script src="../../public/assets/js/demo/chart-area-demo.js"></script> -->
    <!-- <script src="../../public/assets/js/demo/chart-pie-demo.js"></script> -->

    <!-- Page level plugins -->
    <script src="../../public/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../public/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>


</body>
</html>