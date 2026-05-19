<?php
require_once __DIR__ . '/../includes/init.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':   handleLogin();    break;
    case 'register':handleRegister(); break;
    case 'logout':  handleLogout();   break;
    default:
        header('Location: ' . BASE_URL . '/index.php');
        exit;
}

function handleLogin() {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        header('Location: ' . BASE_URL . '/index.php?error=Email+and+password+are+required');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: ' . BASE_URL . '/index.php?error=Invalid+email+or+password');
        exit;
    }
    if (!$user['is_active']) {
        header('Location: ' . BASE_URL . '/index.php?error=Your+account+has+been+deactivated');
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    redirectToDashboard();
}

function handleRegister() {
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $nationality      = trim($_POST['nationality'] ?? '');
    $id_number        = trim($_POST['id_number'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password) {
        header('Location: ' . BASE_URL . '/index.php?mode=register&error=Name+email+and+password+are+required');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . '/index.php?mode=register&error=Invalid+email+format');
        exit;
    }
    if (strlen($password) < 6) {
        header('Location: ' . BASE_URL . '/index.php?mode=register&error=Password+must+be+at+least+6+characters');
        exit;
    }
    if ($password !== $confirm_password) {
        header('Location: ' . BASE_URL . '/index.php?mode=register&error=Passwords+do+not+match');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $stmt->close();
        header('Location: ' . BASE_URL . '/index.php?mode=register&error=Email+already+registered');
        exit;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, phone, nationality, id_number, role) VALUES (?,?,?,?,?,?,'guest')");
    $stmt->bind_param('ssssss', $name, $email, $hash, $phone, $nationality, $id_number);
    $stmt->execute();
    $newId = $db->insert_id;
    $stmt->close();

    // Init loyalty balance
    $stmt = $db->prepare("INSERT INTO loyalty_points (guest_id, points_earned, points_used, balance) VALUES (?,0,0,0)");
    $stmt->bind_param('i', $newId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_id'] = $newId;
    $_SESSION['name']    = $name;
    $_SESSION['email']   = $email;
    $_SESSION['role']    = 'guest';
    header('Location: ' . BASE_URL . '/guest/views/dashboard.php');
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php?error=You+have+been+logged+out');
    exit;
}
