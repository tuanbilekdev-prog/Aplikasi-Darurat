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

/**
 * Generate deskripsi laporan dari gambar menggunakan OpenAI Vision API
 * 
 * @param string $title Judul laporan
 * @param string $category Kategori laporan
 * @param array $photo File upload dari $_FILES
 * @return array Hasil dengan 'success' dan 'description' atau 'error'
 */
function generateAIDescriptionFromImage($title, $category, $photo) {
    // Check jika OpenAI API key sudah diset
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        // Fallback ke text-based generation
        return generateAIDescriptionFromText($title, $category);
    }
    
    // Validasi file
    if ($photo['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'Error saat upload foto'
        ];
    }
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($photo['type'], $allowed_types)) {
        return [
            'success' => false,
            'error' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF'
        ];
    }
    
    // Validasi ukuran (max 5MB)
    if ($photo['size'] > 5 * 1024 * 1024) {
        return [
            'success' => false,
            'error' => 'Ukuran file terlalu besar. Maksimal 5MB'
        ];
    }
    
    // Baca file dan convert ke base64
    $image_data = file_get_contents($photo['tmp_name']);
    $base64_image = base64_encode($image_data);
    
    // Deteksi MIME type
    $mime_type = $photo['type'];
    if (!$mime_type) {
        // Fallback berdasarkan extension
        $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
        $mime_map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        $mime_type = $mime_map[$ext] ?? 'image/jpeg';
    }
    
    // Kategori mapping
    $category_map = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    $category_label = $category_map[$category] ?? $category;
    
    // Build prompt untuk Vision API
    $prompt = "Sebagai sistem pelaporan darurat, analisis gambar ini dan buatkan deskripsi laporan darurat yang detail dalam bahasa Indonesia.\n\n";
    $prompt .= "Konteks: Laporan dengan judul \"" . $title . "\" dalam kategori " . $category_label . ".\n\n";
    $prompt .= "Buatkan deskripsi yang:\n";
    $prompt .= "- Menjelaskan apa yang terlihat di gambar\n";
    $prompt .= "- Menjelaskan kondisi kejadian darurat\n";
    $prompt .= "- Menjelaskan tingkat urgensi\n";
    $prompt .= "- Singkat dan jelas (maksimal 200 kata)\n";
    $prompt .= "- Menggunakan bahasa Indonesia yang baik dan benar\n";
    $prompt .= "- HANYA berisi deskripsi kejadian, TIDAK menyebutkan judul atau kategori\n";
    $prompt .= "- Tidak perlu tanda kurung atau format khusus\n";
    $prompt .= "- Langsung mulai dengan menjelaskan kejadian yang terlihat\n\n";
    $prompt .= "Deskripsi laporan (langsung mulai dengan kejadian, tanpa menyebut judul/kategori):";
    
    try {
        // Call OpenAI Vision API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ]);
        
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah sistem pelaporan darurat. Analisis gambar dan buatkan deskripsi laporan darurat yang detail, jelas, dan profesional dalam bahasa Indonesia. Deskripsi harus langsung menjelaskan kejadian tanpa menyebutkan judul atau kategori laporan.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:' . $mime_type . ';base64,' . $base64_image
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 500,
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
            
            // Fallback ke text-based generation
            return generateAIDescriptionFromText($title, $category);
        }
        
        $response_data = json_decode($response, true);
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            // Fallback ke text-based generation
            return generateAIDescriptionFromText($title, $category);
        }
        
        $description = trim($response_data['choices'][0]['message']['content']);
        
        // Clean up description
        $description = preg_replace('/^["\']|["\']$/', '', $description);
        
        return [
            'success' => true,
            'description' => $description,
            'from_image' => true
        ];
        
    } catch (Exception $e) {
        error_log("AI Vision Helper Error: " . $e->getMessage());
        // Fallback ke text-based generation
        return generateAIDescriptionFromText($title, $category);
    }
}

/**
 * Generate deskripsi laporan dari teks (judul dan kategori)
 * 
 * @param string $title Judul laporan
 * @param string $category Kategori laporan
 * @return array Hasil dengan 'success' dan 'description' atau 'error'
 */
