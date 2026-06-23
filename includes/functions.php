<?php
require_once __DIR__ . '/database.php';


// prevent XSS
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

//  Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require specific role. Redirects if not authorized.

function require_role($allowed_roles) {
    if (!is_logged_in()) {
        redirect('login.php');
    }
    
    $user_role = $_SESSION['role'] ?? 'consumer';
    $allowed_roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
    
    if (!in_array($user_role, $allowed_roles)) {
        redirect('index.php');
    }
}

// Safe redirect with headers
function redirect($url) {
    header("Location: $url");
    exit();
}

// Format currency consistently

function format_price($amount) {
    return 'RM ' . number_format($amount, 2);
}

// Flash Messages (Temporary notifications)

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>