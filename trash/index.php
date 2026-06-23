<?php
require 'includes/functions.php';
include 'includes/header.php';

// Fetch latest approved products
$stmt = $pdo->query("SELECT * FROM products WHERE status = 'approved' ORDER BY created_at DESC LIMIT 12");
$products = $stmt->fetchAll();
?>

<main>
    <!-- Static Hero Carousel -->
    <section class="hero-carousel">
        <div class="slides-container">
            <div class="slide slide-1">
                <div class="slide-content">
                    <h1>New Listings Weekly</h1>
                    <a href="category.php" class="carousel-btn">Browse All</a>
                </div>
            </div>
            <div class="slide slide-2">
                <div class="slide-content">
                    <h1>Trusted Platform</h1>
                    <a href="about.php" class="carousel-btn">Learn More</a>
                </div>
            </div>
        </div>
        <button class="nav-btn prev-btn">&#10094;</button>
        <button class="nav-btn next-btn">&#10095;</button>
        <div class="carousel-indicators">
            <span class="indicator active"></span>
            <span class="indicator"></span>
        </div>
    </section>

    <!-- Dynamic Product Grid -->
    <section class="product-grid-section">
        <div class="section-header">
            <h2>Featured Products</h2>
            <p>Latest verified listings from our community</p>
        </div>
        
        <div class="products-container">
            <?php if (empty($products)): ?>
                <p style="text-align:center; width:100%; grid-column: 1/-1; color:#666;">No products available yet.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $p['id'] ?>">
                        <div class="product-image">
                            <img src="<?= $p['image_path'] ? htmlspecialchars($p['image_path']) : 'images/placeholder.png' ?>" 
                                 alt="<?= sanitize($p['name']) ?>">
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
    </section>
</main>

<?php include 'includes/footer.php'; ?>
<script src="script.js"></script>
</body>
</html>