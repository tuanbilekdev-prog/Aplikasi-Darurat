<?php
/**
 * PROJECT ONE - AI HELPER
 * Helper functions untuk integrasi AI (OpenAI)
 */

/**
 * Generate response suggestion menggunakan OpenAI
 * 
 * @param string $report_title Judul laporan
 * @param string $report_description Deskripsi laporan
 * @param string $report_category Kategori laporan
 * @param string $report_location Lokasi laporan
 * @param string|null $report_status Status laporan saat ini
 * @return array Hasil dengan 'success' dan 'suggestion' atau 'error'
 */
function generateAISuggestion($report_title, $report_description, $report_category, $report_location, $report_status = null) {
    // Check jika OpenAI API key sudah diset
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        return [
            'success' => false,
            'error' => 'OpenAI API key belum dikonfigurasi. Silakan set OPENAI_API_KEY di config.php'
        ];
    }
    
    // Kategori mapping untuk konteks
    $category_map = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    
    $category_label = $category_map[$report_category] ?? $report_category;
    
    // Build prompt untuk OpenAI
    $prompt = "Sebagai admin sistem pelaporan darurat, buatkan catatan penanganan yang profesional dan singkat untuk laporan berikut:\n\n";
    $prompt .= "Judul: " . $report_title . "\n";
    $prompt .= "Kategori: " . $category_label . "\n";
    $prompt .= "Lokasi: " . $report_location . "\n";
    $prompt .= "Deskripsi: " . $report_description . "\n\n";
    
    if ($report_status) {
        $status_map = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'dispatched' => 'Ditugaskan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        $status_label = $status_map[$report_status] ?? $report_status;
        $prompt .= "Status saat ini: " . $status_label . "\n\n";
    }
    
    $prompt .= "Catatan penanganan harus:\n";
    $prompt .= "- Singkat dan jelas (maksimal 150 kata)\n";
    $prompt .= "- Profesional dan sopan\n";
    $prompt .= "- Berisi langkah-langkah penanganan yang akan dilakukan\n";
    $prompt .= "- Menggunakan bahasa Indonesia\n";
    $prompt .= "- Tidak perlu tanda kurung atau format khusus\n\n";
    $prompt .= "Catatan penanganan:";
    
    try {
        // Call OpenAI API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ]);
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah asisten admin sistem pelaporan darurat. Buatkan catatan penanganan yang profesional, singkat, dan jelas dalam bahasa Indonesia.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 300,
            'temperature' => 0.7
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            $error_response = json_decode($response, true);
            $error_message = $error_response['error']['message'] ?? 'Terjadi kesalahan saat memanggil OpenAI API';
            return [
                'success' => false,
                'error' => $error_message
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Format respons tidak valid dari OpenAI API'
            ];
        }
        
        $suggestion = trim($response_data['choices'][0]['message']['content']);
        
        // Clean up suggestion (remove quotes if wrapped)
        $suggestion = preg_replace('/^["\']|["\']$/', '', $suggestion);
        
        return [
            'success' => true,
            'suggestion' => $suggestion
        ];
        
    } catch (Exception $e) {
        error_log("AI Helper Error: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}

/**
 * Fallback suggestion generator (tanpa AI, rule-based)
 * Digunakan jika OpenAI API tidak tersedia
 */
function generateFallbackSuggestion($report_category, $report_description) {
    $category_map = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    
    $category_label = $category_map[$report_category] ?? 'Laporan';
    
    $templates = [
        'kecelakaan' => "Laporan kecelakaan telah diterima dan sedang diproses. Tim akan segera melakukan verifikasi lokasi dan mengirimkan bantuan yang diperlukan. Mohon tetap berada di lokasi yang aman sambil menunggu bantuan tiba.",
        'kebakaran' => "Laporan kebakaran telah diterima dengan prioritas tinggi. Tim pemadam kebakaran akan segera dikirim ke lokasi. Mohon untuk segera mengungsi dari lokasi dan menghubungi nomor darurat jika situasi memburuk.",
        'medis' => "Laporan darurat medis telah diterima. Tim medis akan segera dikirim ke lokasi. Sementara menunggu bantuan tiba, mohon berikan pertolongan pertama jika memungkinkan dan aman untuk dilakukan.",
        'kejahatan' => "Laporan kejahatan telah diterima dan sedang diproses oleh pihak berwenang. Tim keamanan akan segera dikirim ke lokasi. Mohon untuk tidak melakukan tindakan yang dapat membahayakan diri sendiri.",
        'bencana' => "Laporan bencana alam telah diterima dengan prioritas tinggi. Tim penanganan bencana akan segera dikirim ke lokasi. Mohon untuk segera mengungsi ke tempat yang aman jika diperlukan.",
        'lainnya' => "Laporan telah diterima dan sedang diproses. Tim akan melakukan verifikasi lebih lanjut dan mengambil langkah penanganan yang sesuai. Kami akan memberikan update segera setelah informasi tersedia."
    ];
    
    $suggestion = $templates[$report_category] ?? $templates['lainnya'];
    
    return [
        'success' => true,
        'suggestion' => $suggestion,
        'fallback' => true
    ];
}
