<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php?error=Please+log+in+to+continue');
        exit;
    }
    if ($role && $_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/index.php?error=Access+denied');
        exit;
    }
}

function requireRole($roles) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    if (is_array($roles) && !in_array($_SESSION['role'], $roles)) {
        header('Location: ' . BASE_URL . '/index.php?error=Access+denied');
        exit;
    }
    if (is_string($roles) && $_SESSION['role'] !== $roles) {
        header('Location: ' . BASE_URL . '/index.php?error=Access+denied');
        exit;
    }
}

function currentUser() {
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['name'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'email'=> $_SESSION['email'] ?? '',
    ];
}

function redirectToDashboard() {
    $role = $_SESSION['role'] ?? '';
    $map = [
        'guest'        => BASE_URL . '/guest/views/dashboard.php',
        'receptionist' => BASE_URL . '/receptionist/views/dashboard.php',
        'housekeeping' => BASE_URL . '/housekeeping/views/dashboard.php',
        'admin'        => BASE_URL . '/admin/views/dashboard.php',
    ];
    header('Location: ' . ($map[$role] ?? BASE_URL . '/index.php'));
    exit;
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
