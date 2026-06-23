<?php
require 'includes/functions.php';
if (!is_logged_in()) redirect('login.php');

$product_id = $_GET['product_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch product & seller info
$stmt = $pdo->prepare("SELECT p.*, u.id as seller_id, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) die('<div style="padding:40px;text-align:center;"><h2>Product not found</h2><a href="index.php">Back Home</a></div>');

// Determine receiver & partner name
if ($user_id == $product['seller_id']) {
    // Find latest buyer who messaged(Seller)
    $stmt_partner = $pdo->prepare("SELECT sender_id FROM messages WHERE product_id = ? AND receiver_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_partner->execute([$product_id, $user_id]);
    $partner = $stmt_partner->fetch();
    $receiver_id = $partner ? $partner['sender_id'] : null;
    $partner_name = $partner ? 'Buyer' : 'Waiting for buyer inquiry...';
} else {
    // Receiver is always seller(Buyer)
    $receiver_id = $product['seller_id'];
    $partner_name = sanitize($product['seller_name']);
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg && $receiver_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (product_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $receiver_id, $msg]);
        header("Location: message.php?product_id=$product_id");
        exit();
    }
}

// Fetch conversation
$stmt_msgs = $pdo->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.product_id = ? ORDER BY m.created_at ASC");
$stmt_msgs->execute([$product_id]);
$messages = $stmt_msgs->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?= sanitize($product['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .chat-wrapper {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .chat-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chat-header h2 {
            margin: 0 0 5px 0;
        }

        .chat-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .chat-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: 450px;
            overflow-y: auto;
            padding: 20px;
            margin-bottom: 20px;
            scroll-behavior: smooth;
        }

        .empty-chat {
            text-align: center;
            color: #888;
            margin-top: 180px;
            font-style: italic;
        }

        .message {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        .message.sent {
            align-items: flex-end;
        }

        .message.received {
            align-items: flex-start;
        }

        .message-bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 18px;
            word-break: break-word;
            line-height: 1.5;
            font-size: 15px;
        }

        .sent .message-bubble {
            background: #3b82f6;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .received .message-bubble {
            background: #f0f0f0;
            color: #333;
            border-bottom-left-radius: 4px;
        }

        .message-meta {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
            padding: 0 4px;
        }

        .chat-form {
            display: flex;
            gap: 12px;
            background: white;
            padding: 16px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chat-form textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: none;
            height: 52px;
            font-family: inherit;
            font-size: 15px;
        }

        .chat-form textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .chat-form button {
            padding: 0 28px;
            border: none;
            background: #3b82f6;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.2s;
        }

        .chat-form button:hover {
            background: #2563eb;
        }

        .chat-form button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="chat-wrapper">
        <div class="product-back">
            <a href="product.php?id=<?= $product_id ?>">← Back to Product</a>
        </div>

        <div class="chat-header">
            <h2>Chat about: <?= sanitize($product['name']) ?></h2>
            <p>With: <?= $partner_name ?></p>
        </div>

        <div class="chat-box" id="chatBox">
            <?php if (empty($messages)): ?>
                <div class="empty-chat">No messages yet. Start the conversation!</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                        <div class="message-bubble"><?= nl2br(sanitize($msg['message'])) ?></div>
                        <div class="message-meta"><?= sanitize($msg['username']) ?> • <?= date('M d, H:i', strtotime($msg['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($receiver_id): ?>
            <form method="POST" class="chat-form" id="chatForm">
                <textarea name="message" placeholder="Type your message..." required></textarea>
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <div style="background:#fff3cd; padding:15px; border-radius:10px; text-align:center; color:#856404;">
                ⏳ Waiting for buyer to send the first message.
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chatBox = document.getElementById('chatBox');
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>
</body>

</html>