<?php
require 'includes/functions.php';
if (!is_logged_in()) redirect('login.php');

$order_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container order-success-center">
        <div class="login-box">
            <h2 class="order-success-title">✅ Payment Received!</h2>
            <p>Your order <strong>#<?= (int)$order_id ?></strong> has been submitted successfully.</p>
            <p class="order-success-text">
                An admin will review and approve your order shortly. 
                You will be notified once processed.
            </p>
            <a href="index.php" class="btn-login order-success-btn">Back to Homepage</a>
        </div>
    </div>
</body>
</html>