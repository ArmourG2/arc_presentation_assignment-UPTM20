<?php
require 'includes/functions.php';
if (is_logged_in()) redirect('index.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = in_array($_POST['role'], ['consumer', 'seller']) ? $_POST['role'] : 'consumer';
    
    if (strlen($username) < 3 || strlen($password) < 3) {
        $error = 'Username and password must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $role]);
            $message = 'Registration successful! Please login.';
        } catch (PDOException $e) {
            $error = 'Username or email already exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <a href="index.php" class="back-link">← Back to Homepage</a>
            <h2>Create Account</h2>
            <p>Join Mal Tech Store today</p>
            
            <?php if ($message): ?>
                <div class="success-message"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" required placeholder="Choose a username">
                </div>
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required placeholder="Min 8 characters">
                </div>
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="consumer">Consumer (Buyer)</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>
                <button type="submit" class="btn-login">Register</button>
            </form>
            
            <div class="login-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>