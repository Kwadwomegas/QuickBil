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

// Check session and role (allow Cashier, Admin, Budget, Finance)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Cashier', 'Admin', 'Budget', 'Finance'])) {
    header("Location: login.php");
    exit();
}

// Fetch payments from all regions
$query = "
    SELECT payment_id, account_number, business_name, amount, payment_date, receipt_number, 'Battor' as region FROM battor_payments
    UNION
    SELECT payment_id, account_number, business_name, amount, payment_date, receipt_number, 'Mepe' as region FROM mepe_payments
    UNION
    SELECT payment_id, account_number, business_name, amount, payment_date, receipt_number, 'Juapong' as region FROM juapong_payments
    ORDER BY payment_date DESC
";
$result = $conn->query($query);

// Calculate total payments
$total_payments = $conn->query("
    SELECT COALESCE(SUM(amount), 0) FROM (
        SELECT amount FROM battor_payments
        UNION ALL
        SELECT amount FROM mepe_payments
        UNION ALL
        SELECT amount FROM juapong_payments
    ) AS all_payments
")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - View Payments</title>
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
                <a href="dashboard_finance.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="battor_payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💰</span> Battor Payments
                </a>
                <a href="mepe_payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💰</span> Mepe Payments
                </a>
                <a href="juapong_payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💰</span> Juapong Payments
                </a>
                <a href="payment_view.php" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">📜</span> View Payments
                </a>
                <a href="?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">View Payments</h1>
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                <h3 class="text-2xl font-bold text-gray-200 mb-6">Payment Records</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-gray-300">
                        <thead>
                            <tr class="bg-gray-700">
                                <th class="p-4">Payment ID</th>
                                <th class="p-4">Account Number</th>
                                <th class="p-4">Business Name</th>
                                <th class="p-4">Amount (₵)</th>
                                <th class="p-4">Payment Date</th>
                                <th class="p-4">Receipt Number</th>
                                <th class="p-4">Region</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-700 hover:bg-gray-600 transition duration-200">
                                        <td class="p-4"><?php echo htmlspecialchars($row['payment_id']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['account_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['business_name']); ?></td>
                                        <td class="p-4">₵<?php echo number_format($row['amount'], 2); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['payment_date']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['receipt_number']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['region']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="p-4 text-center">No payments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    <p class="text-xl font-semibold text-gray-200">Total Payments: <span class="text-blue-400">₵<?php echo number_format($total_payments, 2); ?></span></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>