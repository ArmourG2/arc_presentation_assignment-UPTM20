<?php
require 'includes/database.php';
echo "✅ DB Connected<br>";
echo "👤 Users: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "<br>";
echo "📦 Products: " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "<br>";
?>