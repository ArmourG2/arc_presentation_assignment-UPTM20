<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cart.php';

init_cart();
$cart_count = get_cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header Top -->
    <div class="header-container">
        <div class="logo">
            <a href="index.php">Mal Tech Store</a>
        </div>
        
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="q" class="search-input" placeholder="Search parts..." value="<?= sanitize($_GET['q'] ?? '') ?>">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>
        
        <div class="auth">
            <a href="cart.php" class="cart-link" style="color:white; margin-right:10px;">🛒 (<?= $cart_count ?>)</a>
            
    <?php if (is_logged_in()): ?>
        <!-- Chats link for ALL logged-in users -->
        <a href="inbox.php" class="cart-link" style="margin-right:10px;">💬 Chats</a>
        
        <!-- Sell button for Sellers & Admins -->
        <?php if ($_SESSION['role'] === 'seller' || $_SESSION['role'] === 'admin'): ?>
            <a href="sell.php" class="btn sell">Sell</a>
        <?php endif; ?>
        
        <!-- Dashboard button for Admins -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="btn login" style="background:blue; margin-right:10px;">Dashboard</a>
        <?php endif; ?>
        
        <!-- User Greeting & Logout -->
        <span class="user-greeting">Hi, <?= sanitize($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn login">Logout</a>
        
        <?php else: ?>
            <!-- Not Logged In -->
            <a href="login.php" class="btn login">Login</a>
            <a href="register.php" class="btn login">Register</a>
        <?php endif; ?>
        </div>
    </div>
    
    <!-- Navigation Bar -->
    <nav class="nav-category">
        <ul class="nav-links">
            <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
            <li class="nav-item"><a href="category.php" class="nav-link">Categories</a></li>
            <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
            <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
        </ul>
    </nav>