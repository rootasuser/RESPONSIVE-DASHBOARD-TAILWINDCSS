<?php 
session_start(); 
require_once 'Models/userModel.php'; 

if (!isset($_SESSION['csrf_token'])) 
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = $_SESSION['message'] ?? null;
$message_type = $_SESSION['message_type'] ?? 'info';

unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Login</title>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      setTimeout(() => {
        const toast = document.getElementById("toast");
        if (toast) toast.classList.add("opacity-0", "translate-x-5");
      }, 3000);
    });
  </script>
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

  <!-- Toast Notification -->
  <?php if ($message): ?>
  <div id="toast" class="fixed top-5 right-5 bg-<?php echo ($message_type === 'success') ? 'green' : 'red'; ?>-500 text-white px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300">
    <?php echo $message; ?>
  </div>
  <?php endif; ?>

  <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-semibold mb-6 text-center">Login Portal</h2>
    
    <form action="Controllers/userController.php" method="POST"> 
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <!-- Username -->
      <div class="mb-4">
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        <input type="text" id="username" name="username" class="mt-1 p-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password" class="mt-1 p-2 w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
        Login
      </button>
    </form>
  </div>

</body>
</html>
