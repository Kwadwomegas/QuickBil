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
    header("Location: login.php");
    exit();
}

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
    $stmt->bind_param("ii", $user_id, $_SESSION['user_id']); // Prevent deleting self
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all users
$users = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWhWvIreFKsb8iKv4Tun5Zr5JLT8c4kB4&libraries=places"></script>
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
                <a href="business_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏢</span> Businesses
                </a>
                <a href="register.php" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">👤</span> Manage Users
                </a>
                <a href="?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Manage Users</h1>

            <!-- Registration Form -->
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

            <!-- User List -->
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
                                            <!-- Edit Button (placeholder) -->
                                            <button class="px-3 py-1 bg-yellow-600 rounded hover:bg-yellow-700 transition duration-200 mr-2">Edit</button>
                                            <!-- Delete Form -->
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
    </div>
</body>
</html>