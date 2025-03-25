<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check session and role (allow only Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    error_log("Redirecting to login.php from business_analytics.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Fetch business counts
$battor_businesses = $conn->query("SELECT COUNT(*) FROM battor_businesses")->fetch_row()[0];
$mepe_businesses = $conn->query("SELECT COUNT(*) FROM mepe_businesses")->fetch_row()[0];
$juapong_businesses = $conn->query("SELECT COUNT(*) FROM juapong_businesses")->fetch_row()[0];

// Fetch average amount payable
$avg_payable_battor = $conn->query("SELECT COALESCE(AVG(amount_payable), 0) FROM battor_businesses")->fetch_row()[0];
$avg_payable_mepe = $conn->query("SELECT COALESCE(AVG(amount_payable), 0) FROM mepe_businesses")->fetch_row()[0];
$avg_payable_juapong = $conn->query("SELECT COALESCE(AVG(amount_payable), 0) FROM juapong_businesses")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Business Analytics</title>
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
                <a href="dashboard_admin.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="business_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏢</span> Businesses
                </a>
                <a href="business_map.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🗺️</span> Business Map
                </a>
                <a href="bills.php" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">💵</span> Bills
                </a>
                <a href="dashboard_admin.php?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Business Analytics</h1>

            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700 mb-8">
                <h3 class="text-2xl font-bold text-gray-200 mb-6">Business Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-gray-700 p-6 rounded-xl">
                        <h4 class="text-xl font-semibold text-gray-200 mb-2">Battor Businesses</h4>
                        <p class="text-2xl font-bold text-blue-400"><?php echo $battor_businesses; ?></p>
                        <p class="text-gray-400 mt-2">Avg Payable: ₵<?php echo number_format($avg_payable_battor, 2); ?></p>
                    </div>
                    <div class="bg-gray-700 p-6 rounded-xl">
                        <h4 class="text-xl font-semibold text-gray-200 mb-2">Mepe Businesses</h4>
                        <p class="text-2xl font-bold text-purple-400"><?php echo $mepe_businesses; ?></p>
                        <p class="text-gray-400 mt-2">Avg Payable: ₵<?php echo number_format($avg_payable_mepe, 2); ?></p>
                    </div>
                    <div class="bg-gray-700 p-6 rounded-xl">
                        <h4 class="text-xl font-semibold text-gray-200 mb-2">Juapong Businesses</h4>
                        <p class="text-2xl font-bold text-teal-400"><?php echo $juapong_businesses; ?></p>
                        <p class="text-gray-400 mt-2">Avg Payable: ₵<?php echo number_format($avg_payable_juapong, 2); ?></p>
                    </div>
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