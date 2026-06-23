<?php
require 'includes/functions.php';
if (!is_logged_in()) redirect('login.php');

$user_id = $_SESSION['user_id'];

// Fetch conversations (grouped by product)
if ($_SESSION['role'] === 'seller' || $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.image_path, 
               MAX(m.created_at) as last_msg,
               COUNT(m.id) as unread_count
        FROM products p
        LEFT JOIN messages m ON p.id = m.product_id AND m.receiver_id = ?
        WHERE p.seller_id = ?
        GROUP BY p.id
        HAVING last_msg IS NOT NULL
        ORDER BY last_msg DESC
    ");
    $stmt->execute([$user_id, $user_id]);
} else {
    // Buyer view: show products they've messaged
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.image_path, 
               MAX(m.created_at) as last_msg
        FROM messages m
        JOIN products p ON m.product_id = p.id
        WHERE m.sender_id = ?
        GROUP BY p.id
        ORDER BY last_msg DESC
    ");
    $stmt->execute([$user_id]);
}
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .inbox-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            min-width: 800px;
        }

        .inbox-card {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .inbox-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .inbox-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: #eee;
        }

        .inbox-info {
            flex: 1;
        }

        .inbox-info h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
        }

        .inbox-info p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }

        .inbox-time {
            color: #999;
            font-size: 12px;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main class="inbox-container">
        <div class="product-back"><a href="index.php">← Back Home</a></div>
        <h2 style="margin-bottom:20px;">My Conversations</h2>

        <?php if (empty($conversations)): ?>
            <div style="text-align:center; padding:60px; background:white; border-radius:10px; color:#666;">
                No conversations yet. Start chatting from a product page!
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="message.php?product_id=<?= $conv['id'] ?>" class="inbox-card">
                    <img src="<?= $conv['image_path'] ? htmlspecialchars($conv['image_path']) : 'images/none.png' ?>" class="inbox-img" alt="Product">
                    <div class="inbox-info">
                        <h4><?= sanitize($conv['name']) ?></h4>
                        <p>Click to open chat</p>
                    </div>
                    <div class="inbox-time"><?= date('M d', strtotime($conv['last_msg'])) ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>