<?php
include '../includes/config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log session variables to check if they are set
error_log("Session variables in business_map.php: user_id=" . ($_SESSION['user_id'] ?? 'not set') . ", role=" . ($_SESSION['role'] ?? 'not set'));

// Check session and role (allow Admin, Budget, and Finance)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Budget', 'Finance'])) {
    error_log("Redirecting to login.php from business_map.php: user_id or role not set or invalid role");
    header("Location: login.php");
    exit();
}

// Fetch businesses from all regions
$businesses = [];
$tables = ['battor_businesses', 'mepe_businesses', 'juapong_businesses'];
$regions = ['Battor', 'Mepe', 'Juapong'];

foreach ($tables as $index => $table) {
    $region = $regions[$index];
    $result = $conn->query("SELECT business_name, latitude, longitude FROM $table WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['region'] = $region;
            $businesses[] = $row;
        }
    } else {
        error_log("Error fetching businesses from $table: " . $conn->error);
    }
}

// Convert businesses array to JSON for JavaScript
$businesses_json = json_encode($businesses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickbil - Business Map</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWhWvIreFKsb8iKv4Tun5Zr5JLT8c4kB4&libraries=places"></script>
    <style>
        #map {
            height: 600px;
            width: 100%;
            border-radius: 1rem;
            border: 2px solid #4A5568;
        }
        .business-list {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen font-inter">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-gray-900 w-72 p-6 fixed h-full shadow-2xl border-r border-gray-800">
            <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-12">Quickbil</h2>
            <nav class="space-y-8">
                <a href="<?php echo $_SESSION['role'] === 'Admin' ? 'dashboard_admin.php' : ($_SESSION['role'] === 'Finance' ? 'dashboard_finance.php' : 'dashboard_budget.php'); ?>" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏠</span> Dashboard
                </a>
                <?php if ($_SESSION['role'] === 'Budget'): ?>
                    <a href="business_add.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                        <span class="mr-3">➕</span> Add Business
                    </a>
                <?php endif; ?>
                <a href="business_view.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🏢</span> View Businesses
                </a>
                <a href="#" class="flex items-center py-3 px-4 bg-blue-700 rounded-xl hover:bg-blue-800 transition duration-300 text-lg font-semibold shadow-md">
                    <span class="mr-3">🗺️</span> Business Map
                </a>
                <a href="../index.php" class="flex items-center py-3 px-4 hover:bg-gray-800 rounded-xl transition duration-300 text-lg font-semibold">
                    <span class="mr-3">🚪</span> Logout
                </a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-12 ml-72">
            <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 mb-10 drop-shadow-lg">Business Map</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Map Container -->
                <div class="md:col-span-2">
                    <div id="map" class="bg-gray-800 bg-opacity-80 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-700"></div>
                </div>
                <!-- Business List -->
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md p-6 rounded-2xl shadow-2xl border border-gray-700 business-list">
                    <h3 class="text-2xl font-bold text-gray-200 mb-4">Businesses</h3>
                    <ul class="space-y-4">
                        <?php foreach ($businesses as $business): ?>
                            <li class="p-4 bg-gray-700 rounded-lg hover:bg-gray-600 transition duration-300">
                                <p class="text-lg font-semibold text-blue-400"><?php echo htmlspecialchars($business['business_name']); ?></p>
                                <p class="text-gray-400">Region: <?php echo htmlspecialchars($business['region']); ?></p>
                                <p class="text-gray-400">Lat: <?php echo htmlspecialchars($business['latitude']); ?>, Lng: <?php echo htmlspecialchars($business['longitude']); ?></p>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($businesses)): ?>
                            <li class="text-gray-400">No businesses found with location data.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 p-4 text-center text-gray-400">
        <p>© <?php echo date('Y'); ?> Quickbil. Powered by KabTech Consulting.</p>
    </footer>
    <script>
        let map;
        let markers = [];

        function initMap() {
            // Default center (central point in Ghana)
            const defaultCenter = { lat: 7.9465, lng: -1.0232 };
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: defaultCenter,
                styles: [
                    { elementType: "geometry", stylers: [{ color: "#212121" }] },
                    { elementType: "labels.text.stroke", stylers: [{ color: "#212121" }] },
                    { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
                    { featureType: "administrative.locality", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                    { featureType: "poi", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                    { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#263c3f" }] },
                    { featureType: "poi.park", elementType: "labels.text.fill", stylers: [{ color: "#6b9a76" }] },
                    { featureType: "road", elementType: "geometry", stylers: [{ color: "#38414e" }] },
                    { featureType: "road", elementType: "geometry.stroke", stylers: [{ color: "#212a37" }] },
                    { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#9ca5b3" }] },
                    { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#746855" }] },
                    { featureType: "road.highway", elementType: "geometry.stroke", stylers: [{ color: "#1f2835" }] },
                    { featureType: "road.highway", elementType: "labels.text.fill", stylers: [{ color: "#f3d19c" }] },
                    { featureType: "transit", elementType: "geometry", stylers: [{ color: "#2f3948" }] },
                    { featureType: "transit.station", elementType: "labels.text.fill", stylers: [{ color: "#d59563" }] },
                    { featureType: "water", elementType: "geometry", stylers: [{ color: "#17263c" }] },
                    { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#515c6d" }] },
                    { featureType: "water", elementType: "labels.text.stroke", stylers: [{ color: "#17263c" }] }
                ]
            });

            // Add markers for each business
            const businesses = <?php echo $businesses_json; ?>;
            businesses.forEach(business => {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(business.latitude), lng: parseFloat(business.longitude) },
                    map: map,
                    title: business.business_name,
                    icon: {
                        url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" // Different color per region can be added
                    }
                });

                // Add info window
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="p-2">
                            <h3 class="text-lg font-bold">${business.business_name}</h3>
                            <p>Region: ${business.region}</p>
                            <p>Lat: ${business.latitude}, Lng: ${business.longitude}</p>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
            });

            // Fit map to bounds if there are markers
            if (businesses.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                businesses.forEach(business => {
                    bounds.extend({ lat: parseFloat(business.latitude), lng: parseFloat(business.longitude) });
                });
                map.fitBounds(bounds);
            }
        }

        // Initialize the map when the page loads
        window.onload = () => {
            initMap();
        };
    </script>
</body>
</html>