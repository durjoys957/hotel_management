<?php
class GuestModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    // ── Profile ────────────────────────────────────────────
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r ?? [];
    }

    public function updateProfile($id, $name, $phone, $nationality, $idNumber, $pic = null) {
        if ($pic) {
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, nationality=?, id_number=?, profile_pic=? WHERE id=?");
            $stmt->bind_param('sssssi', $name, $phone, $nationality, $idNumber, $pic, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, nationality=?, id_number=? WHERE id=?");
            $stmt->bind_param('ssssi', $name, $phone, $nationality, $idNumber, $id);
        }
        $stmt->execute(); $stmt->close();
    }

    public function changePassword($id, $newHash) {
        $stmt = $this->db->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt->bind_param('si', $newHash, $id); $stmt->execute(); $stmt->close();
    }

    // ── Room Types / Search ────────────────────────────────
    public function getRoomTypes() {
        return $this->db->query("SELECT * FROM room_types ORDER BY price_per_night")->fetch_all(MYSQLI_ASSOC);
    }

    public function getRoomTypeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM room_types WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($r && $r['amenities']) $r['amenities'] = json_decode($r['amenities'], true);
        return $r;
    }

    public function searchAvailableRooms($checkin, $checkout, $guests) {
        $stmt = $this->db->prepare("
            SELECT rt.*,
                   COUNT(r.id) AS available_rooms,
                   (SELECT AVG(overall_rating) FROM reviews rv
                    JOIN bookings b ON b.id = rv.booking_id
                    WHERE b.room_type_id = rt.id) AS avg_rating,
                   (SELECT AVG(cleanliness_rating) FROM reviews rv
                    JOIN bookings b ON b.id = rv.booking_id
                    WHERE b.room_type_id = rt.id) AS avg_cleanliness,
                   (SELECT AVG(service_rating) FROM reviews rv
                    JOIN bookings b ON b.id = rv.booking_id
                    WHERE b.room_type_id = rt.id) AS avg_service
            FROM room_types rt
            JOIN rooms r ON r.room_type_id = rt.id
            WHERE rt.max_capacity >= ?
              AND r.status = 'available'
              AND r.id NOT IN (
                  SELECT room_id FROM bookings
                  WHERE room_id IS NOT NULL
                    AND status IN ('confirmed','checked_in')
                    AND checkin_date  < ?
                    AND checkout_date > ?
              )
            GROUP BY rt.id
            HAVING available_rooms > 0
            ORDER BY rt.price_per_night
        ");
        $stmt->bind_param('iss', $guests, $checkout, $checkin);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        foreach ($r as &$row) {
            if ($row['amenities']) $row['amenities'] = json_decode($row['amenities'], true);
        }
        return $r;
    }

    public function getSeasonalPrice($roomTypeId, $checkin, $checkout) {
        $stmt = $this->db->prepare("
            SELECT * FROM seasonal_pricing
            WHERE room_type_id = ?
              AND start_date <= ? AND end_date >= ?
            ORDER BY price_per_night DESC LIMIT 1
        ");
        $stmt->bind_param('iss', $roomTypeId, $checkout, $checkin);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    public function getRoomTypeRatings($roomTypeId) {
        $stmt = $this->db->prepare("
            SELECT AVG(overall_rating) ov, AVG(cleanliness_rating) cl,
                   AVG(service_rating) sv, COUNT(*) cnt
            FROM reviews rv
            JOIN bookings b ON b.id = rv.booking_id
            WHERE b.room_type_id = ?
        ");
        $stmt->bind_param('i', $roomTypeId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    public function getReviewsForRoomType($roomTypeId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT rv.*, u.name guest_name
            FROM reviews rv
            JOIN bookings b ON b.id = rv.booking_id
            JOIN users u ON u.id = rv.guest_id
            WHERE b.room_type_id = ?
            ORDER BY rv.created_at DESC LIMIT ?
        ");
        $stmt->bind_param('ii', $roomTypeId, $limit); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    // ── Bookings ───────────────────────────────────────────
    public function createBooking($guestId, $roomTypeId, $checkin, $checkout, $numGuests, $totalPrice, $specialRequests) {
        $stmt = $this->db->prepare("
            INSERT INTO bookings (guest_id, room_type_id, checkin_date, checkout_date,
                                  num_guests, total_price, status, source, special_requests, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', 'online', ?, NOW())
        ");
        $stmt->bind_param('iissids', $guestId, $roomTypeId, $checkin, $checkout,
                          $numGuests, $totalPrice, $specialRequests);
        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $id;
    }

    public function getBookingById($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, rt.name type_name, rt.price_per_night, rt.thumbnail_path,
                   r.room_number, bi.total_amount, bi.payment_status, bi.receipt_path,
                   bi.base_amount, bi.extras_amount, bi.discount_amount, bi.payment_method
            FROM bookings b
            JOIN room_types rt ON rt.id = b.room_type_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN billing bi ON bi.booking_id = b.id
            WHERE b.id = ?
        ");
        $stmt->bind_param('i', $id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    public function getGuestBookings($guestId) {
        $stmt = $this->db->prepare("
            SELECT b.*, rt.name type_name, rt.thumbnail_path, r.room_number,
                   bi.payment_status, bi.total_amount
            FROM bookings b
            JOIN room_types rt ON rt.id = b.room_type_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN billing bi ON bi.booking_id = b.id
            WHERE b.guest_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    public function cancelBooking($id, $guestId) {
        $stmt = $this->db->prepare("
            UPDATE bookings SET status='cancelled'
            WHERE id=? AND guest_id=? AND status IN ('pending','confirmed')
        ");
        $stmt->bind_param('ii', $id, $guestId); $stmt->execute();
        $affected = $stmt->affected_rows; $stmt->close();
        return $affected > 0;
    }

    public function requestModification($bookingId, $guestId, $newCheckin, $newCheckout) {
        // Store modification request as a special service request note
        $desc = "Date change request: $newCheckin to $newCheckout";
        $stmt = $this->db->prepare("
            INSERT INTO service_requests (booking_id, guest_id, room_id, service_type,
                                          description, status, requested_at)
            SELECT ?, ?, room_id, 'other', ?, 'pending', NOW()
            FROM bookings WHERE id=? AND guest_id=?
        ");
        $stmt->bind_param('iisii', $bookingId, $guestId, $desc, $bookingId, $guestId);
        $stmt->execute(); $stmt->close();
    }

    // ── Service Requests ───────────────────────────────────
    public function createServiceRequest($bookingId, $guestId, $roomId, $type, $description) {
        $stmt = $this->db->prepare("
            INSERT INTO service_requests (booking_id, guest_id, room_id, service_type,
                                          description, status, requested_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param('iiiss', $bookingId, $guestId, $roomId, $type, $description);
        $stmt->execute(); $stmt->close();
    }

    public function getServiceRequestsByGuest($guestId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, r.room_number, b.checkin_date, b.checkout_date
            FROM service_requests sr
            LEFT JOIN rooms r ON r.id = sr.room_id
            JOIN bookings b ON b.id = sr.booking_id
            WHERE sr.guest_id = ?
            ORDER BY sr.requested_at DESC
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    public function getActiveBookingForGuest($guestId) {
        $stmt = $this->db->prepare("
            SELECT b.*, rt.name type_name, r.room_number
            FROM bookings b
            JOIN room_types rt ON rt.id = b.room_type_id
            LEFT JOIN rooms r ON r.id = b.room_id
            WHERE b.guest_id = ? AND b.status = 'checked_in'
            LIMIT 1
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    // ── Reviews ────────────────────────────────────────────
    public function createReview($bookingId, $guestId, $overall, $cleanliness, $service, $text) {
        $stmt = $this->db->prepare("
            INSERT INTO reviews (booking_id, guest_id, overall_rating, cleanliness_rating,
                                  service_rating, review_text, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('iiiiss', $bookingId, $guestId, $overall, $cleanliness, $service, $text);
        $stmt->execute(); $stmt->close();
    }

    public function updateReview($id, $guestId, $overall, $cleanliness, $service, $text) {
        $stmt = $this->db->prepare("
            UPDATE reviews SET overall_rating=?, cleanliness_rating=?, service_rating=?, review_text=?
            WHERE id=? AND guest_id=?
        ");
        $stmt->bind_param('iiisii', $overall, $cleanliness, $service, $text, $id, $guestId);
        $stmt->execute(); $stmt->close();
    }

    public function deleteReview($id, $guestId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id=? AND guest_id=?");
        $stmt->bind_param('ii', $id, $guestId); $stmt->execute(); $stmt->close();
    }

    public function getReviewsByGuest($guestId) {
        $stmt = $this->db->prepare("
            SELECT rv.*, rt.name type_name, b.checkin_date, b.checkout_date
            FROM reviews rv
            JOIN bookings b ON b.id = rv.booking_id
            JOIN room_types rt ON rt.id = b.room_type_id
            WHERE rv.guest_id = ?
            ORDER BY rv.created_at DESC
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    public function getReviewByBooking($bookingId, $guestId) {
        $stmt = $this->db->prepare("SELECT * FROM reviews WHERE booking_id=? AND guest_id=?");
        $stmt->bind_param('ii', $bookingId, $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    public function hasReviewed($bookingId, $guestId) {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE booking_id=? AND guest_id=?");
        $stmt->bind_param('ii', $bookingId, $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r !== null;
    }

    // ── Loyalty Points ─────────────────────────────────────
    public function getLoyaltyBalance($guestId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(points_earned) - SUM(points_used), 0) AS balance
            FROM loyalty_points WHERE guest_id = ?
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return (int)($r['balance'] ?? 0);
    }

    public function getLoyaltyHistory($guestId) {
        $stmt = $this->db->prepare("
            SELECT lp.*, b.checkin_date, b.checkout_date, rt.name type_name
            FROM loyalty_points lp
            JOIN bookings b ON b.id = lp.booking_id
            JOIN room_types rt ON rt.id = b.room_type_id
            WHERE lp.guest_id = ?
            ORDER BY lp.created_at DESC
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    public function addLoyaltyPoints($guestId, $bookingId, $earned, $used = 0) {
        $balance = $this->getLoyaltyBalance($guestId) + $earned - $used;
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_points (guest_id, booking_id, points_earned, points_used, balance, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('iiiii', $guestId, $bookingId, $earned, $used, $balance);
        $stmt->execute(); $stmt->close();
    }

    public function redeemPoints($guestId, $bookingId, $points) {
        $balance = $this->getLoyaltyBalance($guestId);
        if ($points > $balance) return false;
        $newBalance = $balance - $points;
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_points (guest_id, booking_id, points_earned, points_used, balance, created_at)
            VALUES (?, ?, 0, ?, ?, NOW())
        ");
        $stmt->bind_param('iiii', $guestId, $bookingId, $points, $newBalance);
        $stmt->execute(); $stmt->close();
        return true;
    }

    // ── Billing ────────────────────────────────────────────
    public function getBillingHistory($guestId) {
        $stmt = $this->db->prepare("
            SELECT bi.*, b.checkin_date, b.checkout_date, rt.name type_name, r.room_number
            FROM billing bi
            JOIN bookings b ON b.id = bi.booking_id
            JOIN room_types rt ON rt.id = b.room_type_id
            LEFT JOIN rooms r ON r.id = b.room_id
            WHERE bi.guest_id = ?
            ORDER BY bi.paid_at DESC
        ");
        $stmt->bind_param('i', $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
        return $r;
    }

    public function getBillingByBooking($bookingId, $guestId) {
        $stmt = $this->db->prepare("
            SELECT bi.*, b.checkin_date, b.checkout_date, rt.name type_name, r.room_number
            FROM billing bi
            JOIN bookings b ON b.id = bi.booking_id
            JOIN room_types rt ON rt.id = b.room_type_id
            LEFT JOIN rooms r ON r.id = b.room_id
            WHERE bi.booking_id = ? AND bi.guest_id = ?
        ");
        $stmt->bind_param('ii', $bookingId, $guestId); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        return $r;
    }

    // ── Announcements (read-only for guests) ───────────────
    public function getAnnouncements($limit = 5) {
        return $this->db->query("
            SELECT a.*, u.name author FROM announcements a
            JOIN users u ON u.id = a.created_by
            ORDER BY a.created_at DESC LIMIT $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }
}
