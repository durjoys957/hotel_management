<?php
require_once __DIR__ . '/../../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../models/GuestModel.php';

$model  = new GuestModel();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    // Profile
    case 'update_profile':        updateProfile($model, $userId);       break;
    case 'change_password':       changePassword($model, $userId);      break;
    // Bookings
    case 'create_booking':        createBooking($model, $userId);       break;
    case 'cancel_booking':        cancelBooking($model, $userId);       break;
    case 'request_modification':  requestModification($model, $userId); break;
    // Services
    case 'create_service':        createServiceRequest($model, $userId); break;
    // Reviews
    case 'create_review':         createReview($model, $userId);        break;
    case 'update_review':         updateReview($model, $userId);        break;
    case 'delete_review':         deleteReview($model, $userId);        break;
    // AJAX endpoint — search rooms
    case 'search_rooms_ajax':     searchRoomsAjax($model);              break;
    default:
        header('Location: ' . BASE_URL . '/guest/views/dashboard.php'); exit;
}

// ── Profile ──────────────────────────────────────────────────────────────────

function updateProfile($model, $userId) {
    $name        = trim($_POST['name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $idNumber    = trim($_POST['id_number'] ?? '');

    if (!$name) {
        flashMessage('error', 'Name is required');
        back();
    }

    $pic = null;
    if (!empty($_FILES['profile_pic']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            flashMessage('error', 'Invalid image format. Use jpg, png or webp.');
            back();
        }
        $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $dest     = UPLOAD_DIR . 'profiles/' . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
            $pic = 'uploads/profiles/' . $filename;
        }
    }

    $model->updateProfile($userId, $name, $phone, $nationality, $idNumber, $pic);
    $_SESSION['name'] = $name;
    flashMessage('success', 'Profile updated successfully');
    header('Location: ' . BASE_URL . '/guest/views/profile.php'); exit;
}

function changePassword($model, $userId) {
    $old  = $_POST['old_password']     ?? '';
    $new  = $_POST['new_password']     ?? '';
    $conf = $_POST['confirm_password'] ?? '';

    if (!$old || !$new || !$conf) {
        flashMessage('error', 'All password fields are required');
        back();
    }
    if (strlen($new) < 6) {
        flashMessage('error', 'New password must be at least 6 characters');
        back();
    }
    if ($new !== $conf) {
        flashMessage('error', 'New passwords do not match');
        back();
    }

    $user = $model->getUserById($userId);
    if (!password_verify($old, $user['password_hash'])) {
        flashMessage('error', 'Current password is incorrect');
        back();
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $model->changePassword($userId, $hash);
    flashMessage('success', 'Password changed successfully');
    header('Location: ' . BASE_URL . '/guest/views/profile.php'); exit;
}

// ── Bookings ─────────────────────────────────────────────────────────────────

function createBooking($model, $userId) {
    $roomTypeId      = (int)($_POST['room_type_id'] ?? 0);
    $checkin         = $_POST['checkin_date']  ?? '';
    $checkout        = $_POST['checkout_date'] ?? '';
    $numGuests       = (int)($_POST['num_guests'] ?? 1);
    $specialRequests = trim($_POST['special_requests'] ?? '');
    $usePoints       = (int)($_POST['use_points'] ?? 0);

    if (!$roomTypeId || !$checkin || !$checkout) {
        flashMessage('error', 'All booking fields are required');
        back();
    }
    if (strtotime($checkin) >= strtotime($checkout)) {
        flashMessage('error', 'Check-out must be after check-in');
        back();
    }
    if (strtotime($checkin) < strtotime('today')) {
        flashMessage('error', 'Check-in cannot be in the past');
        back();
    }

    $nights    = (int)((strtotime($checkout) - strtotime($checkin)) / 86400);
    $roomType  = $model->getRoomTypeById($roomTypeId);
    if (!$roomType) { flashMessage('error', 'Invalid room type'); back(); }

    // Apply seasonal price if applicable
    $seasonal     = $model->getSeasonalPrice($roomTypeId, $checkin, $checkout);
    $pricePerNight = $seasonal ? $seasonal['price_per_night'] : $roomType['price_per_night'];
    $totalPrice    = $pricePerNight * $nights;

    // Loyalty points discount
    $discount = 0;
    if ($usePoints > 0) {
        $balance  = $model->getLoyaltyBalance($userId);
        $redeem   = min($usePoints, $balance);
        $discount = $redeem * POINTS_VALUE;
        if ($discount > $totalPrice) $discount = $totalPrice;
    }

    $finalPrice = $totalPrice - $discount;
    $bookingId  = $model->createBooking($userId, $roomTypeId, $checkin, $checkout, $numGuests, $finalPrice, $specialRequests);

    // Redeem points if applied
    if ($discount > 0) {
        $pointsUsed = (int)($discount / POINTS_VALUE);
        $model->redeemPoints($userId, $bookingId, $pointsUsed);
    }

    flashMessage('success', 'Booking confirmed! Your booking ID is #' . $bookingId);
    header('Location: ' . BASE_URL . '/guest/views/booking_confirmation.php?id=' . $bookingId); exit;
}

function cancelBooking($model, $userId) {
    $id = (int)($_POST['booking_id'] ?? 0);
    if (!$id) { flashMessage('error', 'Invalid booking'); back(); }

    $booking = $model->getBookingById($id);
    if (!$booking || $booking['guest_id'] != $userId) {
        flashMessage('error', 'Booking not found');
        back();
    }

    // Cancellation policy: only allowed if check-in is more than CANCEL_DAYS_BEFORE days away
    $cancelDays = defined('CANCEL_DAYS_BEFORE') ? CANCEL_DAYS_BEFORE : 2;
    $daysUntilCheckin = (strtotime($booking['checkin_date']) - time()) / 86400;
    if ($daysUntilCheckin < $cancelDays) {
        flashMessage('error', "Cancellations must be made at least $cancelDays days before check-in");
        back();
    }

    $ok = $model->cancelBooking($id, $userId);
    if ($ok) {
        flashMessage('success', 'Booking #' . $id . ' has been cancelled');
    } else {
        flashMessage('error', 'Unable to cancel this booking');
    }
    header('Location: ' . BASE_URL . '/guest/views/bookings.php'); exit;
}

function requestModification($model, $userId) {
    $bookingId   = (int)($_POST['booking_id']   ?? 0);
    $newCheckin  = $_POST['new_checkin_date']  ?? '';
    $newCheckout = $_POST['new_checkout_date'] ?? '';

    if (!$bookingId || !$newCheckin || !$newCheckout) {
        flashMessage('error', 'All fields are required'); back();
    }
    if (strtotime($newCheckin) >= strtotime($newCheckout)) {
        flashMessage('error', 'Check-out must be after check-in'); back();
    }

    $model->requestModification($bookingId, $userId, $newCheckin, $newCheckout);
    flashMessage('success', 'Modification request submitted. A receptionist will confirm the change.');
    header('Location: ' . BASE_URL . '/guest/views/bookings.php'); exit;
}

// ── Service Requests ──────────────────────────────────────────────────────────

function createServiceRequest($model, $userId) {
    $bookingId = (int)($_POST['booking_id']   ?? 0);
    $type      = $_POST['service_type']      ?? '';
    $desc      = trim($_POST['description']  ?? '');
    $roomId    = (int)($_POST['room_id']     ?? 0);

    $allowed = ['extra_bed', 'toiletries', 'laundry', 'room_service', 'other'];
    if (!$bookingId || !$type || !in_array($type, $allowed)) {
        flashMessage('error', 'Invalid service request'); back();
    }

    $model->createServiceRequest($bookingId, $userId, $roomId ?: null, $type, $desc);
    flashMessage('success', 'Service request submitted successfully');
    header('Location: ' . BASE_URL . '/guest/views/services.php'); exit;
}

// ── Reviews ───────────────────────────────────────────────────────────────────

function createReview($model, $userId) {
    $bookingId  = (int)($_POST['booking_id']         ?? 0);
    $overall    = (int)($_POST['overall_rating']      ?? 0);
    $clean      = (int)($_POST['cleanliness_rating']  ?? 0);
    $service    = (int)($_POST['service_rating']      ?? 0);
    $text       = trim($_POST['review_text']          ?? '');

    if (!$bookingId || $overall < 1 || $overall > 5 || $clean < 1 || $service < 1) {
        flashMessage('error', 'Please provide all ratings (1-5)'); back();
    }

    $booking = $model->getBookingById($bookingId);
    if (!$booking || $booking['guest_id'] != $userId || $booking['status'] !== 'checked_out') {
        flashMessage('error', 'You can only review completed stays'); back();
    }
    if ($model->hasReviewed($bookingId, $userId)) {
        flashMessage('error', 'You have already reviewed this stay'); back();
    }

    $model->createReview($bookingId, $userId, $overall, $clean, $service, $text);

    // Award loyalty points for leaving a review (bonus points)
    $model->addLoyaltyPoints($userId, $bookingId, REVIEW_BONUS_POINTS ?? 50);

    flashMessage('success', 'Review submitted. Thank you for your feedback!');
    header('Location: ' . BASE_URL . '/guest/views/reviews.php'); exit;
}

function updateReview($model, $userId) {
    $id      = (int)($_POST['review_id']           ?? 0);
    $overall = (int)($_POST['overall_rating']       ?? 0);
    $clean   = (int)($_POST['cleanliness_rating']   ?? 0);
    $service = (int)($_POST['service_rating']       ?? 0);
    $text    = trim($_POST['review_text']           ?? '');

    if (!$id || $overall < 1 || $clean < 1 || $service < 1) {
        flashMessage('error', 'Please provide all ratings (1-5)'); back();
    }

    $model->updateReview($id, $userId, $overall, $clean, $service, $text);
    flashMessage('success', 'Review updated');
    header('Location: ' . BASE_URL . '/guest/views/reviews.php'); exit;
}

function deleteReview($model, $userId) {
    $id = (int)($_POST['review_id'] ?? 0);
    $model->deleteReview($id, $userId);
    flashMessage('success', 'Review deleted');
    header('Location: ' . BASE_URL . '/guest/views/reviews.php'); exit;
}

// ── AJAX: Search Rooms ────────────────────────────────────────────────────────

function searchRoomsAjax($model) {
    header('Content-Type: application/json');
    $checkin  = $_GET['checkin']  ?? '';
    $checkout = $_GET['checkout'] ?? '';
    $guests   = (int)($_GET['guests'] ?? 1);

    if (!$checkin || !$checkout || strtotime($checkin) >= strtotime($checkout)) {
        echo json_encode(['error' => 'Invalid dates']); exit;
    }

    $rooms = $model->searchAvailableRooms($checkin, $checkout, $guests);
    foreach ($rooms as &$r) {
        $seasonal = $model->getSeasonalPrice($r['id'], $checkin, $checkout);
        if ($seasonal) {
            $r['seasonal']        = $seasonal;
            $r['effective_price'] = $seasonal['price_per_night'];
        } else {
            $r['seasonal']        = null;
            $r['effective_price'] = $r['price_per_night'];
        }
        $nights = (int)((strtotime($checkout) - strtotime($checkin)) / 86400);
        $r['total_price'] = $r['effective_price'] * $nights;
        $r['nights']      = $nights;
    }
    echo json_encode($rooms); exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function back() {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/guest/views/dashboard.php'));
    exit;
}
