<?php
include 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard_" . strtolower($_SESSION['role']) . ".php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <!-- Hero Section -->
    <section class="flex flex-col items-center justify-center h-64 py-4">
        <div class="text-center mb-4">
            <h1 class="text-5xl md:text-7xl font-extrabold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent animate-pulse">Quickbil</h1>
            <p class="text-lg md:text-xl text-gray-300 mt-1">Your All-in-One Revenue Management Solution</p>
            <div class="mt-2">
                <a href="pages/login.php" class="inline-block py-3 px-6 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition duration-300 shadow-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-4 md:px-12 bg-gray-800">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent mb-12">What Quickbil Offers</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">💰</span>
                <h3 class="text-2xl font-semibold text-blue-400">Region-Specific Payments</h3>
                <p class="text-gray-300 mt-2">Manage payments for Battor, Mepe, and Juapong with ease, tracking fees, arrears, and receipts in real-time.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">👤</span>
                <h3 class="text-2xl font-semibold text-purple-400">Role-Based Access</h3>
                <p class="text-gray-300 mt-2">Custom dashboards for Cashiers, Admins, Budget, and Finance teams to streamline workflows and permissions.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">📊</span>
                <h3 class="text-2xl font-semibold text-blue-400">Revenue Insights</h3>
                <p class="text-gray-300 mt-2">Track financial data, monitor payments, and generate reports to keep revenue management transparent.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">🔒</span>
                <h3 class="text-2xl font-semibold text-purple-400">Secure & Reliable</h3>
                <p class="text-gray-300 mt-2">Built with security in mind to protect your data and ensure smooth, dependable performance.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">⚡</span>
                <h3 class="text-2xl font-semibold text-blue-400">Fast & Efficient</h3>
                <p class="text-gray-300 mt-2">Quickly process payments and updates, saving time for your team and businesses alike.</p>
            </div>
            <div class="bg-gray-700 p-6 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <span class="text-3xl mb-4 block">📋</span>
                <h3 class="text-2xl font-semibold text-purple-400">Easy Record Keeping</h3>
                <p class="text-gray-300 mt-2">Store and retrieve payment records effortlessly for audits, reviews, or reconciliations.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 px-4 md:px-12 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-200 mb-6">Ready to Simplify Your Revenue Management?</h2>
        <p class="text-lg text-gray-400 mb-8">Log in to Quickbil and take control of your financial operations.</p>
        <a href="pages/login.php" class="inline-block py-4 px-8 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition duration-300 shadow-lg">Get Started</a>
    </section>

    <!-- Footer -->
    <footer class="py-8 text-center bg-gray-900">
        <p class="text-gray-400">© <?php echo date('Y'); ?> Quickbil. All rights reserved.Powered by KabTech Consulting</p>
    </footer>
</body>
</html>