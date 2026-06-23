<?php
require 'includes/functions.php';
require_role(['seller', 'admin']); // Only sellers/admins can access

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $desc = trim($_POST['description']);
    $condition = $_POST['condition'];
    $location = trim($_POST['location']);
    $category_id = (int)$_POST['category_id'];
    
    // Image Upload Handling
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Invalid image type. Only JPG, PNG, WEBP allowed.';
        } elseif ($file['size'] > $max_size) {
            $error = 'Image too large. Max 5MB.';
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('prod_') . '.' . $ext;
            $dest = $upload_dir . $new_name;
            
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $image_path = 'uploads/' . $new_name;
            } else {
                $error = 'Failed to upload image. Check folder permissions.';
            }
        }
    }
    
    if (!$error && $name && $price > 0 && $category_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image_path, category_id, condition_status, location, seller_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$name, $price, $desc, $image_path, $category_id, $condition, $location, $_SESSION['user_id']]);
            $success = '✅ Product listed successfully! Awaiting admin approval.';
            // Clear form data
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } elseif (!$error) {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Product - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page sell-page">
    <div class="login-container" style="max-width: 600px;">
        <div class="login-box">
            <h2>List a Product</h2>
            <p>Sell your computer parts securely</p>
            
            <?php if ($success): ?>
                <div class="success-message"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" action="">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g., RTX 3080 Graphics Card" value="<?= sanitize($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Price (MYR) *</label>
                    <input type="number" name="price" step="0.01" min="1" required placeholder="299.99" value="<?= $_POST['price'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition">
                        <option value="New" <?= ($_POST['condition'] ?? '') === 'New' ? 'selected' : '' ?>>Brand New</option>
                        <option value="Like New" <?= ($_POST['condition'] ?? '') === 'Like New' ? 'selected' : '' ?>>Used - Like New</option>
                        <option value="Good" <?= ($_POST['condition'] ?? '') === 'Good' ? 'selected' : '' ?>>Used - Good</option>
                        <option value="Fair" <?= ($_POST['condition'] ?? '') === 'Fair' ? 'selected' : '' ?>>Used - Fair</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g., Kuala Lumpur" value="<?= sanitize($_POST['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                    <small>Max 5MB. JPG, PNG, WEBP only.</small>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe your product's condition, specs, and any additional details..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-login">List Product</button>
            </form>
            <div class="login-footer">
                <p><a href="index.php">← Back to Homepage</a></p>
            </div>
        </div>
    </div>
</body>
</html>