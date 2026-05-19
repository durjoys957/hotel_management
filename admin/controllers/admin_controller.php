<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('admin');
require_once __DIR__ . '/../models/AdminModel.php';

$model  = new AdminModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    // Room Types
    case 'create_room_type':  createRoomType($model);  break;
    case 'update_room_type':  updateRoomType($model);  break;
    case 'delete_room_type':  deleteRoomType($model);  break;
    // Rooms
    case 'create_room':       createRoom($model);      break;
    case 'update_room':       updateRoom($model);      break;
    case 'delete_room':       deleteRoom($model);      break;
    // Seasonal
    case 'create_seasonal':   createSeasonal($model);  break;
    case 'update_seasonal':   updateSeasonal($model);  break;
    case 'delete_seasonal':   deleteSeasonal($model);  break;
    // Staff
    case 'create_staff':      createStaff($model);     break;
    case 'update_staff':      updateStaff($model);     break;
    case 'toggle_user':       toggleUser($model);      break;
    // Reviews
    case 'reply_review':      replyReview($model);     break;
    // Announcements
    case 'create_announcement':createAnnouncement($model,$userId); break;
    case 'delete_announcement':deleteAnnouncement($model); break;
    // Profile
    case 'update_admin_profile':  updateAdminProfile($model, $userId);  break;
    case 'change_admin_password': changeAdminPassword($model, $userId); break;
    default:
        header('Location: ' . BASE_URL . '/admin/views/dashboard.php'); exit;
}

function createRoomType($model) {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price_per_night'] ?? 0);
    $cap   = (int)($_POST['max_capacity'] ?? 2);
    $amen  = $_POST['amenities'] ?? [];
    if (!$name || $price <= 0) { flashMessage('error','Name and price are required'); back(); }
    $thumb = uploadThumb();
    $model->createRoomType($name,$desc,$price,$cap,$amen,$thumb);
    flashMessage('success','Room type created');
    header('Location:'.BASE_URL.'/admin/views/room_types.php'); exit;
}

function updateRoomType($model) {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price_per_night'] ?? 0);
    $cap   = (int)($_POST['max_capacity'] ?? 2);
    $amen  = $_POST['amenities'] ?? [];
    if (!$name || $price <= 0) { flashMessage('error','Name and price are required'); back(); }
    $thumb = uploadThumb();
    $model->updateRoomType($id,$name,$desc,$price,$cap,$amen,$thumb ?: null);
    flashMessage('success','Room type updated');
    header('Location:'.BASE_URL.'/admin/views/room_types.php'); exit;
}

function deleteRoomType($model) {
    $id = (int)($_POST['id'] ?? 0);
    $model->deleteRoomType($id);
    flashMessage('success','Room type deleted');
    header('Location:'.BASE_URL.'/admin/views/room_types.php'); exit;
}

function createRoom($model) {
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $number = trim($_POST['room_number'] ?? '');
    $floor  = (int)($_POST['floor'] ?? 1);
    $status = $_POST['status'] ?? 'available';
    $notes  = trim($_POST['notes'] ?? '');
    if (!$typeId || !$number) { flashMessage('error','Room type and number required'); back(); }
    $model->createRoom($typeId,$number,$floor,$status,$notes);
    flashMessage('success','Room created');
    header('Location:'.BASE_URL.'/admin/views/rooms.php'); exit;
}

function updateRoom($model) {
    $id     = (int)($_POST['id'] ?? 0);
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $number = trim($_POST['room_number'] ?? '');
    $floor  = (int)($_POST['floor'] ?? 1);
    $status = $_POST['status'] ?? 'available';
    $notes  = trim($_POST['notes'] ?? '');
    $model->updateRoom($id,$typeId,$number,$floor,$status,$notes);
    flashMessage('success','Room updated');
    header('Location:'.BASE_URL.'/admin/views/rooms.php'); exit;
}

function deleteRoom($model) {
    $id = (int)($_POST['id'] ?? 0);
    $model->deleteRoom($id);
    flashMessage('success','Room deleted (if no active bookings)');
    header('Location:'.BASE_URL.'/admin/views/rooms.php'); exit;
}

function createSeasonal($model) {
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $label  = trim($_POST['label'] ?? '');
    $start  = $_POST['start_date'] ?? '';
    $end    = $_POST['end_date'] ?? '';
    $price  = (float)($_POST['price_per_night'] ?? 0);
    if (!$typeId||!$label||!$start||!$end||$price<=0) { flashMessage('error','All fields required'); back(); }
    $model->createSeasonalPricing($typeId,$label,$start,$end,$price);
    flashMessage('success','Seasonal pricing created');
    header('Location:'.BASE_URL.'/admin/views/seasonal.php'); exit;
}

function updateSeasonal($model) {
    $id     = (int)($_POST['id'] ?? 0);
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $label  = trim($_POST['label'] ?? '');
    $start  = $_POST['start_date'] ?? '';
    $end    = $_POST['end_date'] ?? '';
    $price  = (float)($_POST['price_per_night'] ?? 0);
    $model->updateSeasonalPricing($id,$typeId,$label,$start,$end,$price);
    flashMessage('success','Seasonal pricing updated');
    header('Location:'.BASE_URL.'/admin/views/seasonal.php'); exit;
}

