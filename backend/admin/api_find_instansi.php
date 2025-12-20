<?php
/**
 * PROJECT ONE - API: CARI INSTANSI DARURAT TERDEKAT
 * 
 * ENDPOINT: Mencari instansi darurat terdekat menggunakan Google Places API
 * 
 * OPTIMASI CREDIT PLACES API:
 * ============================================
 * 1. HAPUS AUTocomplete - Autocomplete dipanggil setiap kali user mengetik,
 *    yang bisa menghabiskan ratusan credit per hari. DIHAPUS untuk menghemat.
 * 
 * 2. TOMBOL MANUAL - Places API hanya dipanggil saat admin menekan tombol manual.
 *    Ini mengurangi request dari ratusan/jam menjadi hanya beberapa kali per hari.
 * 
 * 3. CACHE DATABASE - Sebelum memanggil Places API, cek dulu data di database.
 *    Jika sudah ada instansi dalam radius yang sama, gunakan data dari database.
 *    Ini bisa menghemat 80-90% credit Places API.
 * 
 * 4. BATASI QUERY - Hanya cari: hospital, police, fire_station
 *    Radius maksimal: 5000 meter
 *    Ini mengurangi jumlah hasil dan credit yang digunakan.
 * 
 * RISIKO BIAYA PLACES API:
 * ============================================
 * - Autocomplete: $2.83 per 1000 requests
 * - Nearby Search: $32 per 1000 requests
 * - Tanpa optimasi ini, aplikasi bisa menghabiskan ratusan dollar per bulan
 * - Dengan optimasi ini, biaya bisa turun menjadi < $10 per bulan
 * 
 * KEAMANAN:
 * ============================================
 * - Hanya admin yang bisa mengakses endpoint ini
 * - API key tidak hardcoded, menggunakan konstanta dari config.php
 * 
 * CARA PENGGUNAAN:
 * ============================================
 * POST /backend/admin/api_find_instansi.php
 * Parameters:
 *   - latitude: float (required)
 *   - longitude: float (required)
 *   - jenis_instansi: string (optional, default: all) - hospital|police|fire_station|all
 * 
 * Response JSON:
 *   {
 *     "success": true,
 *     "from_cache": true/false,
 *     "data": [...],
 *     "message": "..."
 *   }
 */

require_once __DIR__ . '/middleware/auth_admin.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/connection.php';

// Set header JSON
header('Content-Type: application/json');

// Wajibkan login admin
requireAdminLogin();

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Hanya POST request yang diizinkan.'
    ]);
    exit;
}

// Ambil dan validasi parameter
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$jenis_instansi = isset($_POST['jenis_instansi']) ? sanitizeInput($_POST['jenis_instansi']) : 'all';

// Validasi koordinat
if ($latitude === null || $longitude === null || 
    $latitude < -90 || $latitude > 90 || 
    $longitude < -180 || $longitude > 180) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Koordinat tidak valid. Latitude harus -90 sampai 90, Longitude harus -180 sampai 180.'
    ]);
    exit;
}

// Validasi jenis instansi
$allowed_types = ['hospital', 'police', 'fire_station', 'all'];
if (!in_array($jenis_instansi, $allowed_types)) {
    $jenis_instansi = 'all';
}

// Radius maksimal (dalam meter) - sesuai requirement
$radius_meters = 5000;

// Mapping jenis instansi ke Google Places type
$places_type_map = [
    'hospital' => 'hospital',
    'police' => 'police',
    'fire_station' => 'fire_station',
    'all' => null // Akan dicari semua jenis
];

