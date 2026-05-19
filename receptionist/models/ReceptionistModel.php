<?php
class ReceptionistModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function getDashboardStats() {
        $today = date('Y-m-d');
        $stats = [];

        $r = $this->db->query("SELECT COUNT(*) c FROM bookings WHERE checkin_date='$today' AND status='confirmed'");
        $stats['expected_checkins'] = $r->fetch_assoc()['c'];

        $r = $this->db->query("SELECT COUNT(*) c FROM bookings WHERE checkout_date='$today' AND status='checked_in'");
        $stats['expected_checkouts'] = $r->fetch_assoc()['c'];

        $r = $this->db->query("SELECT COUNT(*) c FROM bookings WHERE status='checked_in'");
        $stats['current_guests'] = $r->fetch_assoc()['c'];

        $r = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'");
        $stats['available_rooms'] = $r->fetch_assoc()['c'];

        $r = $this->db->query("SELECT COALESCE(SUM(total_amount),0) rev FROM billing WHERE payment_status='paid' AND DATE(paid_at)='$today'");
        $stats['today_revenue'] = $r->fetch_assoc()['rev'];

        $r = $this->db->query("SELECT COUNT(*) c FROM service_requests WHERE status='pending'");
        $stats['pending_services'] = $r->fetch_assoc()['c'];

