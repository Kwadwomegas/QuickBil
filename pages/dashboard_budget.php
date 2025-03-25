<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header("Location: login.php");
    exit();
}

// Check session and role (allow only Budget)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Budget') {
    header("Location: login.php");
    exit();
}

// Fetch business counts from separate tables
$battor_count = $conn->query("SELECT COUNT(*) FROM battor_businesses")->fetch_row()[0];
$mepe_count = $conn->query("SELECT COUNT(*) FROM mepe_businesses")->fetch_row()[0];
$juapong_count = $conn->query("SELECT COUNT(*) FROM juapong_businesses")->fetch_row()[0];

// Fetch total amount payable for each region
$battor_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM battor_businesses")->fetch_row()[0];
$mepe_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM mepe_businesses")->fetch_row()[0];
$juapong_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM juapong_businesses")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Budget Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
    <a href="dashboard_admin.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
        <span class="mr-3">🏠</span> Dashboard
    </a>
    <a href="business_view.php" class="flex items-center py-3 px-4 [bg-blue-700 if on business_view.php] rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
        <span class="mr-3">🏢</span> Businesses
    </a>
    <a href="business_map.php" class="flex items-center py-3 px-4 [bg-blue-700 if on business_map.php] rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
        <span class="mr-3">🗺️</span> Business Map
    </a>
    <a href="payment_overview.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
        <span class="mr-3">💰</span> Payment Overview
    </a>
    <a href="business_analytics.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
        <span class="mr-3">📊</span> Business Analytics
    </a>
    <a href="user_activity_logs.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
        <span class="mr-3">📜</span> Activity Logs
    </a>
    <a href="?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
        <span class="mr-3">🚪</span> Logout
    </a>
</nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Budget Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Business Counts -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Battor Businesses</h3>
                    <p class="text-3xl font-extrabold text-blue-400 drop-shadow-md"><?php echo $battor_count; ?></p>
                    <p class="text-gray-400 mt-3">Registered in Battor</p>
                </div>
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Mepe Businesses</h3>
                    <p class="text-3xl font-extrabold text-purple-400 drop-shadow-md"><?php echo $mepe_count; ?></p>
                    <p class="text-gray-400 mt-3">Registered in Mepe</p>
                </div>
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Juapong Businesses</h3>
                    <p class="text-3xl font-extrabold text-green-400 drop-shadow-md"><?php echo $juapong_count; ?></p>
                    <p class="text-gray-400 mt-3">Registered in Juapong</p>
                </div>
                <!-- Total Expected Amounts -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Amount Expected</h3>
                    <p class="text-3xl font-extrabold text-blue-400 drop-shadow-md">₵<?php echo number_format($battor_payable, 2); ?></p>
                    <p class="text-gray-400 mt-3">Total in Battor</p>
                </div>
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Amount Expected</h3>
                    <p class="text-3xl font-extrabold text-purple-400 drop-shadow-md">₵<?php echo number_format($mepe_payable, 2); ?></p>
                    <p class="text-gray-400 mt-3">Total in Mepe</p>
                </div>
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Amount Expected</h3>
                    <p class="text-3xl font-extrabold text-green-400 drop-shadow-md">₵<?php echo number_format($juapong_payable, 2); ?></p>
                    <p class="text-gray-400 mt-3">Total in Juapong</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 p-4 text-center text-gray-400">
        <p>© <?php echo date('Y'); ?> Quickbil. Powered by KabTech Consulting.</p>
    </footer>
</body>
</html>