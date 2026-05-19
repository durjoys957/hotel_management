<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../models/ReceptionistModel.php';

$model  = new ReceptionistModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'checkin':          doCheckIn($model, $userId);    break;
    case 'checkout':         doCheckOut($model, $userId);   break;
    case 'process_payment':  doPayment($model);             break;
    case 'add_extra':        doAddExtra($model);            break;
    case 'update_service':   doUpdateService($model);       break;
    case 'modify_dates':     doModifyDates($model);         break;
    case 'walkin':           doWalkIn($model, $userId);     break;
    case 'apply_points':     doApplyPoints($model);         break;
    case 'update_profile':   doUpdateProfile($model,$userId); break;
    default:
        header('Location: ' . BASE_URL . '/receptionist/views/dashboard.php'); exit;
}

function doCheckIn($model, $staffId) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $roomId    = (int)($_POST['room_id'] ?? 0);
    if (!$bookingId || !$roomId) { flashMessage('error','Booking and room are required'); back(); }
    $model->checkIn($bookingId, $roomId, $staffId);
    flashMessage('success','Guest checked in successfully');
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doCheckOut($model, $staffId) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    if (!$bookingId) { flashMessage('error','Booking ID required'); back(); }
    // Check bill is paid
    $bill = $model->getBillingForBooking($bookingId);
    if ($bill && $bill['payment_status'] !== 'paid') {
        flashMessage('error','Cannot check out — bill is not settled. Please process payment first.');
        header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
    }
    $model->checkOut($bookingId);
    flashMessage('success','Guest checked out. Room marked for cleaning.');
    header('Location:'.BASE_URL.'/receptionist/views/checkouts.php'); exit;
}

function doPayment($model) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $method    = $_POST['payment_method'] ?? '';
    $allowed   = ['cash','card','online'];
    if (!in_array($method, $allowed)) { flashMessage('error','Invalid payment method'); back(); }
    $model->processPayment($bookingId, $method);
    flashMessage('success','Payment recorded successfully');
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doAddExtra($model) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $amount    = (float)($_POST['amount'] ?? 0);
    $desc      = trim($_POST['description'] ?? '');
    if ($amount <= 0) { flashMessage('error','Amount must be greater than 0'); back(); }
    $model->addExtra($bookingId, $amount, $desc);
    flashMessage('success','Extra charge added');
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doUpdateService($model) {
    $id     = (int)($_POST['service_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (!in_array($status,['in_progress','completed'])) { flashMessage('error','Invalid status'); back(); }
    $model->updateServiceRequest($id, $status);
    flashMessage('success','Service request updated');
    header('Location:'.BASE_URL.'/receptionist/views/services.php'); exit;
}

function doModifyDates($model) {
    $bookingId   = (int)($_POST['booking_id'] ?? 0);
    $newCheckin  = $_POST['new_checkin'] ?? '';
    $newCheckout = $_POST['new_checkout'] ?? '';
    if (!$bookingId || !$newCheckin || !$newCheckout) { flashMessage('error','All fields required'); back(); }
    if ($newCheckin >= $newCheckout) { flashMessage('error','Check-out must be after check-in'); back(); }
    $model->modifyBookingDates($bookingId, $newCheckin, $newCheckout);
    flashMessage('success','Booking dates updated');
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doWalkIn($model, $staffId) {
    $name        = trim($_POST['guest_name'] ?? '');
    $email       = trim($_POST['guest_email'] ?? '');
    $phone       = trim($_POST['guest_phone'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $idNum       = trim($_POST['id_number'] ?? '');
    $roomTypeId  = (int)($_POST['room_type_id'] ?? 0);
    $roomId      = (int)($_POST['room_id'] ?? 0);
    $checkin     = $_POST['checkin_date'] ?? date('Y-m-d');
    $checkout    = $_POST['checkout_date'] ?? '';
    $numGuests   = (int)($_POST['num_guests'] ?? 1);

    if (!$name || !$email || !$roomTypeId || !$roomId || !$checkout) {
        flashMessage('error','All required fields must be filled'); header('Location:'.BASE_URL.'/receptionist/views/walkin.php'); exit;
    }

    // Check or create guest
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param('s',$email); $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc(); $stmt->close();

    $guestId = $existing ? $existing['id'] : $model->createWalkInGuest($name, $email, $phone, $nationality, $idNum);

    $rt = $db->query("SELECT price_per_night FROM room_types WHERE id=$roomTypeId")->fetch_assoc();
    $nights = (strtotime($checkout)-strtotime($checkin))/86400;
    $price  = $nights * $rt['price_per_night'];

    $bookingId = $model->createWalkInBooking($guestId, $roomId, $roomTypeId, $checkin, $checkout, $numGuests, $price, $staffId);
    flashMessage('success','Walk-in booking created. Booking #'.$bookingId);
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doApplyPoints($model) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $guestId   = (int)($_POST['guest_id'] ?? 0);
    $points    = (int)($_POST['points'] ?? 0);
    if ($points <= 0) { flashMessage('error','Enter valid points'); back(); }
    $model->applyLoyaltyDiscount($bookingId, $guestId, $points);
    flashMessage('success','Loyalty discount applied');
    header('Location:'.BASE_URL.'/receptionist/views/booking_detail.php?id='.$bookingId); exit;
}

function doUpdateProfile($model, $userId) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$name) { flashMessage('error','Name is required'); header('Location:'.BASE_URL.'/receptionist/views/profile.php'); exit; }
    $model->updateProfile($userId, $name, $phone);
    $_SESSION['name'] = $name;
    flashMessage('success','Profile updated');
    header('Location:'.BASE_URL.'/receptionist/views/profile.php'); exit;
}

function back() {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL.'/receptionist/views/dashboard.php')); exit;
}
