<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Budget') {
    header("Location: login.php");
    exit();
}

$message = '';

function generateAccountNumber($conn, $region) {
    $prefix = ['Battor' => 'BAT', 'Mepe' => 'MEP', 'Juapong' => 'JUA'][$region] ?? 'BAT';
    $table = strtolower($region) . '_businesses';
    $result = $conn->query("SELECT account_number FROM $table ORDER BY account_number DESC LIMIT 1");
    if (!$result) {
        error_log("Database error in generateAccountNumber: " . $conn->error);
        return $prefix . '001';
    }
    $new_number = $result->num_rows > 0 ? str_pad((int)substr($result->fetch_assoc()['account_number'], 3) + 1, 3, '0', STR_PAD_LEFT) : '001';
    return $prefix . $new_number;
}

function generateDigitalAddress($conn) {
    $api_url = "https://api.ghanapostgps.com/v2/PublicGPGPSAPI.aspx";
    $authorization = "QW5kcm9pZEtleTpTV3RsYm01aFFGWnZhMkZqYjIwMFZRPT0=";

    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    error_log("Received coordinates - Latitude: $latitude, Longitude: $longitude");

    if (!$latitude || !$longitude) {
        error_log("Missing latitude/longitude");
        return 'GH-000-0000';
    }

    $payload = [
        'asaaseUser' => 'SWtlbm5hQFZva2Fjb200VQ==',
        'languageCode' => 'en',
        'language' => 'English',
        'deviceId' => 'AndroidKey',
        'androidCert' => '49:DD:00:18:04:D3:47:D0:77:44:A0:B3:93:47:4F:BE:B6:7E:D7:67',
        'androidPackage' => 'com.ghanapostgps.ghanapost',
        'countryName' => 'Ghana',
        'country' => 'GH',
        'latitude' => $latitude,
        'longitude' => $longitude,
        'action' => 'generateAddress'
    ];

    $query_string = http_build_query($payload);
    $full_url = "$api_url?$query_string";
    error_log("Sending GET request to GhanaPost GPS API: $full_url");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $full_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authorization"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        error_log("GhanaPost GPS API failed - HTTP Code: $http_code, Error: $curl_error, Response: " . ($response ?: 'No response'));
        return 'GH-000-0000';
    }

    error_log("API Raw Response: " . $response);
    $data = json_decode($response, true);
    if (isset($data['digitalAddress']) && !empty($data['digitalAddress'])) {
        error_log("Generated digital address: " . $data['digitalAddress']);
        return $data['digitalAddress'];
    } else {
        error_log("Invalid API response - " . $response);
        return 'GH-000-0000';
    }
}

