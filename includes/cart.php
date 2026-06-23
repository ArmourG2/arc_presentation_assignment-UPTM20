<?php
require_once __DIR__ . '/functions.php';
// Create Cart
function init_cart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// Add Item to cart
function add_to_cart($product_id, $quantity = 1) {
    init_cart();
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Update Quantitiy
function update_cart_item($product_id, $quantity) {
    init_cart();
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Remove Item
function remove_cart_item($product_id) {
    init_cart();
    $product_id = (int)$product_id;
    unset($_SESSION['cart'][$product_id]);
}

// Clear cart
function clear_cart() {
    unset($_SESSION['cart']);
}

// Get cart item + details of the item
function get_cart_items() {
    global $pdo;
    init_cart();
    
    if (empty($_SESSION['cart'])) {
        return [];
    }
    
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'approved'");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    
    // Merge cart quantity with product data
    $cart_items = [];
    foreach ($products as $product) {
        $product['quantity'] = $_SESSION['cart'][$product['id']];
        $product['subtotal'] = $product['price'] * $product['quantity'];
        $cart_items[] = $product;
    }
    
    return $cart_items;
}

// get cart total price
function get_cart_total() {
    $items = get_cart_items();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// Get total number of item in cart
function get_cart_count() {
    init_cart();
    return array_sum($_SESSION['cart']);
}
?>