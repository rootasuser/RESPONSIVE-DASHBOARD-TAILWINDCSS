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

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowedPages = ['dashboard', 'eventsetup', 'addjudge', 'finalresults', 'participants', 'eventsettings', 'adminsettings', 'backup'];

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="../../public/assets/fontawesomev4/css/font-awesome.min.css">
    <script src="../../public/assets/alphine/dist/cdn.min.js" defer></script>
    
</head>
<body class="h-screen flex">

  <!-- Sidebar -->
<div class="flex min-h-screen">
  <aside class="bg-gray-900 text-white w-64 py-7 px-4 hidden md:block h-auto">
    <!-- Logo / Title -->
    <h2 class="text-2xl font-semibold text-center mb-4">Tabulation System</h2>

    <!-- Navigation -->
    <nav class="space-y-2">
      <a href="?page=dashboard" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex items-center">
        <i class="fa fa-home mr-2"></i> Dashboard
      </a>

      <!-- Event Management -->
      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
          <span><i class="fa fa-calendar mr-2"></i> Event Management</span>
          <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-show="open" class="pl-6 mt-1">
          <a href="?page=eventsetup" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Event Setup</a>
        </div>
      </div>

      <!-- Participant Management -->
      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
          <span><i class="fa fa-users mr-2"></i> Participant Management</span>
          <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-show="open" class="pl-6 mt-1">
          <a href="?page=participants" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Participants</a>
        </div>
      </div>

      <!-- Leaderboard Management -->
      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
          <span><i class="fa fa-bar-chart mr-2"></i> Manage Leaderboard</span>
          <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-show="open" class="pl-6 mt-1">
          <a href="?page=finalresults" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Final Results</a>
        </div>
      </div>

      <!-- System Settings -->
      <div x-data="{ open: false }">
        <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
          <span><i class="fa fa-cogs mr-2"></i> System Settings</span>
          <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-show="open" class="pl-6 mt-1 space-y-1">
          <a href="?page=eventsettings" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Event Settings</a>
          <a href="?page=adminsettings" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Admin Settings</a>
          <a href="?page=backup" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Backup Database</a>
        </div>
      </div>
    </nav>
  </aside>
</div>



    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        
        <!-- Navbar -->
        <header class="bg-white shadow-md p-4 flex justify-between items-center">
            <button id="menu-toggle" class="md:hidden text-gray-600 text-xl">
                ☰
            </button>
            <h2 class="text-lg font-semibold">Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
            <a href="" id="logout-btn" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
            <i class="fa fa-sign-out" aria-hidden="true"></i>
            </a>
        </header>

        <!-- Dashboard Content -->
        <main class="p-6 flex-1 overflow-auto min-h-0">
        <?php include "../../pages/$page.php"; ?>
    </main>
    </div>

  <!-- Mobile Sidebar -->
<div id="mobile-menu" class="fixed inset-0 bg-gray-900 text-white w-64 space-y-6 py-7 px-4 transform -translate-x-full transition-transform md:hidden">
    <button id="close-menu" class="absolute top-4 right-4 text-xl">✖</button>
    <h2 class="text-2xl font-semibold text-center">Tabulation System</h2>
    
    <nav class="space-y-2">
        <a href="?page=dashboard" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex items-center">
            <i class="fa fa-home mr-2"></i> Dashboard
        </a>

        <!-- Event Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
                <span><i class="fa fa-calendar mr-2"></i> Event Management</span>
                <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" class="pl-6 mt-1">
                <a href="?page=eventsetup" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Event Setup</a>
            </div>
        </div>

        <!-- Participant Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
                <span><i class="fa fa-users mr-2"></i> Participant Management</span>
                <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" class="pl-6 mt-1">
                <a href="?page=participants" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Participants</a>
            </div>
        </div>

        <!-- Leaderboard Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
                <span><i class="fa fa-bar-chart mr-2"></i> Manage Leaderboard</span>
                <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" class="pl-6 mt-1">
                <a href="?page=finalresults" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Final Results</a>
            </div>
        </div>

        <!-- System Settings -->
        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left py-2.5 px-4 rounded transition duration-200 hover:bg-blue-800 flex justify-between items-center">
                <span><i class="fa fa-cogs mr-2"></i> System Settings</span>
                <i class="fa" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="open" class="pl-6 mt-1 space-y-1">
                <a href="?page=eventsettings" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Event Settings</a>
                <a href="?page=adminsettings" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Admin Settings</a>
                <a href="?page=backup" class="block py-2 px-4 rounded transition duration-200 hover:bg-blue-800">Backup Database</a>
            </div>
        </div>
    </nav>
</div>



    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 mt-6 backdrop-opacity-10 flex items-center justify-center hidden">

        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold text-center">Are you sure you want to logout?</h2>
            <div class="mt-4 flex justify-center space-x-4">
                <button id="cancel-logout" class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500">
                    Cancel
                </button>
                <a href="../../Controllers/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Confirm Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mobile Menu Toggle
            const menuToggle = document.getElementById("menu-toggle");
            const mobileMenu = document.getElementById("mobile-menu");
            const closeMenu = document.getElementById("close-menu");

            if (menuToggle) {
                menuToggle.addEventListener("click", function() {
                    mobileMenu.classList.remove("-translate-x-full");
                });
            }

            if (closeMenu) {
                closeMenu.addEventListener("click", function() {
                    mobileMenu.classList.add("-translate-x-full");
                });
            }

            // Logout Modal
            const logoutBtn = document.getElementById("logout-btn");
            const logoutModal = document.getElementById("logout-modal");
            const cancelLogout = document.getElementById("cancel-logout");

            if (logoutBtn) {
                logoutBtn.addEventListener("click", function(event) {
                    event.preventDefault();
                    logoutModal.classList.remove("hidden");
                });
            }

            if (cancelLogout) {
                cancelLogout.addEventListener("click", function() {
                    logoutModal.classList.add("hidden");
                });
            }
        });
    </script>

</body>
</html>