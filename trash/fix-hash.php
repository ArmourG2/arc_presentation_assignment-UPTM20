<?php
// WARNING! Please enter this page ONLY if password are wrong.
// Generate a fresh hash for password '123'
$password = '123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "🔑 Fresh hash for '123':<br>";
echo "<code style='background:#f4f4f4; padding:10px; display:block; margin:10px 0; word-break:break-all;'>$hash</code><br><br>";

echo "📋 Copy the hash above, then run this SQL in phpMyAdmin:<br>";
echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>";
echo "UPDATE users SET password = '$hash' WHERE username IN ('admin', 'seller1', 'consumer1');";
echo "</pre>";
?>