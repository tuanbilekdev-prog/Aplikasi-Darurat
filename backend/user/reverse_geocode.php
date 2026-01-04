<?php
/**
 * PROJECT ONE - REVERSE GEOCODE PROXY
 * Endpoint untuk reverse geocoding (koordinat ke alamat)
 * Menggunakan Nominatim API (OpenStreetMap)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Periksa request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Ambil parameter
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

// Validasi parameter
if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid coordinates']);
    exit();
}

// URL Nominatim API
$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1&accept-language=id";

// Request ke Nominatim menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10 detik
curl_setopt($ch, CURLOPT_USERAGENT, 'Project One Emergency App'); // User-Agent untuk Nominatim
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept-Language: id,en'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle error
if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch address: ' . $error]);
    exit();
}

if ($http_code !== 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'Nominatim API error', 'code' => $http_code]);
    exit();
}

// Parse response
$data = json_decode($response, true);

if (!$data) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from Nominatim']);
    exit();
}

// Return data
echo json_encode($data);
exit();

?>