        return $stats;
    }

    public function getTodayCheckins() {
        $today = date('Y-m-d');
        $stmt  = $this->db->prepare("SELECT b.*, u.name guest_name, u.email, u.phone, u.id_number, rt.name type_name FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id WHERE b.checkin_date=? AND b.status='confirmed' ORDER BY b.created_at");
        $stmt->bind_param('s', $today); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getTodayCheckouts() {
        $today = date('Y-m-d');
        $stmt  = $this->db->prepare("SELECT b.*, u.name guest_name, rt.name type_name, r.room_number FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id JOIN rooms r ON r.id=b.room_id WHERE b.checkout_date=? AND b.status='checked_in' ORDER BY b.created_at");
        $stmt->bind_param('s', $today); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function searchBooking($query) {
        $q = '%'.$query.'%';
        $stmt = $this->db->prepare("SELECT b.*, u.name guest_name, u.phone, rt.name type_name, r.room_number FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id LEFT JOIN rooms r ON r.id=b.room_id WHERE b.id LIKE ? OR u.name LIKE ? OR u.phone LIKE ? ORDER BY b.created_at DESC LIMIT 20");
        $stmt->bind_param('sss',$q,$q,$q); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getBookingById($id) {
        $stmt = $this->db->prepare("SELECT b.*, u.name guest_name, u.email, u.phone, u.id_number, u.nationality, rt.name type_name, r.room_number, r.floor FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id LEFT JOIN rooms r ON r.id=b.room_id WHERE b.id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function getAvailableRoomsOfType($roomTypeId) {
        $stmt = $this->db->prepare("SELECT r.* FROM rooms r WHERE r.room_type_id=? AND r.status='available' ORDER BY r.room_number");
        $stmt->bind_param('i',$roomTypeId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function checkIn($bookingId, $roomId, $staffId) {
        $db = $this->db;
        // Update booking
        $stmt = $db->prepare("UPDATE bookings SET status='checked_in', room_id=? WHERE id=? AND status='confirmed'");
        $stmt->bind_param('ii',$roomId,$bookingId); $stmt->execute(); $stmt->close();
        // Update room
        $stmt2 = $db->prepare("UPDATE rooms SET status='occupied' WHERE id=?");
        $stmt2->bind_param('i',$roomId); $stmt2->execute(); $stmt2->close();
    }

    public function checkOut($bookingId) {
        $booking = $this->getBookingById($bookingId);
        if (!$booking) return false;
        $roomId = $booking['room_id'];
        $stmt = $this->db->prepare("UPDATE bookings SET status='checked_out' WHERE id=? AND status='checked_in'");
        $stmt->bind_param('i',$bookingId); $stmt->execute(); $stmt->close();
        // Mark room dirty
        $stmt2 = $this->db->prepare("UPDATE rooms SET status='dirty' WHERE id=?");
        $stmt2->bind_param('i',$roomId); $stmt2->execute(); $stmt2->close();
        return true;
    }

    public function getBillingForBooking($bookingId) {
        $stmt = $this->db->prepare("SELECT bi.*, b.checkin_date, b.checkout_date, rt.name type_name, u.name guest_name FROM billing bi JOIN bookings b ON b.id=bi.booking_id JOIN room_types rt ON rt.id=b.room_type_id JOIN users u ON u.id=bi.guest_id WHERE bi.booking_id=?");
        $stmt->bind_param('i',$bookingId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function processPayment($bookingId, $method) {
        $stmt = $this->db->prepare("UPDATE billing SET payment_method=?, payment_status='paid', paid_at=NOW(), total_amount=base_amount+extras_amount-discount_amount WHERE booking_id=?");
        $stmt->bind_param('si',$method,$bookingId); $stmt->execute(); $stmt->close();
    }

    public function applyLoyaltyDiscount($bookingId, $guestId, $points) {
        $discount = $points * POINTS_VALUE;
        $stmt = $this->db->prepare("UPDATE billing SET discount_amount=discount_amount+?, total_amount=total_amount-? WHERE booking_id=?");
        $stmt->bind_param('ddi',$discount,$discount,$bookingId); $stmt->execute(); $stmt->close();

        $stmt2 = $this->db->prepare("INSERT INTO loyalty_points (guest_id,booking_id,points_earned,points_used,balance,created_at) VALUES (?,?,0,?,?,NOW())");
        $neg = -$points;
        $stmt2->bind_param('iiii',$guestId,$bookingId,$points,$neg); $stmt2->execute(); $stmt2->close();
    }

    public function addExtra($bookingId, $amount, $desc) {
        $stmt = $this->db->prepare("UPDATE billing SET extras_amount=extras_amount+? WHERE booking_id=?");
        $stmt->bind_param('di',$amount,$bookingId); $stmt->execute(); $stmt->close();
    }

    // Walk-in
    public function createWalkInGuest($name, $email, $phone, $nationality, $idNum) {
        $hash = password_hash('walkin' . time(), PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name,email,password_hash,phone,nationality,id_number,role) VALUES (?,?,?,?,?,?,'guest')");
        $stmt->bind_param('ssssss', $name, $email, $hash, $phone, $nationality, $idNum);
        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        // Initialise loyalty points record for new guest
        $stmt2 = $this->db->prepare("INSERT INTO loyalty_points (guest_id,points_earned,points_used,balance) VALUES (?,0,0,0)");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $stmt2->close();

        return $id;
    }

    public function createWalkInBooking($guestId, $roomId, $roomTypeId, $checkin, $checkout, $numGuests, $price, $staffId) {
        $stmt = $this->db->prepare("INSERT INTO bookings (guest_id,room_id,room_type_id,checkin_date,checkout_date,num_guests,total_price,status,source) VALUES (?,?,?,?,?,?,?,'checked_in','walk_in')");
        $stmt->bind_param('iiissid',$guestId,$roomId,$roomTypeId,$checkin,$checkout,$numGuests,$price);
        $stmt->execute(); $id = $this->db->insert_id; $stmt->close();
        // Billing
        $stmt2 = $this->db->prepare("INSERT INTO billing (booking_id,guest_id,base_amount,total_amount,payment_status) VALUES (?,?,?,?,'pending')");
        $stmt2->bind_param('iidd',$id,$guestId,$price,$price); $stmt2->execute(); $stmt2->close();
        // Room occupied
        $stmt3 = $this->db->prepare("UPDATE rooms SET status='occupied' WHERE id=?");
        $stmt3->bind_param('i',$roomId); $stmt3->execute(); $stmt3->close();
        return $id;
    }

    public function getServiceRequests($status = null) {
        $sql = "SELECT sr.*, u.name guest_name, r.room_number, b.checkin_date FROM service_requests sr JOIN users u ON u.id=sr.guest_id JOIN rooms r ON r.id=sr.room_id JOIN bookings b ON b.id=sr.booking_id";
        if ($status) $sql .= " WHERE sr.status='$status'";
        $sql .= " ORDER BY sr.requested_at DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function updateServiceRequest($id, $status) {
        $stmt = $this->db->prepare("UPDATE service_requests SET status=? WHERE id=?");
        $stmt->bind_param('si',$status,$id); $stmt->execute(); $stmt->close();
    }

    public function modifyBookingDates($bookingId, $newCheckin, $newCheckout) {
        // Recalc price
        $b = $this->getBookingById($bookingId);
        $nights = (strtotime($newCheckout)-strtotime($newCheckin))/86400;
        $rt = $this->db->query("SELECT price_per_night FROM room_types WHERE id=".(int)$b['room_type_id'])->fetch_assoc();
        $newTotal = $nights * $rt['price_per_night'];
        $stmt = $this->db->prepare("UPDATE bookings SET checkin_date=?,checkout_date=?,total_price=? WHERE id=?");
        $stmt->bind_param('ssdi',$newCheckin,$newCheckout,$newTotal,$bookingId); $stmt->execute(); $stmt->close();
        $stmt2 = $this->db->prepare("UPDATE billing SET base_amount=?,total_amount=? WHERE booking_id=?");
        $stmt2->bind_param('ddi',$newTotal,$newTotal,$bookingId); $stmt2->execute(); $stmt2->close();
    }

    public function getAllRooms() {
        return $this->db->query("SELECT r.*, rt.name type_name FROM rooms r JOIN room_types rt ON rt.id=r.room_type_id ORDER BY r.floor, r.room_number")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllBookings($filters = []) {
        $where = [];
        $params = []; $types = '';
        if (!empty($filters['status']))    { $where[] = 'b.status=?';     $types .= 's'; $params[] = $filters['status']; }
        if (!empty($filters['source']))    { $where[] = 'b.source=?';     $types .= 's'; $params[] = $filters['source']; }
        if (!empty($filters['date_from'])) { $where[] = 'b.checkin_date>=?'; $types .= 's'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))   { $where[] = 'b.checkout_date<=?';$types .= 's'; $params[] = $filters['date_to']; }
        $sql = "SELECT b.*, u.name guest_name, u.phone, rt.name type_name, r.room_number FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id LEFT JOIN rooms r ON r.id=b.room_id";
        if ($where) $sql .= ' WHERE '.implode(' AND ',$where);
        $sql .= ' ORDER BY b.created_at DESC LIMIT 100';
        if ($types) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types,...$params); $stmt->execute();
            $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
        }
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getDailyReport($date) {
        $report = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM bookings WHERE checkin_date=? AND status IN ('checked_in','checked_out')");
        $stmt->bind_param('s',$date); $stmt->execute();
        $report['arrivals'] = $stmt->get_result()->fetch_assoc()['c']; $stmt->close();

        $stmt2 = $this->db->prepare("SELECT COUNT(*) c FROM bookings WHERE checkout_date=? AND status='checked_out'");
        $stmt2->bind_param('s',$date); $stmt2->execute();
        $report['departures'] = $stmt2->get_result()->fetch_assoc()['c']; $stmt2->close();

        $stmt3 = $this->db->prepare("SELECT COUNT(*) c FROM bookings WHERE checkin_date=? AND source='walk_in'");
        $stmt3->bind_param('s',$date); $stmt3->execute();
        $report['walkins'] = $stmt3->get_result()->fetch_assoc()['c']; $stmt3->close();

        $stmt4 = $this->db->prepare("SELECT COALESCE(SUM(total_amount),0) rev FROM billing WHERE payment_status='paid' AND DATE(paid_at)=?");
        $stmt4->bind_param('s',$date); $stmt4->execute();
        $report['revenue'] = $stmt4->get_result()->fetch_assoc()['rev']; $stmt4->close();

        $report['occupied'] = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
        $report['available']= $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()['c'];
        return $report;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r ?? [];
    }

    public function updateProfile($id,$name,$phone) {
        $stmt = $this->db->prepare("UPDATE users SET name=?,phone=? WHERE id=?");
        $stmt->bind_param('ssi',$name,$phone,$id); $stmt->execute(); $stmt->close();
    }

    public function getRoomTypes() {
        return $this->db->query("SELECT * FROM room_types ORDER BY price_per_night")->fetch_all(MYSQLI_ASSOC);
    }
}
