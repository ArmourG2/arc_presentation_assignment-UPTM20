<?php
require 'includes/config.php';
include 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message, status) VALUES (?, ?, ?, 'new')");
            $stmt->execute([$name, $email, $message]);
            $success = "✅ Message sent successfully! We'll get back to you within 24 hours.";
            $_POST = [];// Clear POST data to prevent resubmission on refresh
        } catch (PDOException $e) {
            $error = "❌ Failed to send message. Please try again later.";
        }
    } else {
        $error = "❌ Please fill in all fields with valid information.";
    }
}
?>

<main class="contact-page-main">
    <div class="section-header">
        <h2>Contact Us</h2>
        <p>Have questions? We're here to help.</p>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="contact-grid">
        <!-- Contact Info -->
        <div class="contact-card info-card">
            <h3 class="contact-title">Get in Touch</h3>
            <div class="contact-details">
                <div class="contact-item">
                    <span class="icon">📧</span>
                    <div>
                        <strong>Email</strong>
                        <p>support@maltech.my</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="icon">🕐</span>
                    <div>
                        <strong>Business Hours</strong>
                        <p>Mon-Fri, 9AM - 6PM</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="icon">📍</span>
                    <div>
                        <strong>Location</strong>
                        <p>Kuala Lumpur, Malaysia</p>
                    </div>
                </div>
                <div class="contact-note">
                    For order inquiries, please include your Order ID. We aim to respond within 24 hours.
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="contact-card form-card">
            <h3 class="contact-title">Send a Message</h3>
            <form method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required placeholder="How can we help you?"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>