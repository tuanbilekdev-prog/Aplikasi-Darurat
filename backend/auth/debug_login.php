<?php
/**
 * PROJECT ONE - DEBUG LOGIN
 * Script untuk debug masalah login
 * HAPUS FILE INI SETELAH SELESAI DEBUG!
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Hanya untuk development - HAPUS di production!
if (ini_get('display_errors') != 1) {
    die("Debug mode is disabled. Enable display_errors in config.php");
}

echo "<h2>Debug Login - Project One</h2>";
echo "<p style='color: red;'><strong>PERINGATAN: Hapus file ini setelah selesai debug!</strong></p>";

// Test database connection
echo "<h3>1. Test Database Connection</h3>";
try {
    $db = getDB();
    echo "✅ Database connection: OK<br>";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
    exit();
}

// Get username from GET parameter
$test_username = $_GET['username'] ?? '';
$test_password = $_GET['password'] ?? '';

if ($test_username && $test_password) {
    echo "<h3>2. Test Login Process</h3>";
    echo "Username/Email: " . htmlspecialchars($test_username) . "<br>";
    echo "Password: " . str_repeat('*', strlen($test_password)) . "<br><br>";
    
    try {
        // Check if user exists
        // Use separate parameters for username and email to avoid SQL parameter error
        $stmt = $db->prepare("
            SELECT id, username, email, password, role, status 
            FROM users 
            WHERE username = :username OR email = :email
            LIMIT 1
        ");
        $stmt->execute([
            'username' => $test_username,
            'email' => $test_username
        ]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "❌ User tidak ditemukan<br>";
            echo "Query: SELECT * FROM users WHERE username = :username OR email = :username<br>";
        } else {
            echo "✅ User ditemukan:<br>";
            echo "- ID: " . $user['id'] . "<br>";
            echo "- Username: " . $user['username'] . "<br>";
            echo "- Email: " . $user['email'] . "<br>";
            echo "- Role: " . $user['role'] . "<br>";
            echo "- Status: " . $user['status'] . "<br>";
            echo "- Password Hash: " . substr($user['password'], 0, 20) . "...<br><br>";
            
            // Check status
            if ($user['status'] !== 'active') {
                echo "❌ Status user: " . $user['status'] . " (harus 'active')<br>";
            } else {
                echo "✅ Status: active<br>";
            }
            
            // Test password verification
            echo "<h4>Password Verification:</h4>";
            $password_hash = $user['password'];
            $is_valid = password_verify($test_password, $password_hash);
            
            if ($is_valid) {
                echo "✅ Password verification: SUCCESS<br>";
                echo "Password cocok!<br>";
            } else {
                echo "❌ Password verification: FAILED<br>";
                echo "Password tidak cocok!<br>";
                echo "<br><strong>Kemungkinan masalah:</strong><br>";
                echo "1. Password yang dimasukkan salah<br>";
                echo "2. Password di database tidak di-hash dengan benar<br>";
                echo "3. Ada karakter tambahan (spasi, dll) pada password<br>";
            }
        }
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

// List all users
echo "<h3>3. Daftar Semua User</h3>";
try {
    $stmt = $db->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 10");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "❌ Tidak ada user di database<br>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>" . $u['id'] . "</td>";
            echo "<td>" . htmlspecialchars($u['username']) . "</td>";
            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
            echo "<td>" . $u['role'] . "</td>";
            echo "<td>" . $u['status'] . "</td>";
            echo "<td>" . $u['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test form
echo "<h3>4. Test Login</h3>";
echo "<form method='GET' style='border: 1px solid #ccc; padding: 20px; max-width: 400px;'>";
echo "<input type='hidden' name='test' value='1'>";
echo "<label>Username/Email:<br>";
echo "<input type='text' name='username' value='" . htmlspecialchars($test_username) . "' required style='width: 100%; padding: 8px;'><br></label><br>";
echo "<label>Password:<br>";
echo "<input type='password' name='password' value='" . htmlspecialchars($test_password) . "' required style='width: 100%; padding: 8px;'><br></label><br>";
echo "<button type='submit' style='padding: 10px 20px; background: #0A2540; color: white; border: none; cursor: pointer;'>Test Login</button>";
echo "</form>";

echo "<hr>";
echo "<p><small>File: debug_login.php - Hapus file ini setelah selesai!</small></p>";
?>

