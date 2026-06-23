<?php
// Prevent multiple session starts
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1);      // JS cannot access session cookie
    ini_set('session.cookie_samesite', 'Lax');   // CSRF protection
    ini_set('session.use_strict_mode', 1);       // Reject uninitialized IDs
    
    session_start();
}

// Error reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Regenerate session ID on login (Prevent same session)
function secure_login() {
    session_regenerate_id(true);
}
?>