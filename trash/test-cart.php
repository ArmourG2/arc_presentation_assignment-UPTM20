<?php
require 'includes/cart.php';
add_to_cart(1, 2); // Add 2x RTX 3080
echo "Cart Items: " . get_cart_count() . "<br>";
echo "Total: " . format_price(get_cart_total());
?>