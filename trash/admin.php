<?php
require 'includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Product Approval/Rejection
    if (isset($_POST['product_action'], $_POST['product_id'])) {
        $action = $_POST['product_action'];
        $product_id = (int)$_POST['product_id'];
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $product_id]);
        $message = "Product #$product_id " . ucfirst($new_status) . " successfully!";
    }
    //Contact Message Status Update
    elseif (isset($_POST['contact_action'], $_POST['msg_id'])) {
        $status = $_POST['contact_action'] === 'mark_read' ? 'read' : 'replied';
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, (int)$_POST['msg_id']]);
        $message = "Message status updated!";
    }

    //Order Approval/Rejection
    elseif (isset($_POST['order_action'], $_POST['order_id'])) {
        $action = $_POST['order_action'];
        $order_id = (int)$_POST['order_id'];
        $new_status = ($action === 'approve') ? 'approved' : 'cancelled';

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $message = "Order #$order_id " . ucfirst($new_status) . " successfully!";
    }

    //Category Management
    elseif (isset($_POST['cat_action'])) {
        if ($_POST['cat_action'] === 'add') {
            $name = trim($_POST['name']);
            $slug = strtolower(trim($_POST['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', $name)));
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $message = "Category '$name' added successfully!";
        } elseif ($_POST['cat_action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Category deleted successfully!";
        }
    }
}

//Stats
$stats = [
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending_approval'")->fetchColumn(),
    'pending_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'pending'")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'approved'")->fetchColumn(),
];

//Lists
$pending_orders = $pdo->query("SELECT o.*, u.username, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o JOIN users u ON o.user_id = u.id WHERE o.status = 'pending_approval' ORDER BY o.created_at DESC")->fetchAll();
$pending_products = $pdo->query("SELECT p.*, u.username as seller_name, c.name as category_name FROM products p JOIN users u ON p.seller_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.status = 'pending' ORDER BY p.created_at DESC")->fetchAll();
$recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name")->fetchAll();
$contact_msgs = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

