<?php
require 'includes/config.php';
require 'includes/cart.php';

echo "<h3>🛒 Cart Debug</h3>";

// Handle actions
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    clear_cart();
    echo "<p style='color:green;'>✅ Cart cleared!</p>";
}

if (isset($_GET['test'])) {
    add_to_cart(1, 1);
    echo "<p style='color:blue;'>✅ Added Product ID 1 to cart!</p>";
}

echo "<p><strong>Current Session Cart:</strong><br><pre>";
print_r($_SESSION['cart'] ?? 'EMPTY');
echo "</pre></p>";

echo "<p>
    <a href='debug-cart.php?test=1'>➕ Add Test Item</a> | 
    <a href='debug-cart.php?action=clear'>🗑️ Clear Cart</a> | 
    <a href='cart.php'>🛒 Go to Real Cart</a>
</p>";
?>