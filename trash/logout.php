<?php
require 'includes/config.php';

// Regenerate ID before destroy (prevents session fixation)
session_regenerate_id(true);
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>