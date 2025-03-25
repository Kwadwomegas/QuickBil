<?php
ob_start(); // Start output buffering to prevent header issues
 // Start session explicitly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/config.php'; // Include config early

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWhWvIreFKsb8iKv4Tun5Zr5JLT8c4kB4&libraries=places"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-2xl shadow-xl w-full max-w-md transform transition-all hover:shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">Login</h1>
            <p class="text-gray-400 mt-2">Access Your Quickbil Account</p>
        </div>
        <form method="POST" action="login.php" class="space-y-6">
            <input type="text" name="username" placeholder="Username" required class="w-full p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400 text-gray-200">
            <input type="password" name="password" placeholder="Password" required class="w-full p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400 text-gray-200">
            <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition duration-300 shadow-md">Login</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']); // Trim whitespace
            $password = trim($_POST['password']);

            // Prepare statement with error checking
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            if (!$stmt) {
                echo "<p class='text-red-400 text-center mt-6'>Database error: " . $conn->error . "</p>";
            } else {
                $stmt->bind_param("s", $username);
                if ($stmt->execute()) {
                    $user = $stmt->get_result()->fetch_assoc();
                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        $redirect = "dashboard_" . strtolower($user['role']) . ".php";
                        header("Location: $redirect");
                        ob_end_flush(); // Send output and headers
                        exit();
                    } else {
                        echo "<p class='text-red-400 text-center mt-6'>Invalid username or password!</p>";
                    }
                } else {
                    echo "<p class='text-red-400 text-center mt-6'>Query execution failed: " . $stmt->error . "</p>";
                }
                $stmt->close();
            }
        }
        ?>
    </div>
</body>
</html>
<?php ob_end_flush(); // Ensure all output is sent ?>