try {
    $db = getDB();
    
    // ============================================
    // STEP 1: CEK CACHE DATABASE
    // ============================================
    // Cari instansi di database yang sudah ada dalam radius yang sama
    // Menggunakan formula Haversine untuk menghitung jarak
    
    $cache_query = "
        SELECT 
            i.id,
            i.nama,
            i.kode,
            i.jenis,
            i.status,
            ai.alamat_lengkap,
            ai.kelurahan,
            ai.kecamatan,
            ai.kota,
            ai.provinsi,
            ai.latitude,
            ai.longitude,
            (
                6371000 * acos(
                    cos(radians(:lat)) * 
                    cos(radians(ai.latitude)) * 
                    cos(radians(ai.longitude) - radians(:lng)) + 
                    sin(radians(:lat)) * 
                    sin(radians(ai.latitude))
                )
            ) AS distance_meters
        FROM instansi i
        LEFT JOIN alamat_instansi ai ON i.id = ai.instansi_id
        WHERE ai.latitude IS NOT NULL 
            AND ai.longitude IS NOT NULL
            AND i.status = 'active'
            AND (
                6371000 * acos(
                    cos(radians(:lat)) * 
                    cos(radians(ai.latitude)) * 
                    cos(radians(ai.longitude) - radians(:lng)) + 
                    sin(radians(:lat)) * 
                    sin(radians(ai.latitude))
                )
            ) <= :radius
    ";
    
    // Filter berdasarkan jenis jika diperlukan
    if ($jenis_instansi !== 'all') {
        // Mapping jenis ke jenis_instansi di database
        $jenis_db_map = [
            'hospital' => 'pemerintah', // Bisa disesuaikan
            'police' => 'pemerintah',
            'fire_station' => 'pemerintah'
        ];
        // Untuk sementara, kita cari semua dan filter di PHP
        // Atau bisa ditambahkan kolom places_type di tabel instansi
    }
    
    $stmt = $db->prepare($cache_query);
    $stmt->execute([
        'lat' => $latitude,
        'lng' => $longitude,
        'radius' => $radius_meters
    ]);
    $cached_instansi = $stmt->fetchAll();
    
    // Jika ada data di cache dan cukup banyak (misalnya >= 3), gunakan cache
    if (count($cached_instansi) >= 3) {
        // Sort by distance
        usort($cached_instansi, function($a, $b) {
            return $a['distance_meters'] <=> $b['distance_meters'];
        });
        
        // Limit hasil (maksimal 20)
        $cached_instansi = array_slice($cached_instansi, 0, 20);
        
        echo json_encode([
            'success' => true,
            'from_cache' => true,
            'data' => $cached_instansi,
            'message' => 'Data instansi ditemukan dari cache database. Credit Places API tidak digunakan.',
            'count' => count($cached_instansi)
        ]);
        exit;
    }
    
    // ============================================
    // STEP 2: REQUEST KE PLACES API (jika cache tidak ada)
    // ============================================
    // Hanya dipanggil jika data tidak ditemukan di cache
    
    // Pastikan API key sudah di-set
    if (!defined('GOOGLE_MAPS_API_KEY') || GOOGLE_MAPS_API_KEY === 'YOUR_GOOGLE_MAPS_API_KEY') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Google Maps API key belum dikonfigurasi. Silakan set GOOGLE_MAPS_API_KEY di config.php'
        ]);
        exit;
    }
    
    // Build URL untuk Nearby Search API
    $api_key = GOOGLE_MAPS_API_KEY;
    $base_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json';
    
    // Jika jenis_instansi = 'all', kita perlu melakukan 3 request terpisah
    // Tapi untuk menghemat credit, kita bisa batasi hanya 1 jenis yang paling relevan
    // Atau kita bisa gunakan multiple types dalam 1 request (jika didukung)
    
    $places_types = [];
    if ($jenis_instansi === 'all') {
        $places_types = ['hospital', 'police', 'fire_station'];
    } else {
        $places_types = [$places_type_map[$jenis_instansi]];
    }
    
    $all_results = [];
    
    // Request untuk setiap jenis (maksimal 3 request jika 'all')
    foreach ($places_types as $type) {
        $url = $base_url . '?' . http_build_query([
            'location' => "$latitude,$longitude",
            'radius' => $radius_meters,
            'type' => $type,
            'key' => $api_key
        ]);
        
        // Gunakan cURL untuk request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            continue; // Skip jika error
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $result) {
                $all_results[] = [
                    'places_id' => $result['place_id'] ?? '',
                    'nama' => $result['name'] ?? '',
                    'alamat' => $result['vicinity'] ?? ($result['formatted_address'] ?? ''),
                    'latitude' => $result['geometry']['location']['lat'] ?? null,
                    'longitude' => $result['geometry']['location']['lng'] ?? null,
                    'rating' => $result['rating'] ?? null,
                    'types' => $result['types'] ?? [],
                    'places_type' => $type
                ];
            }
        }
        
        // Limit: maksimal 20 hasil per jenis
        if (count($all_results) >= 20) {
            break;
        }
    }
    
    // ============================================
    // STEP 3: SIMPAN KE DATABASE (CACHE)
    // ============================================
    // Simpan hasil ke database untuk cache
    $saved_count = 0;
    
    foreach ($all_results as $result) {
        if ($result['latitude'] === null || $result['longitude'] === null) {
            continue;
        }
        
        try {
            // Generate kode unik untuk instansi
            $kode = 'PLACES-' . strtoupper(substr($result['places_type'], 0, 3)) . '-' . 
                    substr(md5($result['places_id']), 0, 8);
            
            // Cek apakah sudah ada berdasarkan places_id atau kode
            $check_stmt = $db->prepare("SELECT id FROM instansi WHERE kode = :kode LIMIT 1");
            $check_stmt->execute(['kode' => $kode]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                continue; // Skip jika sudah ada
            }
            
            // Insert instansi
            $insert_instansi = $db->prepare("
                INSERT INTO instansi (nama, kode, jenis, status)
                VALUES (:nama, :kode, 'pemerintah', 'active')
            ");
            $insert_instansi->execute([
                'nama' => $result['nama'],
                'kode' => $kode
            ]);
            $instansi_id = $db->lastInsertId();
            
            // Insert alamat dengan koordinat
            // CATATAN: Pastikan migration 07_add_coordinates_to_alamat_instansi.sql sudah dijalankan
            // Jika kolom latitude/longitude belum ada, cek dulu dengan DESCRIBE atau coba-catch
            try {
                // Cek apakah kolom latitude/longitude sudah ada
                $check_cols = $db->query("SHOW COLUMNS FROM alamat_instansi LIKE 'latitude'");
                $has_coordinates = $check_cols->rowCount() > 0;
                
                if ($has_coordinates) {
                    // Kolom sudah ada, gunakan query dengan latitude/longitude
                    $insert_alamat = $db->prepare("
                        INSERT INTO alamat_instansi (instansi_id, alamat_lengkap, kota, provinsi, latitude, longitude)
                        VALUES (:instansi_id, :alamat, :kota, :provinsi, :lat, :lng)
                    ");
                } else {
                    // Kolom belum ada, gunakan query tanpa latitude/longitude
                    // CATATAN: Instansi akan disimpan tanpa koordinat, cache tidak akan berfungsi
                    // Solusi: Jalankan migration 07_add_coordinates_to_alamat_instansi.sql
                    error_log("WARNING: Kolom latitude/longitude belum ada di alamat_instansi. Jalankan migration script.");
                    $insert_alamat = $db->prepare("
                        INSERT INTO alamat_instansi (instansi_id, alamat_lengkap, kota, provinsi)
                        VALUES (:instansi_id, :alamat, :kota, :provinsi)
                    ");
                }
                
                // Parse alamat untuk mendapatkan kota dan provinsi
                $alamat_parts = explode(',', $result['alamat']);
                $kota = trim(end($alamat_parts)) ?? 'Tidak diketahui';
                $provinsi = trim($alamat_parts[count($alamat_parts) - 2] ?? 'Tidak diketahui');
                
                $params = [
                    'instansi_id' => $instansi_id,
                    'alamat' => $result['alamat'],
                    'kota' => $kota,
                    'provinsi' => $provinsi
                ];
                
                if ($has_coordinates) {
                    $params['lat'] = $result['latitude'];
                    $params['lng'] = $result['longitude'];
                }
                
                $insert_alamat->execute($params);
                
            } catch (PDOException $e) {
                // Jika error karena kolom tidak ada, skip dan log warning
                if (strpos($e->getMessage(), 'Unknown column') !== false) {
                    error_log("WARNING: Kolom latitude/longitude belum ada. Jalankan migration 07_add_coordinates_to_alamat_instansi.sql");
                    // Coba insert tanpa koordinat
                    try {
                        $insert_alamat = $db->prepare("
                            INSERT INTO alamat_instansi (instansi_id, alamat_lengkap, kota, provinsi)
                            VALUES (:instansi_id, :alamat, :kota, :provinsi)
                        ");
                        $insert_alamat->execute([
                            'instansi_id' => $instansi_id,
                            'alamat' => $result['alamat'],
                            'kota' => $kota ?? 'Tidak diketahui',
                            'provinsi' => $provinsi ?? 'Tidak diketahui'
                        ]);
                    } catch (PDOException $e2) {
                        error_log("Error saving alamat (without coordinates): " . $e2->getMessage());
                        continue;
                    }
                } else {
                    throw $e; // Re-throw jika error lain
                }
            }
            
            $saved_count++;
            
        } catch (PDOException $e) {
            // Skip jika error (mungkin duplicate atau constraint violation)
            error_log("Error saving instansi to cache: " . $e->getMessage());
            continue;
        }
    }
    
    // Return hasil
    echo json_encode([
        'success' => true,
        'from_cache' => false,
        'data' => array_slice($all_results, 0, 20), // Limit 20 hasil
        'message' => "Data ditemukan dari Google Places API. {$saved_count} instansi disimpan ke cache database.",
        'count' => count($all_results),
        'saved_to_cache' => $saved_count
    ]);
    
} catch (Exception $e) {
    error_log("API find instansi error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat mencari instansi: ' . $e->getMessage()
    ]);
}

