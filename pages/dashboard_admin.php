<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log session variables to check if they are set
error_log("Session variables in dashboard_admin.php: user_id=" . ($_SESSION['user_id'] ?? 'not set') . ", role=" . ($_SESSION['role'] ?? 'not set'));

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check session and role (allow only Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    error_log("Redirecting to login.php from dashboard_admin.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Fetch business counts
$battor_businesses = $conn->query("SELECT COUNT(*) FROM battor_businesses")->fetch_row()[0];
$mepe_businesses = $conn->query("SELECT COUNT(*) FROM mepe_businesses")->fetch_row()[0];
$juapong_businesses = $conn->query("SELECT COUNT(*) FROM juapong_businesses")->fetch_row()[0];
$total_businesses = $battor_businesses + $mepe_businesses + $juapong_businesses;

// Fetch total revenue (amount paid)
$battor_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM battor_payments")->fetch_row()[0];
$mepe_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM mepe_payments")->fetch_row()[0];
$juapong_revenue = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM juapong_payments")->fetch_row()[0];
$total_revenue = $battor_revenue + $mepe_revenue + $juapong_revenue;

// Fetch total amount payable
$battor_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM battor_businesses")->fetch_row()[0];
$mepe_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM mepe_businesses")->fetch_row()[0];
$juapong_payable = $conn->query("SELECT COALESCE(SUM(amount_payable), 0) FROM juapong_businesses")->fetch_row()[0];
$total_payable = $battor_payable + $mepe_payable + $juapong_payable;

// Calculate total arrears
$total_arrears = $total_payable - $total_revenue;

// Calculate percentages for Amount Payable pie chart
$payable_percentages = [];
$payable_regions = ['Battor', 'Mepe', 'Juapong'];
$payable_amounts = [$battor_payable, $mepe_payable, $juapong_payable];
if ($total_payable > 0) {
    $payable_percentages = [
        round(($battor_payable / $total_payable) * 100, 2),
        round(($mepe_payable / $total_payable) * 100, 2),
        round(($juapong_payable / $total_payable) * 100, 2)
    ];
} else {
    $payable_percentages = [0, 0, 0];
}

// Calculate data for Amount Paid and Arrears pie chart
$paid_arrears_labels = ['Battor Paid', 'Mepe Paid', 'Juapong Paid', 'Total Arrears'];
$paid_arrears_amounts = [$battor_revenue, $mepe_revenue, $juapong_revenue, $total_arrears];
$total_paid_and_arrears = $total_revenue + $total_arrears;
$paid_arrears_percentages = [];
if ($total_paid_and_arrears > 0) {
    $paid_arrears_percentages = [
        round(($battor_revenue / $total_paid_and_arrears) * 100, 2),
        round(($mepe_revenue / $total_paid_and_arrears) * 100, 2),
        round(($juapong_revenue / $total_paid_and_arrears) * 100, 2),
        round(($total_arrears / $total_paid_and_arrears) * 100, 2)
    ];
} else {
    $paid_arrears_percentages = [0, 0, 0, 0];
}

// Prepare data for the pie charts
$payable_chart_data = json_encode([
    'labels' => array_map(function($region, $percentage) {
        return "$region ($percentage%)";
    }, $payable_regions, $payable_percentages),
    'amounts' => $payable_amounts
]);

$paid_arrears_chart_data = json_encode([
    'labels' => array_map(function($label, $percentage) {
        return "$label ($percentage%)";
    }, $paid_arrears_labels, $paid_arrears_percentages),
    'amounts' => $paid_arrears_amounts
]);

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = trim($_POST['role']);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    if ($stmt->execute()) {
        $success = "User registered successfully!";
    } else {
        $error = "Error registering user: " . $conn->error;
    }
    $stmt->close();
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all users
$users = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");

// Handle database backup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup'])) {
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $command = "mysqldump -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . $backup_file;
    exec($command, $output, $return_var);
    if ($return_var === 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_file . '"');
        readfile($backup_file);
        unlink($backup_file);
        exit();
    } else {
        $error = "Backup failed!";
    }
}

// Handle backup import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file']['tmp_name'];
    if (pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION) === 'sql') {
        $command = "mysql -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " < " . $file;
        exec($command, $output, $return_var);
        if ($return_var === 0) {
            $success = "Backup imported successfully!";
        } else {
            $error = "Error importing backup!";
        }
    } else {
        $error = "Please upload a .sql file!";
    }
}

