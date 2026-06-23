<?php
require 'includes/functions.php';
require 'includes/cart.php';

if (!is_logged_in()) redirect('login.php');

$cart_items = get_cart_items();
$total = get_cart_total();

if (empty($cart_items)) redirect('cart.php');

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    try {
        $pdo->beginTransaction();
        
        // Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending_approval')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();
        
        // Create Order Items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        $stmt_product = $pdo->prepare("UPDATE products SET status = 'reserved' WHERE id = ? AND status = 'approved'");
        
        foreach ($cart_items as $item) {
            $stmt_item->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            $stmt_product->execute([$item['id']]);
        }
        
        // Clear Cart & Commit
        clear_cart();
        $pdo->commit();
        
        redirect('order-success.php?id=' . $order_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Checkout failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="checkout-grid">
            <!-- LEFT: Order Summary -->
            <div class="checkout-summary">
                <h2>Order Summary</h2>
                <div class="checkout-items-list">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="checkout-item-row">
                        <span><?= sanitize($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                        <span><?= format_price($item['subtotal']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="checkout-total-row">
                    <span>Total</span>
                    <span><?= format_price($total) ?></span>
                </div>
            </div>
            
            <!-- RIGHT: Payment Methods -->
            <div class="checkout-qr">
                <h3>Select Payment Method</h3>
                
                <div class="payment-methods">
                    <!-- ACTIVE: QR Payment -->
                    <div class="payment-option active">
                        <div class="payment-icon">📱</div>
                        <div class="payment-info">
                            <div class="payment-name">QR Payment</div>
                            <div class="payment-desc">Scan QR code to pay</div>
                        </div>
                        <span class="payment-badge badge-active">Active</span>
                    </div>
                    
                    <!-- DISABLED: Credit/Debit Card -->
                    <div class="payment-option disabled">
                        <div class="payment-icon">💳</div>
                        <div class="payment-info">
                            <div class="payment-name">Credit/Debit Card</div>
                            <div class="payment-desc">Visa & Mastercard</div>
                        </div>
                        <span class="payment-badge badge-soon">Coming Soon</span>
                    </div>
                    
                    <!-- DISABLED: Online Banking -->
                    <div class="payment-option disabled">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-info">
                            <div class="payment-name">Online Banking</div>
                            <div class="payment-desc">FPX, Maybank2u, CIMB Clicks</div>
                        </div>
                        <span class="payment-badge badge-soon">Coming Soon</span>
                    </div>
                    
                    <!-- DISABLED: E-Wallet -->
                    <div class="payment-option disabled">
                        <div class="payment-icon">👛</div>
                        <div class="payment-info">
                            <div class="payment-name">E-Wallet</div>
                            <div class="payment-desc">Touch 'n Go, GrabPay, Boost</div>
                        </div>
                        <span class="payment-badge badge-soon">Coming Soon</span>
                    </div>
                </div>
                
                <!-- Notice Box -->
                <div class="payment-notice">
                    <strong>⚠️ Payment Notice</strong>
                    Currently we only accept QR Payment due to issues with our payment provider. 
                    Other payment methods will be available soon. We apologize for any inconvenience.
                </div>
                
                <!-- QR Code Display -->
                <div class="qr-placeholder">
                    <img src="images/qr.png" alt="QR Code for Payment">
                </div>
                <p class="qr-instruction">Scan with your banking app to pay</p>
                
                <!-- Payment Button -->
                <form method="POST">
                    <button type="submit" name="pay" class="btn-login">✓ I Have Paid</button>
                </form>
                
                <p class="qr-note">Your order will be reviewed by admin before processing.</p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 