// Handle AJAX request for digital address
if (isset($_GET['get_digital_address'])) {
    header('Content-Type: text/plain');
    $digital_address = generateDigitalAddress($conn);
    echo $digital_address;
    error_log("Generated Digital Address: $digital_address");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $business_name = $_POST['business_name'];
    $owner_name = $_POST['owner_name'];
    $business_type = $_POST['business_type'];
    $telephone = $_POST['telephone'];
    $business_category = $_POST['business_category'];
    $location = $_POST['location'];
    $category = $_POST['category'];
    $old_fee = $_POST['old_fee'];
    $previous_payment = $_POST['previous_payment'];
    $arrears = $_POST['arrears'];
    $current_fee = $_POST['current_fee'];
    $amount_payable = $_POST['amount_payable'];
    $batch = $_POST['batch'];
    $status = $_POST['status'];
    $digital_address = $_POST['digital_address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $region = $_POST['region'] ?? 'Battor';
    $table = strtolower($region) . '_businesses';
    $year = date('Y');

    if (isset($_POST['submit'])) {
        $account_number = generateAccountNumber($conn, $region);
        $stmt = $conn->prepare("INSERT INTO $table (account_number, business_name, owner_name, business_type, telephone, business_category, location, category, old_fee, previous_payment, arrears, current_fee, amount_payable, batch, status, digital_address, latitude, longitude, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssddddssssdds", $account_number, $business_name, $owner_name, $business_type, $telephone, $business_category, $location, $category, $old_fee, $previous_payment, $arrears, $current_fee, $amount_payable, $batch, $status, $digital_address, $latitude, $longitude, $year);
        if ($stmt->execute()) {
            echo "<script>alert('Business added successfully! Account Number: $account_number');</script>";
        } else {
            $message = "Error: Account number conflict or database issue.";
            error_log("Insert error: " . $stmt->error);
        }
    } elseif (isset($_POST['update'])) {
        $account_number = $_POST['account_number'];
        $stmt = $conn->prepare("UPDATE $table SET business_name=?, owner_name=?, business_type=?, telephone=?, business_category=?, location=?, category=?, old_fee=?, previous_payment=?, arrears=?, current_fee=?, amount_payable=?, batch=?, status=?, digital_address=?, latitude=?, longitude=?, year=? WHERE account_number=?");
        $stmt->bind_param("ssssssssddddssssddss", $business_name, $owner_name, $business_type, $telephone, $business_category, $location, $category, $old_fee, $previous_payment, $arrears, $current_fee, $amount_payable, $batch, $status, $digital_address, $latitude, $longitude, $year, $account_number);
        if ($stmt->execute()) {
            $message = $stmt->affected_rows > 0 ? "Business updated successfully!" : "No business found with that account number.";
        } else {
            $message = "Error updating business.";
            error_log("Update error: " . $stmt->error);
        }
    } elseif (isset($_POST['delete'])) {
        $account_number = $_POST['account_number'];
        $stmt = $conn->prepare("DELETE FROM $table WHERE account_number=?");
        $stmt->bind_param("s", $account_number);
        if ($stmt->execute()) {
            $message = $stmt->affected_rows > 0 ? "Business deleted successfully!" : "No business found with that account number.";
        } else {
            $message = "Error deleting business.";
            error_log("Delete error: " . $stmt->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Manage Business</title>
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
                <a href="dashboard_budget.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <a href="#" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">➕</span> Manage Business
                </a>
                <a href="business_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏢</span> View Businesses
                </a>
                <a href="../index.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Manage Business</h1>
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-gray-700">
                <form method="POST" id="businessForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="text" name="account_number" placeholder="Account Number (for Update/Delete)" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200" <?php echo isset($_POST['submit']) ? 'disabled' : ''; ?>>
                    <input type="text" name="business_name" placeholder="Business Name" required class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                    <input type="text" name="owner_name" placeholder="Owner Name" required class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                    <input type="text" name="business_type" placeholder="Business Type" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                    <input type="text" name="telephone" placeholder="Telephone" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                    <select name="business_category" required class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        <option value="" disabled selected>Select Business Category</option>
                        <option value="Agriculture">Agriculture</option>
                        <option value="Industry">Industry</option>
                        <option value="Services">Services</option>
                    </select>
                    <input type="text" name="location" id="location" placeholder="Location (Auto-Filled)" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <select name="category" required class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        <option value="" disabled selected>Select Category</option>
                        <option value="Large Scale">Large Scale</option>
                        <option value="Medium Scale">Medium Scale</option>
                        <option value="Small Scale">Small Scale</option>
                    </select>
                    <input type="number" name="old_fee" id="old_fee" placeholder="Old Fee" step="0.01" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200" oninput="calculate()">
                    <input type="number" name="previous_payment" id="previous_payment" placeholder="Previous Payment" step="0.01" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200" oninput="calculate()">
                    <input type="number" name="arrears" id="arrears" placeholder="Arrears (Auto-Calculated)" step="0.01" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <input type="number" name="current_fee" id="current_fee" placeholder="Current Fee" step="0.01" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200" oninput="calculate()">
                    <input type="number" name="amount_payable" id="amount_payable" placeholder="Amount Payable (Auto-Calculated)" step="0.01" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <input type="text" name="batch" placeholder="Batch" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                    <select name="status" required class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200">
                        <option value="" disabled selected>Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <input type="text" name="digital_address" id="digital_address" placeholder="Digital Address (Auto-Generated)" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <input type="number" name="latitude" id="latitude" placeholder="Latitude (Auto-Filled)" step="0.0000001" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <input type="number" name="longitude" id="longitude" placeholder="Longitude (Auto-Filled)" step="0.0000001" class="p-4 bg-gray-600 border border-gray-500 rounded-lg text-gray-300" readonly>
                    <select name="region" id="region" class="p-4 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-200 md:col-span-2">
                        <option value="Battor" selected>Battor</option>
                        <option value="Mepe">Mepe</option>
                        <option value="Juapong">Juapong</option>
                        <option value="Accra">Accra</option>
                    </select>
                    <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <button type="submit" name="submit" class="py-4 bg-blue-700 text-white rounded-xl font-bold hover:bg-blue-800 transition duration-300 shadow-md">Submit</button>
                        <button type="submit" name="update" class="py-4 bg-purple-700 text-white rounded-xl font-bold hover:bg-purple-800 transition duration-300 shadow-md">Update</button>
                        <button type="button" onclick="document.getElementById('businessForm').reset(); calculate();" class="py-4 bg-gray-600 text-white rounded-xl font-bold hover:bg-gray-700 transition duration-300 shadow-md">Clear</button>
                        <button type="submit" name="delete" class="py-4 bg-red-700 text-white rounded-xl font-bold hover:bg-red-800 transition duration-300 shadow-md">Delete</button>
                    </div>
                </form>
                <?php if ($message): ?>
                    <p class="text-center mt-6 <?php echo strpos($message, 'Error') === false && strpos($message, 'No business') === false ? 'text-green-400' : 'text-red-400'; ?>"><?php echo $message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function calculate() {
            const oldFee = parseFloat(document.getElementById('old_fee').value) || 0;
            const prevPayment = parseFloat(document.getElementById('previous_payment').value) || 0;
            const currentFee = parseFloat(document.getElementById('current_fee').value) || 0;
            const arrears = oldFee - prevPayment;
            const amountPayable = arrears + currentFee;
            document.getElementById('arrears').value = arrears.toFixed(2);
            document.getElementById('amount_payable').value = amountPayable.toFixed(2);
        }

        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            console.log('Got location - Lat:', lat, 'Lng:', lng);
                            document.getElementById('latitude').value = lat.toFixed(7);
                            document.getElementById('longitude').value = lng.toFixed(7);
                            reverseGeocode(lat, lng);
                            resolve({ lat, lng });
                        },
                        (error) => {
                            console.error('Geolocation error:', error.message);
                            resetLocationFields();
                            reject(error);
                        }
                    );
                } else {
                    alert('Geolocation is not supported by this browser.');
                    resetLocationFields();
                    reject(new Error('Geolocation not supported'));
                }
            });
        }

        async function fetchDigitalAddress(lat, lng) {
            try {
                console.log('Fetching digital address with Lat:', lat, 'Lng:', lng);
                const formData = new FormData();
                formData.append('latitude', lat);
                formData.append('longitude', lng);
                const response = await fetch('/quickbil/pages/business_add.php?get_digital_address=true', {
                    method: 'POST', // Switch to POST to send coordinates
                    body: formData,
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                const data = await response.text();
                console.log('Fetched Digital Address:', data);
                document.getElementById('digital_address').value = data;
            } catch (error) {
                console.error('Error fetching digital address:', error);
                document.getElementById('digital_address').value = 'Error: ' + error.message;
            }
        }

        function reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
            geocoder.geocode({ 'location': latlng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    console.log('Reverse Geocode Result:', results[0].formatted_address);
                    document.getElementById('location').value = results[0].formatted_address;
                } else {
                    document.getElementById('location').value = 'Unknown Location';
                    console.log('Reverse geocode failed:', status);
                }
            });
        }

        function resetLocationFields() {
            document.getElementById('location').value = '';
            document.getElementById('digital_address').value = '';
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        }

        window.onload = () => {
            getCurrentLocation()
                .then(({ lat, lng }) => {
                    fetchDigitalAddress(lat, lng);
                })
                .catch((error) => {
                    console.error('Initial location fetch failed:', error);
                    document.getElementById('digital_address').value = 'Error: Unable to get location';
                });
        };
    </script>
</body>
</html>