function generateAIDescriptionFromText($title, $category) {
    // Check jika OpenAI API key sudah diset
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        // Fallback ke template-based
        return generateFallbackDescription($title, $category);
    }
    
    // Kategori mapping
    $category_map = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    $category_label = $category_map[$category] ?? $category;
    
    // Build prompt untuk OpenAI
    $prompt = "Sebagai sistem pelaporan darurat, buatkan deskripsi laporan darurat yang detail dalam bahasa Indonesia.\n\n";
    $prompt .= "Konteks: Laporan dengan judul \"" . $title . "\" dalam kategori " . $category_label . ".\n\n";
    $prompt .= "Buatkan deskripsi yang:\n";
    $prompt .= "- Menjelaskan kejadian darurat secara detail\n";
    $prompt .= "- Menjelaskan kondisi yang terjadi\n";
    $prompt .= "- Menjelaskan tingkat urgensi\n";
    $prompt .= "- Singkat dan jelas (maksimal 200 kata)\n";
    $prompt .= "- Menggunakan bahasa Indonesia yang baik dan benar\n";
    $prompt .= "- HANYA berisi deskripsi kejadian, TIDAK menyebutkan judul atau kategori\n";
    $prompt .= "- Tidak perlu tanda kurung atau format khusus\n";
    $prompt .= "- Langsung mulai dengan menjelaskan kejadian\n\n";
    $prompt .= "Deskripsi laporan (langsung mulai dengan kejadian, tanpa menyebut judul/kategori):";
    
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
                    'content' => 'Anda adalah sistem pelaporan darurat. Buatkan deskripsi laporan darurat yang detail, jelas, dan profesional dalam bahasa Indonesia. Deskripsi harus langsung menjelaskan kejadian tanpa menyebutkan judul atau kategori laporan.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 500,
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
            
            // Fallback ke template-based
            return generateFallbackDescription($title, $category);
        }
        
        $response_data = json_decode($response, true);
        
        if (!isset($response_data['choices'][0]['message']['content'])) {
            // Fallback ke template-based
            return generateFallbackDescription($title, $category);
        }
        
        $description = trim($response_data['choices'][0]['message']['content']);
        
        // Clean up description
        $description = preg_replace('/^["\']|["\']$/', '', $description);
        
        return [
            'success' => true,
            'description' => $description,
            'from_image' => false
        ];
        
    } catch (Exception $e) {
        error_log("AI Text Helper Error: " . $e->getMessage());
        // Fallback ke template-based
        return generateFallbackDescription($title, $category);
    }
}

/**
 * Fallback description generator (tanpa AI, template-based)
 */
function generateFallbackDescription($title, $category) {
    $category_map = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    
    $category_label = $category_map[$category] ?? 'Laporan';
    
    $templates = [
        'kecelakaan' => "Terjadi kecelakaan di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan segera dari pihak berwenang. Mohon kirimkan bantuan medis dan keamanan ke lokasi kejadian.",
        'kebakaran' => "Terjadi kebakaran di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan segera dari tim pemadam kebakaran. Mohon kirimkan bantuan secepat mungkin ke lokasi kejadian.",
        'medis' => "Terjadi darurat medis di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan medis segera. Mohon kirimkan ambulans dan tim medis ke lokasi kejadian.",
        'kejahatan' => "Terjadi kejahatan di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan segera dari pihak berwenang. Mohon kirimkan tim keamanan ke lokasi kejadian.",
        'bencana' => "Terjadi bencana alam di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan segera dari tim penanganan bencana. Mohon kirimkan bantuan secepat mungkin ke lokasi kejadian.",
        'lainnya' => "Terjadi kejadian darurat di lokasi tersebut. " . $title . ". Kondisi memerlukan bantuan segera dari pihak berwenang. Mohon kirimkan bantuan ke lokasi kejadian."
    ];
    
    $description = $templates[$category] ?? $templates['lainnya'];
    
    return [
        'success' => true,
        'description' => $description,
        'from_image' => false,
        'fallback' => true
    ];
}
