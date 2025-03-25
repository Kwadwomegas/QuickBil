<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check session and role (allow only Admin and Budget)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Budget'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Businesses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
                <a href="<?php echo $_SESSION['role'] === 'Admin' ? 'dashboard_admin.php' : 'dashboard_budget.php'; ?>" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="businesses.php" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">🏢</span> Businesses
                </a>
                <a href="business_map.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🗺️</span> Business Map
                </a>
                <a href="bills.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💵</span> Bills
                </a>
                <a href="../index.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72 flex flex-col">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Businesses</h1>
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700 flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-md mx-auto">
                    <a href="business_view.php" class="flex items-center justify-center py-4 px-6 bg-blue-700 text-white rounded-xl font-bold hover:bg-blue-800 transition duration-300 shadow-md">
                        <span class="mr-3">🏢</span> View Businesses
                    </a>
                    <a href="payment_add.php" class="flex items-center justify-center py-4 px-6 bg-green-700 text-white rounded-xl font-bold hover:bg-green-800 transition duration-300 shadow-md">
                        <span class="mr-3">💰</span> Add Payment
                    </a>
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