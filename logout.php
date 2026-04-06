<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Logout the user
logoutUser();

// Redirect to login page
redirect('login.php');
?>
