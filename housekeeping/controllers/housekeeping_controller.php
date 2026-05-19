<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('housekeeping');
require_once __DIR__ . '/../models/HousekeepingModel.php';

$model  = new HousekeepingModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'create_task':          createTask($model, $userId);       break;
    case 'update_task':          updateTask($model);                break;
    case 'mark_clean':           markClean($model);                 break;
    case 'create_maintenance':   createMaintenance($model, $userId);break;
    case 'update_maintenance':   updateMaintenance($model);        break;
    case 'update_profile':       updateProfile($model, $userId);   break;
    default:
        header('Location: ' . BASE_URL . '/housekeeping/views/dashboard.php'); exit;
}

function createTask($model, $userId) {
    $roomId   = (int)($_POST['room_id'] ?? 0);
    $type     = $_POST['task_type'] ?? '';
    $priority = $_POST['priority'] ?? 'normal';
    $notes    = trim($_POST['notes'] ?? '');
    $date     = $_POST['scheduled_date'] ?? date('Y-m-d');
    if (!$roomId || !$type) { flashMessage('error','Room and task type required'); header('Location:'.BASE_URL.'/housekeeping/views/tasks.php'); exit; }
    $model->createTask($roomId, $userId, $type, $priority, $notes, $date);
    flashMessage('success','Task created successfully');
    header('Location:'.BASE_URL.'/housekeeping/views/tasks.php'); exit;
}

function updateTask($model) {
    $taskId = (int)($_POST['task_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $notes  = trim($_POST['notes'] ?? '');
    if (!in_array($status,['in_progress','done'])) { flashMessage('error','Invalid status'); header('Location:'.BASE_URL.'/housekeeping/views/tasks.php'); exit; }
    $model->updateTaskStatus($taskId, $status, $notes ?: null);
    if ($status === 'done') {
        // Optionally auto-mark room available - user will confirm via mark_clean
        flashMessage('success','Task marked as done');
    } else {
        flashMessage('success','Task updated');
    }
    header('Location:'.BASE_URL.'/housekeeping/views/tasks.php'); exit;
}

function markClean($model) {
    $roomId = (int)($_POST['room_id'] ?? 0);
    if (!$roomId) { flashMessage('error','Room ID required'); header('Location:'.BASE_URL.'/housekeeping/views/rooms.php'); exit; }
    $model->markRoomClean($roomId);
    flashMessage('success','Room marked as available/clean');
    header('Location:'.BASE_URL.'/housekeeping/views/rooms.php'); exit;
}

function createMaintenance($model, $userId) {
    $roomId   = (int)($_POST['room_id'] ?? 0);
    $desc     = trim($_POST['description'] ?? '');
    $severity = $_POST['severity'] ?? 'medium';
    if (!$roomId || !$desc) { flashMessage('error','Room and description required'); header('Location:'.BASE_URL.'/housekeeping/views/maintenance.php'); exit; }
    if (!in_array($severity,['low','medium','high'])) $severity = 'medium';
    $model->createMaintenanceReport($roomId, $userId, $desc, $severity);
    flashMessage('success','Maintenance report logged. Room marked for maintenance.');
    header('Location:'.BASE_URL.'/housekeeping/views/maintenance.php'); exit;
}

function updateMaintenance($model) {
    $id     = (int)($_POST['report_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (!in_array($status,['in_progress','resolved'])) { flashMessage('error','Invalid status'); header('Location:'.BASE_URL.'/housekeeping/views/maintenance.php'); exit; }
    $model->updateMaintenanceReport($id, $status);
    $msg = $status === 'resolved' ? 'Issue resolved. Room restored to available.' : 'Report updated.';
    flashMessage('success', $msg);
    header('Location:'.BASE_URL.'/housekeeping/views/maintenance.php'); exit;
}

function updateProfile($model, $userId) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$name) { flashMessage('error','Name required'); header('Location:'.BASE_URL.'/housekeeping/views/profile.php'); exit; }
    $model->updateProfile($userId, $name, $phone);
    $_SESSION['name'] = $name;
    flashMessage('success','Profile updated');
    header('Location:'.BASE_URL.'/housekeeping/views/profile.php'); exit;
}
