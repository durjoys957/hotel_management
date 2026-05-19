<?php
// Detect base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
// Find the project root folder name
$parts = explode('/', trim($script, '/'));
$projectFolder = $parts[0] ?? 'hotel_management';
define('BASE_URL', $protocol . '://' . $host . '/' . $projectFolder);
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('CANCELLATION_DAYS_BEFORE', 2); // Allow cancellation if > 2 days before check-in
define('POINTS_PER_BDT', 1);           // 1 point per BDT spent
define('POINTS_VALUE', 0.50);          // 1 point = 0.50 BDT discount

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/includes/auth.php';
