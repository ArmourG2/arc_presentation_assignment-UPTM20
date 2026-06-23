<?php
require 'includes/functions.php';
include 'includes/header.php';

$category_slug = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'approved'";
$params = [];

if ($category_slug) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
}

if ($sort === 'price_asc') $sql .= " ORDER BY p.price ASC";
elseif ($sort === 'price_desc') $sql .= " ORDER BY p.price DESC";
else $sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<main class="category-page">
    <div class="section-header">
        <h2><?= $category_slug ? 'Browse: ' . ucfirst($category_slug) : 'All Categories' ?></h2>
        <form method="GET" class="category-filter-form">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['slug'] ?>" <?= $category_slug === $cat['slug'] ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </form>
    </div>

    <div class="products-container">
        <?php if (empty($products)): ?>
            <p class="no-products-msg">No products found.</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <div class="product-image">
                        <img src="<?= $p['image_path'] ? htmlspecialchars($p['image_path']) : 'images/placeholder.png' ?>" alt="<?= sanitize($p['name']) ?>">
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