function deleteSeasonal($model) {
    $id = (int)($_POST['id'] ?? 0);
    $model->deleteSeasonalPricing($id);
    flashMessage('success','Seasonal pricing deleted');
    header('Location:'.BASE_URL.'/admin/views/seasonal.php'); exit;
}

function createStaff($model) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $role     = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $allowed  = ['receptionist','housekeeping','admin'];
    if (!$name||!$email||!$role||!$password) { flashMessage('error','All fields required'); back(); }
    if (!in_array($role,$allowed)) { flashMessage('error','Invalid role'); back(); }
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { flashMessage('error','Invalid email'); back(); }
    if (strlen($password)<6) { flashMessage('error','Password min 6 chars'); back(); }
    $model->createStaff($name,$email,$phone,$role,$password);
    flashMessage('success','Staff account created');
    header('Location:'.BASE_URL.'/admin/views/staff.php'); exit;
}

function updateStaff($model) {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$name||!$email) { flashMessage('error','Name and email required'); back(); }
    $model->updateStaff($id,$name,$email,$phone);
    flashMessage('success','Staff updated');
    header('Location:'.BASE_URL.'/admin/views/staff.php'); exit;
}

function toggleUser($model) {
    $id     = (int)($_POST['id'] ?? 0);
    $active = (int)($_POST['active'] ?? 1);
    $model->toggleUserActive($id,$active);
    flashMessage('success','Account status updated');
    back();
}

function replyReview($model) {
    $id    = (int)($_POST['review_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');
    if (!$reply) { flashMessage('error','Reply cannot be empty'); back(); }
    $model->replyToReview($id,$reply);
    flashMessage('success','Reply posted');
    header('Location:'.BASE_URL.'/admin/views/reviews.php'); exit;
}

function createAnnouncement($model,$userId) {
    $title = trim($_POST['title'] ?? '');
    $msg   = trim($_POST['message'] ?? '');
    if (!$title||!$msg) { flashMessage('error','Title and message required'); back(); }
    $model->createAnnouncement($title,$msg,$userId);
    flashMessage('success','Announcement posted');
    header('Location:'.BASE_URL.'/admin/views/announcements.php'); exit;
}

function deleteAnnouncement($model) {
    $id = (int)($_POST['id'] ?? 0);
    $model->deleteAnnouncement($id);
    flashMessage('success','Announcement deleted');
    header('Location:'.BASE_URL.'/admin/views/announcements.php'); exit;
}

function uploadThumb() {
    if (!empty($_FILES['thumbnail']['name'])) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp'])) {
            $filename = 'room_'.time().'.'.$ext;
            $dest = UPLOAD_DIR.'room_images/'.$filename;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'],$dest)) {
                return 'uploads/room_images/'.$filename;
            }
        }
    }
    return null;
}

function updateAdminProfile($model, $userId) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$name) {
        flashMessage('error', 'Name is required');
        header('Location: ' . BASE_URL . '/admin/views/profile.php');
        exit;
    }

    // Handle profile picture upload
    $pic = null;
    if (!empty($_FILES['profile_pic']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            flashMessage('error', 'Invalid image format. Use jpg, png or webp.');
            header('Location: ' . BASE_URL . '/admin/views/profile.php');
            exit;
        }
        $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $dest     = UPLOAD_DIR . 'profiles/' . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
            $pic = 'uploads/profiles/' . $filename;
        }
    }

    $db = getDB();
    if ($pic) {
        $stmt = $db->prepare("UPDATE users SET name=?, phone=?, profile_pic=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $phone, $pic, $userId);
    } else {
        $stmt = $db->prepare("UPDATE users SET name=?, phone=? WHERE id=?");
        $stmt->bind_param('ssi', $name, $phone, $userId);
    }
    $stmt->execute();
    $stmt->close();

    $_SESSION['name'] = $name;
    flashMessage('success', 'Profile updated successfully');
    header('Location: ' . BASE_URL . '/admin/views/profile.php');
    exit;
}

function changeAdminPassword($model, $userId) {
    $old  = $_POST['old_password']     ?? '';
    $new  = $_POST['new_password']     ?? '';
    $conf = $_POST['confirm_password'] ?? '';

    if (!$old || !$new || !$conf) {
        flashMessage('error', 'All password fields are required');
        header('Location: ' . BASE_URL . '/admin/views/profile.php');
        exit;
    }
    if (strlen($new) < 6) {
        flashMessage('error', 'New password must be at least 6 characters');
        header('Location: ' . BASE_URL . '/admin/views/profile.php');
        exit;
    }
    if ($new !== $conf) {
        flashMessage('error', 'New passwords do not match');
        header('Location: ' . BASE_URL . '/admin/views/profile.php');
        exit;
    }

    $user = $model->getUserById($userId);
    if (!password_verify($old, $user['password_hash'])) {
        flashMessage('error', 'Current password is incorrect');
        header('Location: ' . BASE_URL . '/admin/views/profile.php');
        exit;
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $db   = getDB();
    $stmt = $db->prepare("UPDATE users SET password_hash=? WHERE id=?");
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute();
    $stmt->close();

    flashMessage('success', 'Password changed successfully');
    header('Location: ' . BASE_URL . '/admin/views/profile.php');
    exit;
}

function back() {
    header('Location: '.($_SERVER['HTTP_REFERER'] ?? BASE_URL.'/admin/views/dashboard.php')); exit;
}