// Handle bulk business import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['business_file'])) {
    $file = $_FILES['business_file']['tmp_name'];
    if (pathinfo($_FILES['business_file']['name'], PATHINFO_EXTENSION) === 'csv') {
        $handle = fopen($file, 'r');
        fgetcsv($handle); // Skip header row
        $stmt_battor = $conn->prepare("INSERT INTO battor_businesses (account_number, business_name, amount_payable) VALUES (?, ?, ?)");
        $stmt_mepe = $conn->prepare("INSERT INTO mepe_businesses (account_number, business_name, amount_payable) VALUES (?, ?, ?)");
        $stmt_juapong = $conn->prepare("INSERT INTO juapong_businesses (account_number, business_name, amount_payable) VALUES (?, ?, ?)");
        while (($data = fgetcsv($handle)) !== false) {
            $account_number = $data[0];
            $business_name = $data[1];
            $amount_payable = floatval($data[2]);
            $region = strtolower(trim($data[3]));
            if ($region === 'battor') {
                $stmt_battor->bind_param("ssd", $account_number, $business_name, $amount_payable);
                $stmt_battor->execute();
            } elseif ($region === 'mepe') {
                $stmt_mepe->bind_param("ssd", $account_number, $business_name, $amount_payable);
                $stmt_mepe->execute();
            } elseif ($region === 'juapong') {
                $stmt_juapong->bind_param("ssd", $account_number, $business_name, $amount_payable);
                $stmt_juapong->execute();
            }
        }
        fclose($handle);
        $success = "Businesses imported successfully!";
    } else {
        $error = "Please upload a .csv file!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('bg-blue-700', 'shadow-md'));
            document.getElementById(tabId).classList.remove('hidden');
            document.querySelector(`button[onclick="showTab('${tabId}')"]`).classList.add('bg-blue-700', 'shadow-md');
        }
    </script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter" onload="showTab('overview')">
    <div class="flex flex-col min-h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
                <a href="#" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="business_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏢</span> Businesses
                </a>
                <a href="business_map.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🗺️</span> Business Map
                </a>
                <a href="bills.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">💵</span> Bills
                </a>
                <a href="?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Admin Dashboard</h1>

            <!-- Tabs -->
            <div class="flex space-x-4 mb-8">
                <button onclick="showTab('overview')" class="tab-button py-2 px-4 bg-gray-700 rounded-lg hover:bg-blue-700 transition duration-300 text-lg font-semibold">Overview</button>
                <button onclick="showTab('users')" class="tab-button py-2 px-4 bg-gray-700 rounded-lg hover:bg-blue-700 transition duration-300 text-lg font-semibold">Manage Users</button>
                <button onclick="showTab('backup')" class="tab-button py-2 px-4 bg-gray-700 rounded-lg hover:bg-blue-700 transition duration-300 text-lg font-semibold">Backup Database</button>
                <button onclick="showTab('import')" class="tab-button py-2 px-4 bg-gray-700 rounded-lg hover:bg-blue-700 transition duration-300 text-lg font-semibold">Import Backup</button>
                <button onclick="showTab('bulk')" class="tab-button py-2 px-4 bg-gray-700 rounded-lg hover:bg-blue-800 transition duration-300 text-lg font-semibold">Bulk Import Businesses</button>
            </div>

            <!-- Tab Content -->
            <div id="overview" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-200 mb-4">Total Businesses</h3>
                        <p class="text-3xl font-extrabold text-blue-400 drop-shadow-md"><?php echo $total_businesses; ?></p>
                        <p class="text-gray-400 mt-3">Across Battor, Mepe, Juapong</p>
                    </div>
                    <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-200 mb-4">Total Revenue</h3>
                        <p class="text-3xl font-extrabold text-purple-400 drop-shadow-md">₵<?php echo number_format($total_revenue, 2); ?></p>
                        <p class="text-gray-400 mt-3">Collected from payments</p>
                    </div>
                    <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl hover:shadow-xl transition duration-500 transform hover:-translate-y-2 border border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-200 mb-4">Total Arrears</h3>
                        <p class="text-3xl font-extrabold text-red-400 drop-shadow-md">₵<?php echo number_format($total_arrears, 2); ?></p>
                        <p class="text-gray-400 mt-3">Outstanding dues</p>
                    </div>
                </div>
                <!-- Pie Charts Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Amount Payable Pie Chart -->
                    <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-200 mb-6">Amount Payable by Region</h3>
                        <canvas id="payableChart" class="max-w-md mx-auto"></canvas>
                    </div>
                    <!-- Amount Paid and Arrears Pie Chart -->
                    <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-200 mb-6">Amount Paid and Arrears</h3>
                        <canvas id="paidArrearsChart" class="max-w-md mx-auto"></canvas>
                    </div>
                </div>
            </div>

            <div id="users" class="tab-content hidden">
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700 mb-8">
                    <h3 class="text-2xl font-bold text-gray-200 mb-6">Register New User</h3>
                    <?php if (isset($success)): ?>
                        <p class="text-green-400 mb-4"><?php echo $success; ?></p>
                    <?php elseif (isset($error)): ?>
                        <p class="text-red-400 mb-4"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-gray-300 mb-2" for="username">Username</label>
                            <input type="text" name="username" id="username" required class="w-full p-3 bg-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-2" for="password">Password</label>
                            <input type="password" name="password" id="password" required class="w-full p-3 bg-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-300 mb-2" for="role">Role</label>
                            <select name="role" id="role" required class="w-full p-3 bg-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Admin">Admin</option>
                                <option value="Budget">Budget</option>
                                <option value="Finance">Finance</option>
                                <option value="Cashier">Cashier</option>
                            </select>
                        </div>
                        <button type="submit" name="register" class="w-full py-3 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">Register User</button>
                    </form>
                </div>
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-6">Existing Users</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-gray-300">
                            <thead>
                                <tr class="bg-gray-700">
                                    <th class="p-4">ID</th>
                                    <th class="p-4">Username</th>
                                    <th class="p-4">Role</th>
                                    <th class="p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users && $users->num_rows > 0): ?>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <tr class="border-b border-gray-700 hover:bg-gray-600 transition duration-200">
                                            <td class="p-4"><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td class="p-4"><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td class="p-4">
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete" class="px-3 py-1 bg-red-600 rounded hover:bg-red-700 transition duration-200">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="p-4 text-center">No users found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="backup" class="tab-content hidden">
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-6">Backup Database</h3>
                    <form method="POST">
                        <button type="submit" name="backup" class="w-full py-3 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">Download Backup</button>
                    </form>
                </div>
            </div>

            <div id="import" class="tab-content hidden">
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-6">Import Backup</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="file" name="backup_file" accept=".sql" required class="w-full p-3 bg-gray-700 rounded-lg text-white">
                        <button type="submit" class="w-full py-3 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">Import Backup</button>
                    </form>
                </div>
            </div>

            <div id="bulk" class="tab-content hidden">
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-200 mb-6">Bulk Import Businesses</h3>
                    <p class="text-gray-400 mb-4">CSV format: account_number,business_name,amount_payable,region (battor/mepe/juapong)</p>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="file" name="business_file" accept=".csv" required class="w-full p-3 bg-gray-700 rounded-lg text-white">
                        <button type="submit" class="w-full py-3 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">Import Businesses</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <footer class="bg-gray-900 border-t border-gray-800 p-4 text-center text-gray-400">
            <p>© <?php echo date('Y'); ?> Quickbil. Powered by KabTech Consulting.</p>
        </footer>
    </div>

    <!-- JavaScript to render the pie charts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartOptions = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#E5E7EB',
                            font: { size: 14 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                label += '₵' + context.raw.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            };

            const payableCtx = document.getElementById('payableChart').getContext('2d');
            const payableChartData = <?php echo $payable_chart_data; ?>;
            new Chart(payableCtx, {
                type: 'pie',
                data: {
                    labels: payableChartData.labels,
                    datasets: [{
                        data: payableChartData.amounts,
                        backgroundColor: ['rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(75, 192, 192, 0.8)'],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)', 'rgba(75, 192, 192, 1)'],
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            const paidArrearsCtx = document.getElementById('paidArrearsChart').getContext('2d');
            const paidArrearsChartData = <?php echo $paid_arrears_chart_data; ?>;
            new Chart(paidArrearsCtx, {
                type: 'pie',
                data: {
                    labels: paidArrearsChartData.labels,
                    datasets: [{
                        data: paidArrearsChartData.amounts,
                        backgroundColor: ['rgba(54, 162, 235, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(255, 99, 132, 0.8)'],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(153, 102, 255, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });
        });
    </script>
</body>
</html>