<?php
require 'includes/functions.php';
require 'includes/cart.php';

// Handle cart actions BEFORE header
$action = $_GET['action'] ?? null;

if ($action) {
    $id = $_GET['id'] ?? null;
    
    if ($action === 'add' && $id) {
        add_to_cart($id);
        set_flash('success', 'Item added to cart!');
    } elseif ($action === 'remove' && $id) {
        remove_cart_item($id);
    } elseif ($action === 'clear') {
        clear_cart();
        set_flash('info', 'Cart cleared.');
    }
    
    redirect('cart.php');
}

include 'includes/header.php';
$cart_items = get_cart_items();
$total = get_cart_total();
?>

<main class="cart-page">
    <h2>Your Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="cart-empty-box">
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn-login cart-btn-continue">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-items-box">
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item-row">
                <div class="cart-item-details">
                    <strong><?= sanitize($item['name']) ?></strong>
                    <br><small>x<?= $item['quantity'] ?> @ <?= format_price($item['price']) ?></small>
                </div>
                <div class="cart-item-actions">
                    <span class="cart-item-subtotal"><?= format_price($item['subtotal']) ?></span>
                    <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="cart-remove-link">Remove</a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="cart-total-row">
                <span>Total</span>
                <span><?= format_price($total) ?></span>
            </div>
            
            <a href="checkout.php" class="btn-login cart-btn-checkout">Proceed to Checkout</a>
            <a href="cart.php?action=clear" class="cart-btn-clear">Clear Cart</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>