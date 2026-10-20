<?php
require_once 'includes/db.php';

$new_password = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$new_password]);

echo "<h3>Admin şifresi başarıyla güncellendi!</h3>";
echo "<p><a href='login.php'>Giriş sayfasına dön</a></p>";