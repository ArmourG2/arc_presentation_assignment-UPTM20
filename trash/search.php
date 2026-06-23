<?php
require 'includes/functions.php';
include 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$products = [];
if ($q) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'approved' AND (name LIKE ? OR description LIKE ?)");
    $stmt->execute(["%$q%", "%$q%"]);
    $products = $stmt->fetchAll();
}
?>

<main class="search-page" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <div class="section-header">
        <h2>Search Results <?= $q ? 'for "' . sanitize($q) . '"' : '' ?></h2>
    </div>
    <div class="products-container">
        <?php if (!$q): ?>
            <p style="text-align:center; width:100%; grid-column: 1/-1; color:#666;">Enter a search term to find products.</p>
        <?php elseif (empty($products)): ?>
            <p style="text-align:center; width:100%; grid-column: 1/-1; color:#666;">No products found matching your search.</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <div class="product-image">
                        <img src="<?= $p['image_path'] ? htmlspecialchars($p['image_path']) : 'images/none.png' ?>" alt="<?= sanitize($p['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?= sanitize($p['name']) ?></h3>
                        <p class="product-price"><?= format_price($p['price']) ?></p>
                        <a href="cart.php?action=add&id=<?= $p['id'] ?>" class="btn-cart">Add to Cart</a>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>