$active_tab = $_GET['tab'] ?? 'orders';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mal Tech Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Manage orders, products, and users</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending_orders'] ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending_products'] ?></div>
                <div class="stat-label">Pending Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_products'] ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="admin-tabs">
            <a href="?tab=orders" class="tab-btn <?= $active_tab === 'orders' ? 'active' : '' ?>">Orders</a>
            <a href="?tab=products" class="tab-btn <?= $active_tab === 'products' ? 'active' : '' ?>">Products</a>
            <a href="?tab=categories" class="tab-btn <?= $active_tab === 'categories' ? 'active' : '' ?>">Categories</a>
            <a href="?tab=users" class="tab-btn <?= $active_tab === 'users' ? 'active' : '' ?>">Users</a>
            <a href="?tab=contact" class="tab-btn <?= $active_tab === 'contact' ? 'active' : '' ?>">Contact</a>
        </div>

        <!-- TAB: Orders -->
        <?php if ($active_tab === 'orders'): ?>
            <h2>Pending Orders</h2>
            <?php if (empty($pending_orders)): ?><div class="empty-state">No pending orders.</div><?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= sanitize($order['username']) ?></td>
                                <td><?= format_price($order['total_amount']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="order_action" value="approve" class="btn-action btn-approve">✓</button>
                                        <button type="submit" name="order_action" value="reject" class="btn-action btn-reject">✗</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>

        <!-- TAB: Products -->
        <?php if ($active_tab === 'products'): ?>
            <h2>Pending Product Listings</h2>
            <?php if (empty($pending_products)): ?><div class="empty-state">No pending products.</div><?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_products as $p): ?>
                            <tr>
                                <td><?= sanitize($p['name']) ?></td>
                                <td><?= sanitize($p['seller_name']) ?></td>
                                <td><?= format_price($p['price']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="product_action" value="approve" class="btn-action btn-approve">✓</button>
                                        <button type="submit" name="product_action" value="reject" class="btn-action btn-reject">✗</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($active_tab === 'categories'): ?>
            <h2>Category Management</h2>

            <div class="cat-management">
                <!-- Left: Add Form -->
                <div class="cat-form-box">
                    <h3>Add New Category</h3>
                    <form method="POST" style="margin-top:15px;">
                        <input type="hidden" name="cat_action" value="add">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="name" required placeholder="e.g., Monitors">
                        </div>
                        <div class="form-group">
                            <label>URL Slug</label>
                            <input type="text" name="slug" placeholder="e.g., monitors (auto-generated if empty)">
                        </div>
                        <button type="submit" class="btn-login">Add Category</button>
                    </form>
                </div>

                <!-- Right: List -->
                <div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= sanitize($cat['name']) ?></td>
                                    <td><?= sanitize($cat['slug']) ?></td>
                                    <td><?= $cat['product_count'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="cat_action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="btn-action btn-reject">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- TAB: Users -->
        <?php if ($active_tab === 'users'): ?>
            <h2>Recent Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= sanitize($u['username']) ?></td>
                            <td><?= sanitize($u['email']) ?></td>
                            <td><span class="status-badge status-<?= $u['role'] === 'admin' ? 'approved' : 'cancelled' ?>"><?= ucfirst($u['role']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- TAB: Contact Messages -->
        <?php if ($active_tab === 'contact'): ?>
            <h2>Contact Submissions</h2>
            <?php if (empty($contact_msgs)): ?>
                <div class="empty-state">No contact messages yet.</div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message Preview</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_msgs as $msg): ?>
                            <tr>
                                <td>#<?= $msg['id'] ?></td>
                                <td><?= sanitize($msg['name']) ?></td>
                                <td><a href="mailto:<?= sanitize($msg['email']) ?>" style="color:#3b82f6; text-decoration:none;"><?= sanitize($msg['email']) ?></a></td>
                                <td style="max-width:200px;">
                                    <span style="cursor:pointer; color:#2563eb; text-decoration:underline;" onclick="openMessageModal(<?= $msg['id'] ?>, '<?= sanitize(addslashes($msg['name'])) ?>', '<?= sanitize(addslashes($msg['email'])) ?>', '<?= sanitize(addslashes($msg['message'])) ?>', '<?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>')">
                                        <?= substr(sanitize($msg['message']), 0, 50) ?>...
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $msg['status'] ?>">
                                        <?= ucfirst($msg['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, H:i', strtotime($msg['created_at'])) ?></td>
                                <td>
                                    <button onclick="openMessageModal(<?= $msg['id'] ?>, '<?= sanitize(addslashes($msg['name'])) ?>', '<?= sanitize(addslashes($msg['email'])) ?>', '<?= sanitize(addslashes($msg['message'])) ?>', '<?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>')" class="btn-action" style="background:#3b82f6; color:white;">👁 View</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                                        <?php if ($msg['status'] === 'new'): ?>
                                            <button type="submit" name="contact_action" value="mark_read" class="btn-action btn-approve">✓ Read</button>
                                        <?php elseif ($msg['status'] === 'read'): ?>
                                            <button type="submit" name="contact_action" value="replied" class="btn-action" style="background:#10b981; color:white;">💬 Replied</button>
                                        <?php else: ?>
                                            <span style="color:#666; font-size:12px;">Completed</span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Message Detail Modal -->
            <div id="messageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:white; border-radius:12px; max-width:700px; width:90%; max-height:80vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
                    <div style="padding:20px; border-bottom:2px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0; color:#1e293b;">Message Details</h3>
                        <button onclick="closeMessageModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">&times;</button>
                    </div>
                    <div style="padding:20px;">
                        <div style="margin-bottom:20px;">
                            <strong style="color:#64748b; font-size:13px;">From:</strong>
                            <div style="margin-top:4px; font-weight:600; color:#1e293b;" id="modalName"></div>
                            <a href="#" id="modalEmail" style="color:#3b82f6; text-decoration:none; font-size:14px;"></a>
                        </div>
                        <div style="margin-bottom:20px;">
                            <strong style="color:#64748b; font-size:13px;">Received:</strong>
                            <div style="margin-top:4px; color:#1e293b;" id="modalDate"></div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <strong style="color:#64748b; font-size:13px;">Message:</strong>
                            <div style="margin-top:8px; padding:15px; background:#f8fafc; border-radius:8px; border-left:4px solid #3b82f6; line-height:1.6; color:#334155; white-space:pre-wrap;" id="modalMessage"></div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <a href="#" id="modalReplyLink" style="flex:1; text-align:center; padding:12px; background:#3b82f6; color:white; text-decoration:none; border-radius:8px; font-weight:600;">✉ Reply via Email</a>
                            <button onclick="closeMessageModal()" style="flex:1; padding:12px; background:#e2e8f0; color:#334155; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openMessageModal(id, name, email, message, date) {
                    document.getElementById('modalName').textContent = name;
                    document.getElementById('modalEmail').textContent = email;
                    document.getElementById('modalEmail').href = 'mailto:' + email;
                    document.getElementById('modalDate').textContent = date;
                    document.getElementById('modalMessage').textContent = message;
                    document.getElementById('modalReplyLink').href = 'mailto:' + email + '?subject=Re: Your inquiry to Mal Tech Store';
                    document.getElementById('messageModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }

                function closeMessageModal() {
                    document.getElementById('messageModal').style.display = 'none';
                    document.body.style.overflow = 'auto';
                }

                // Close modal when clicking outside
                document.getElementById('messageModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeMessageModal();
                    }
                });

                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeMessageModal();
                    }
                });
            </script>

        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>