<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Judge') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Dashboard</title>
    <link href="../src/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center justify-center">

    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-semibold text-center mb-4">Welcome, Judge <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p class="text-center text-gray-600">Access your judging panel and evaluate participants.</p>

        <div class="mt-4 flex justify-center">
            <a href="../Controllers/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                Logout
            </a>
        </div>
    </div>

</body>
</html>
