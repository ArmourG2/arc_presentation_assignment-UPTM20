<?php
require 'includes/functions.php';
include 'includes/header.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ? AND p.status = 'approved'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo '<main class="product-not-found">';
    echo '<h2>Product not found or pending approval</h2>';
    echo '<p style="color:#666; margin-bottom:20px;">This item may have been removed, rejected, or is awaiting admin review.</p>';
    echo '<a href="index.php" class="btn login">← Back Home</a>';
    echo '</main>';
    include 'includes/footer.php';
    echo '</body>';
    echo '</html>';
    exit();
}
?>

<main class="product-page">
    <div class="product-back">
        <div class="back-btn-login"></div>
        <a href="#" onclick="history.back(); return false;">← Back to Products</a>
    </div>
    
    <div class="product-details">
        <div class="product-image-large">
            <img src="<?= $product['image_path'] ? htmlspecialchars($product['image_path']) : 'images/placeholder.png' ?>" 
                 alt="<?= sanitize($product['name']) ?>">
        </div>
        
        <div class="product-info-large">
            <h1 class="product-name-large"><?= sanitize($product['name']) ?></h1>
            <p class="product-price-large"><?= format_price($product['price']) ?></p>
            
            <div class="product-meta">
                <p><strong>Condition:</strong> <?= sanitize($product['condition_status']) ?></p>
                <p><strong>Location:</strong> <?= sanitize($product['location'] ?: 'Not specified') ?></p>
                <p><strong>Seller:</strong> <?= sanitize($product['seller_name']) ?></p>
                <p><strong>Posted:</strong> <?= date('M d, Y', strtotime($product['created_at'])) ?></p>
            </div>
            
            <p class="product-description"><?= nl2br(sanitize($product['description'])) ?></p>
            
            <div class="product-actions">
                <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn-cart-large">Add to Cart</a>
                <a href="message.php?product_id=<?= $product['id'] ?>" class="btn-contact">Contact Seller</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>