<?php
include '../includes/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Cashier', 'Admin', 'Budget', 'Finance'])) {
    header("Location: ../pages/login.php");
    exit();
}

$message = '';

function generatePaymentId($conn) {
    $table = 'mepe_payments';
    $stmt = $conn->prepare("SELECT payment_id FROM $table ORDER BY CAST(SUBSTRING(payment_id, 5) AS UNSIGNED) DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? "PAY-" . str_pad((int)substr($result->fetch_assoc()['payment_id'], 4) + 1, 3, "0", STR_PAD_LEFT) : "PAY-001";
}

$payment_id = generatePaymentId($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $payment_id = $_POST['payment_id'];
    $account_number = $_POST['account_number'];
    $business_name = $_POST['business_name'];
    $old_fee = $_POST['old_fee'];
    $previous_payment = $_POST['previous_payment'];
    $arrears = $_POST['arrears'];
    $current_fee = $_POST['current_fee'];
    $amount_payable = $_POST['amount_payable'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $receipt_number = $_POST['receipt_number'];

    // Check for duplicate receipt_number
    $stmt = $conn->prepare("SELECT receipt_number FROM mepe_payments WHERE receipt_number = ?");
    $stmt->bind_param("s", $receipt_number);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $message = "Error: Receipt number '$receipt_number' already exists!";
    } else {
        // Insert only specified fields into mepe_payments
        $stmt = $conn->prepare("INSERT INTO mepe_payments (payment_id, account_number, business_name, amount, payment_date, receipt_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdds", $payment_id, $account_number, $business_name, $amount, $payment_date, $receipt_number);
        if ($stmt->execute()) {
            $message = "Payment added successfully!";
            // Update mepe_businesses with amount
            $stmt = $conn->prepare("UPDATE mepe_businesses SET previous_payment = previous_payment + ?, arrears = arrears - ? WHERE account_number = ?");
            $stmt->bind_param("dds", $amount, $amount, $account_number);
            $stmt->execute();
            $payment_id = generatePaymentId($conn);
        } else {
            $message = "Error adding payment: " . $conn->error;
        }
    }
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT account_number, business_name, old_fee, previous_payment, arrears, current_fee, amount_payable FROM mepe_businesses WHERE account_number LIKE ? OR business_name LIKE ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $like_search = "%$search%";
    $stmt->bind_param("ss", $like_search, $like_search);
    $stmt->execute();
    $result = $stmt->get_result();
    header('Content-Type: application/json');
    echo json_encode($result->num_rows > 0 ? $result->fetch_assoc() : []);
    exit();
}

if (isset($_GET['get_payment_id'])) {
    echo generatePaymentId($conn);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Add Mepe Payment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <div class="flex">
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
                <a href="dashboard_finance.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold"><span class="mr-3">🏠</span> Dashboard</a>
                <a href="battor_payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold"><span class="mr-3">💰</span> Battor Payments</a>
                <a href="mepe_payment_add.php" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md"><span class="mr-3">💰</span> Mepe Payments</a>
                <a href="juapong_payment_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold"><span class="mr-3">💰</span> Juapong Payments</a>
                <a href="payment_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold"><span class="mr-3">📜</span> View Payments</a>
                <a href="../index.php?logout=true" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold"><span class="mr-3">🚪</span> Logout</a>
            </nav>
        </div>
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-6 drop-shadow-lg">Add Mepe Payment</h1>
            <div class="flex gap-4 mb-6">
                <input type="text" id="search" placeholder="Search by Account Number or Business Name" class="p-2 w-full bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200" onkeyup="searchBusiness()">
            </div>
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                <form method="POST" id="paymentForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Side -->
                    <div class="space-y-6">
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-300">Account Number</label>
                            <input type="text" name="account_number" id="account_number" placeholder="Account Number" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="business_name" class="block text-sm font-medium text-gray-300">Business Name</label>
                            <input type="text" name="business_name" id="business_name" placeholder="Business Name" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="old_fee" class="block text-sm font-medium text-gray-300">Old Fee</label>
                            <input type="number" name="old_fee" id="old_fee" placeholder="Old Fee" step="0.01" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="previous_payment" class="block text-sm font-medium text-gray-300">Previous Payment</label>
                            <input type="number" name="previous_payment" id="previous_payment" placeholder="Previous Payment" step="0.01" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="arrears" class="block text-sm font-medium text-gray-300">Arrears</label>
                            <input type="number" name="arrears" id="arrears" placeholder="Arrears" step="0.01" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="current_fee" class="block text-sm font-medium text-gray-300">Current Fee</label>
                            <input type="number" name="current_fee" id="current_fee" placeholder="Current Fee" step="0.01" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                        <div>
                            <label for="amount_payable" class="block text-sm font-medium text-gray-300">Amount Payable</label>
                            <input type="number" name="amount_payable" id="amount_payable" placeholder="Amount Payable" step="0.01" class="mt-1 p-2 w-full bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                        </div>
                    </div>
                    <!-- Right Side -->
                    <div class="space-y-6">
                        <div>
                            <label for="payment_id" class="block text-sm font-medium text-gray-300">Payment ID</label>
                            <input type="text" name="payment_id" id="payment_id" value="<?php echo $payment_id; ?>" placeholder="Payment ID" class="mt-1 p-2 w-full bg-gray-700 border border-gray-600 rounded-lg text-gray-200" readonly>
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-300">Amount</label>
                            <input type="number" name="amount" id="amount" placeholder="Amount" step="0.01" required class="mt-1 p-2 w-full bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        </div>
                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-300">Payment Date</label>
                            <input type="date" name="payment_date" placeholder="Payment Date" required class="mt-1 p-2 w-full bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        </div>
                        <div>
                            <label for="receipt_number" class="block text-sm font-medium text-gray-300">Receipt Number</label>
                            <input type="text" name="receipt_number" id="receipt_number" placeholder="Receipt Number" required class="mt-1 p-2 w-full bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        </div>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <button type="submit" name="submit" class="py-2 bg-blue-700 text-white rounded-xl font-bold hover:bg-blue-800 transition duration-300 shadow-md">Submit</button>
                        <button type="button" onclick="resetForm();" class="py-2 bg-gray-600 text-white rounded-xl font-bold hover:bg-gray-700 transition duration-300 shadow-md">Clear</button>
                    </div>
                </form>
                <?php if ($message): ?>
                    <p class="text-center mt-6 <?php echo strpos($message, 'Error') === false ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function searchBusiness() {
            const search = document.getElementById('search').value.trim();
            if (search.length < 1) {
                resetSearchFields();
                return;
            }

            fetch(`?search=${encodeURIComponent(search)}`, { method: 'GET' })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Search response:', data);
                    if (data && Object.keys(data).length > 0) {
                        document.getElementById('account_number').value = data.account_number || '';
                        document.getElementById('business_name').value = data.business_name || '';
                        document.getElementById('old_fee').value = data.old_fee ? parseFloat(data.old_fee).toFixed(2) : '';
                        document.getElementById('previous_payment').value = data.previous_payment ? parseFloat(data.previous_payment).toFixed(2) : '';
                        document.getElementById('arrears').value = data.arrears ? parseFloat(data.arrears).toFixed(2) : '';
                        document.getElementById('current_fee').value = data.current_fee ? parseFloat(data.current_fee).toFixed(2) : '';
                        document.getElementById('amount_payable').value = data.amount_payable ? parseFloat(data.amount_payable).toFixed(2) : '';
                    } else {
                        resetSearchFields();
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resetSearchFields();
                });
        }

        function resetSearchFields() {
            ['account_number', 'business_name', 'old_fee', 'previous_payment', 'arrears', 'current_fee', 'amount_payable'].forEach(id => {
                document.getElementById(id).value = '';
            });
        }

        function resetForm() {
            document.getElementById('paymentForm').reset();
            resetSearchFields();
            document.getElementById('search').value = '';
            fetch('?get_payment_id=true').then(response => response.text()).then(data => {
                document.getElementById('payment_id').value = data;
            });
        }

        window.onload = () => fetch('?get_payment_id=true').then(response => response.text()).then(data => {
            document.getElementById('payment_id').value = data;
        });
    </script>
</body>
</html>