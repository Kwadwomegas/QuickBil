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

// Fetch businesses from all regions
$regions = ['battor', 'mepe', 'juapong'];
$businesses = [];
foreach ($regions as $region) {
    $table = $region . '_businesses';
    $query = "SELECT account_number, business_name, owner_name, business_type, telephone, business_category, category, old_fee, previous_payment, arrears, current_fee, amount_payable, batch, status, location FROM $table";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['region'] = ucfirst($region);
            $businesses[] = $row;
        }
    } else {
        error_log("Error fetching businesses from $table: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - View Businesses</title>
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
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">View Businesses</h1>
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                <!-- Navigation Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-md mb-6">
                    <a href="business_view.php" class="flex items-center justify-center py-4 px-6 bg-blue-700 text-white rounded-xl font-bold hover:bg-blue-800 transition duration-300 shadow-md">
                        <span class="mr-3">🏢</span> View Businesses
                    </a>
                    <a href="payment_add.php" class="flex items-center justify-center py-4 px-6 bg-green-700 text-white rounded-xl font-bold hover:bg-green-800 transition duration-300 shadow-md">
                        <span class="mr-3">💰</span> Add Payment
                    </a>
                </div>
                <!-- Search Bar -->
                <input type="text" id="search" placeholder="Search by Account, Name, Owner, or Region" class="p-4 w-full bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200 mb-6" oninput="filterTable()">
                <!-- Businesses Table -->
                <div class="overflow-x-auto">
                    <table id="businessTable" class="w-full text-left text-gray-200">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="p-4 font-bold">Account</th>
                                <th class="p-4 font-bold">Business Name</th>
                                <th class="p-4 font-bold">Owner</th>
                                <th class="p-4 font-bold">Region</th>
                                <th class="p-4 font-bold">Category</th>
                                <th class="p-4 font-bold">Amount Payable</th>
                                <th class="p-4 font-bold">Status</th>
                                <th class="p-4 font-bold">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($businesses as $business): ?>
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition duration-300">
                                    <td class="p-4"><?php echo htmlspecialchars($business['account_number']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['business_name']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['owner_name']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['region']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['category']); ?></td>
                                    <td class="p-4">$<?php echo number_format($business['amount_payable'], 2); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['status']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($business['location'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($businesses)): ?>
                                <tr>
                                    <td colspan="8" class="p-4 text-center text-gray-400">No businesses found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const table = document.getElementById('businessTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) { // Start at 1 to skip thead
                const cells = rows[i].getElementsByTagName('td');
                let match = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(search)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? '' : 'none';
            }
        }
    </script>
</body>
</html>