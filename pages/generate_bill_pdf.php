<?php
include '../includes/config.php';
require_once '../vendor/dompdf/autoload.inc.php'; // Adjust path if needed

use Dompdf\Dompdf;

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check session and role (allow only Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    error_log("Redirecting to login.php from generate_bill_pdf.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Determine if generating all bills or filtered bills
$generate_all = isset($_POST['generate_all']);
$region_filter = isset($_POST['region']) ? trim($_POST['region']) : '';
$batch = isset($_POST['batch']) ? trim($_POST['batch']) : '';
$account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : '';

// Fetch businesses based on conditions
$businesses = [];
$regions = ['battor', 'mepe', 'juapong'];

if ($generate_all) {
    // Fetch all businesses from all regions
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
} elseif ($region_filter) {
    // Fetch businesses from the selected region with conditions
    $table = strtolower($region_filter) . '_businesses';
    $query = "SELECT account_number, business_name, owner_name, business_type, telephone, business_category, category, old_fee, previous_payment, arrears, current_fee, amount_payable, batch, status, location FROM $table WHERE 1=1";
    
    if ($batch) {
        $batch = $conn->real_escape_string($batch);
        $query .= " AND batch = '$batch'";
    } elseif ($account_number) {
        $account_number = $conn->real_escape_string($account_number);
        $query .= " AND account_number = '$account_number'";
    }
    
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['region'] = ucfirst($region_filter);
            $businesses[] = $row;
        }
    } else {
        error_log("Error fetching businesses from $table: " . $conn->error);
    }
}

// Generate PDF
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .bill-container { border: 1px solid #000; padding: 20px; max-width: 800px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 14px; }
        .details { margin-bottom: 20px; font-size: 14px; }
        .details p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="header">
            <h1>NORTH TONGU DISTRICT ASSEMBLY</h1>
            <p>+233545041428</p>
            <p>BUSINESS OPERATING PERMIT</p>
        </div>
        <div class="details">';

if (!empty($businesses)) {
    foreach ($businesses as $business) {
        $html .= '
            <p><strong>BUSINESS NAME</strong><br>' . htmlspecialchars($business['business_name']) . '</p>
            <p><strong>LOCATION</strong><br>' . htmlspecialchars($business['location']) . '</p>
            <p><strong>BUSINESS TYPE</strong><br>' . htmlspecialchars($business['business_type']) . '</p>
            <p><strong>ACCOUNT NUMBER</strong><br>' . htmlspecialchars($business['account_number']) . '</p>
            <p><strong>BATCH</strong><br>' . htmlspecialchars($business['batch']) . '</p>
            <p><strong>AMOUNT PAYABLE</strong><br>₵' . number_format($business['amount_payable'], 2) . '</p>
            <p><strong>STATUS</strong><br>' . htmlspecialchars($business['status']) . '</p>
            <hr style="margin: 20px 0;">';
    }
} else {
    $html .= '<p>No businesses found matching the filter criteria.</p>';
}

$html .= '
        </div>
        <table>
            <thead>
                <tr>
                    <th>Account Number</th>
                    <th>Business Name</th>
                    <th>Owner Name</th>
                    <th>Region</th>
                    <th>Business Type</th>
                    <th>Batch</th>
                    <th>Amount Payable</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($businesses)) {
    foreach ($businesses as $business) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($business['account_number']) . '</td>
                <td>' . htmlspecialchars($business['business_name']) . '</td>
                <td>' . htmlspecialchars($business['owner_name']) . '</td>
                <td>' . htmlspecialchars($business['region']) . '</td>
                <td>' . htmlspecialchars($business['business_type']) . '</td>
                <td>' . htmlspecialchars($business['batch']) . '</td>
                <td>₵' . number_format($business['amount_payable'], 2) . '</td>
                <td>' . htmlspecialchars($business['status']) . '</td>
            </tr>';
    }
} else {
    $html .= '<tr><td colspan="8">No data available.</td></tr>';
}

$html .= '
            </tbody>
        </table>
    </div>
</body>
</html>';

// Initialize Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Business_Operating_Permit_" . date('Ymd') . ".pdf", ["Attachment" => true]);
exit();
?>