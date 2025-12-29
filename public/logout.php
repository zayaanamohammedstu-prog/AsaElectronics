<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Clear session
session_destroy();

// Redirect to home
setFlash('success', 'You have been logged out successfully');
redirect('/public/index.php');
