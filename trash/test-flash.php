<?php
require 'includes/functions.php';
set_flash('success', 'Block 1 foundation is ready!');
$flash = get_flash();
echo "<div style='padding:15px; background:#d4edda; color:#155724;'>{$flash['message']}</div>";
?>