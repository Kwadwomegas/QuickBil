<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log session variables to check if they are set
error_log("Session variables in dashboard_finance.php: user_id=" . ($_SESSION['user_id'] ?? 'not set') . ", role=" . ($_SESSION['role'] ?? 'not set'));

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header("Location: login.php");
    exit();
}

// Check session and role (allow only Finance)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Finance') {
    error_log("Redirecting to login.php from dashboard_finance.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Fetch total payments for each region
$battor_payments = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM battor_payments")->fetch_row()[0];
$mepe_payments = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM mepe_payments")->fetch_row()[0];
$juapong_payments = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM juapong_payments")->fetch_row()[0];
$total_payments = $battor_payments + $mepe_payments + $juapong_payments;

// Fetch total amount payable
$battor_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM battor_businesses")->fetch_row()[0];
$mepe_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM mepe_businesses")->fetch_row()[0];
$juapong_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM juapong_businesses")->fetch_row()[0];
$total_payable = $battor_payable + $mepe_payable + $juapong_payable;

// Calculate total arrears as total payable minus total payments
$total_arrears = $total_payable - $total_payments;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Finance Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <div class="flex flex-col min-h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
                <a href="#" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💸</span> Add Payment
                </a>
                <a href="business_map.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🗺️</span> Business Map
                </a>
                <a href="?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Finance Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Total Payments -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Total Payments</h3>
                    <p class="text-3xl font-extrabold text-blue-400 drop-shadow-md">₵<?php echo number_format($total_payments, 2); ?></p>
                    <p class="text-gray-400 mt-3">Collected across regions</p>
                </div>
                <!-- Battor Payments -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Battor Payments</h3>
                    <p class="text-3xl font-extrabold text-blue-400 drop-shadow-md">₵<?php echo number_format($battor_payments, 2); ?></p>
                    <p class="text-gray-400 mt-3">Collected in Battor</p>
                </div>
                <!-- Mepe Payments -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Mepe Payments</h3>
                    <p class="text-3xl font-extrabold text-purple-400 drop-shadow-md">₵<?php echo number_format($mepe_payments, 2); ?></p>
                    <p class="text-gray-400 mt-3">Collected in Mepe</p>
                </div>
                <!-- Juapong Payments -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Juapong Payments</h3>
                    <p class="text-3xl font-extrabold text-green-400 drop-shadow-md">₵<?php echo number_format($juapong_payments, 2); ?></p>
                    <p class="text-gray-400 mt-3">Collected in Juapong</p>
                </div>
                <!-- Amount Expected -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Amount Expected</h3>
                    <p class="text-3xl font-extrabold text-yellow-400 drop-shadow-md">₵<?php echo number_format($total_payable, 2); ?></p>
                    <p class="text-gray-400 mt-3">Total payable across regions</p>
                </div>
                <!-- Pending Arrears -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Pending Arrears</h3>
                    <p class="text-3xl font-extrabold text-red-400 drop-shadow-md">₵<?php echo number_format($total_arrears, 2); ?></p>
                    <p class="text-gray-400 mt-3">Outstanding dues</p>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <footer class="bg-gray-900 border-t border-gray-800 p-4 text-center text-gray-400">
            <p>© <?php echo date('Y'); ?> Quickbil. Powered by KabTech Consulting.</p>
        </footer>
    </div>
</body>
</html>