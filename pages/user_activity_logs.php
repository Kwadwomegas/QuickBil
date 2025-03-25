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
    error_log("Redirecting to login.php from user_activity_logs.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Fetch activity logs with error handling
$activity_logs = false;
try {
    $activity_logs = $conn->query("SELECT l.id, u.username, l.action, l.timestamp 
                                   FROM activity_logs l 
                                   JOIN users u ON l.user_id = u.id 
                                   ORDER BY l.timestamp DESC LIMIT 20");
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching activity logs: " . $e->getMessage());
    $activity_logs_error = "Unable to fetch activity logs. Please ensure the activity_logs table exists.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - User Activity Logs</title>
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
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">User Activity Logs</h1>

            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                <h3 class="text-2xl font-bold text-gray-200 mb-6">User Activity Logs</h3>
                <?php if (isset($activity_logs_error)): ?>
                    <p class="text-red-400 mb-4"><?php echo $activity_logs_error; ?></p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-gray-300">
                            <thead>
                                <tr class="bg-gray-700">
                                    <th class="p-4">ID</th>
                                    <th class="p-4">Username</th>
                                    <th class="p-4">Action</th>
                                    <th class="p-4">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($activity_logs && $activity_logs->num_rows > 0): ?>
                                    <?php while ($log = $activity_logs->fetch_assoc()): ?>
                                        <tr class="border-b border-gray-700 hover:bg-gray-600 transition duration-200">
                                            <td class="p-4"><?php echo htmlspecialchars($log['id']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($log['username']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-4 text-center">No activity logs found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Footer -->
        <footer class="bg-gray-900 border-t border-gray-800 p-4 text-center text-gray-400">
            <p>© <?php echo date('Y'); ?> Quickbil. Powered by KabTech Consulting.</p>
        </footer>
    </div>
</body>
</html>