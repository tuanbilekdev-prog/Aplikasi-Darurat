<?php
/**
 * PROJECT ONE - RESET PASSWORD
 * Script untuk reset password user
 * HAPUS FILE INI SETELAH SELESAI!
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Hanya untuk development - HAPUS di production!
if (ini_get('display_errors') != 1) {
    die("Debug mode is disabled. Enable display_errors in config.php");
}

echo "<h2>Reset Password - Project One</h2>";
echo "<p style='color: red;'><strong>PERINGATAN: Hapus file ini setelah selesai!</strong></p>";

// Get parameters
$username = $_GET['username'] ?? '';
$new_password = $_GET['password'] ?? '';

if ($username && $new_password) {
    try {
        $db = getDB();
        
        // Find user
        $stmt = $db->prepare("SELECT id, username, email FROM users WHERE username = :username OR email = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "❌ User tidak ditemukan<br>";
        } else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([
                'password' => $hashed_password,
                'id' => $user['id']
            ]);
            
            echo "✅ Password berhasil direset untuk user: " . htmlspecialchars($user['username']) . "<br>";
            echo "Email: " . htmlspecialchars($user['email']) . "<br>";
            echo "Password baru: " . str_repeat('*', strlen($new_password)) . "<br>";
            echo "<br><a href='login.php'>Kembali ke Login</a>";
        }
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
} else {
    // Show form
    echo "<form method='GET' style='border: 1px solid #ccc; padding: 20px; max-width: 400px;'>";
    echo "<label>Username/Email:<br>";
    echo "<input type='text' name='username' required style='width: 100%; padding: 8px;'><br></label><br>";
    echo "<label>Password Baru:<br>";
    echo "<input type='password' name='password' required minlength='8' style='width: 100%; padding: 8px;'><br></label><br>";
    echo "<button type='submit' style='padding: 10px 20px; background: #E63946; color: white; border: none; cursor: pointer;'>Reset Password</button>";
    echo "</form>";
}

echo "<hr>";
echo "<p><small>File: reset_password.php - Hapus file ini setelah selesai!</small></p>";
?>

