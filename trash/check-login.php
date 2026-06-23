<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/config.php';
include 'includes/database.php';

echo "<h3>🔍 Login Diagnostic</h3>";

// Check POST data
echo "<p><strong>POST Data:</strong><br>";
var_dump($_POST);
echo "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Query DB
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    echo "<p><strong>DB Result:</strong><br>";
    if ($user) {
        echo "✅ User found: " . htmlspecialchars($user['username']) . "<br>";
        echo "🔑 Hash length: " . strlen($user['password']) . " chars<br>";
        echo "🔑 Hash preview: " . substr($user['password'], 0, 20) . "..." . "<br>";
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green; font-weight:bold;'>🎉 PASSWORD VERIFIED! Login should work.</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>❌ PASSWORD VERIFY FAILED</p>";
            echo "<p>Expected hash for '123': <code>\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</code></p>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ USER NOT FOUND IN DATABASE</p>";
    }
    echo "</p>";
}
?>

<form method="POST">
    Username: <input type="text" name="username" value="seller"><br>
    Password: <input type="text" name="password" value="123"><br>
    <button type="submit">Test Login</button>
</form>