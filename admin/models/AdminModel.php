<?php
class AdminModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    // ── Dashboard ──────────────────────────────────────────
    public function getDashboardStats() {
        $s = [];
        $total = (int)$this->db->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
        $occupied = (int)$this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
        $s['occupancy_rate'] = $total > 0 ? round($occupied/$total*100) : 0;
        $s['occupied']  = $occupied;
        $s['available'] = (int)$this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()['c'];
        $s['today_revenue'] = (float)$this->db->query("SELECT COALESCE(SUM(total_amount),0) v FROM billing WHERE payment_status='paid'
         AND DATE(paid_at)='".date('Y-m-d')."'")->fetch_assoc()['v'];
        $s['maintenance_issues'] = (int)$this->db->query("SELECT COUNT(*) c FROM maintenance_reports WHERE status!='resolved'")
        ->fetch_assoc()['c'];
        $s['pending_reviews'] = (int)$this->db->query("SELECT COUNT(*) c FROM reviews WHERE admin_reply IS NULL")->fetch_assoc()['c'];
        return $s;
    }

    // ── Room Types ─────────────────────────────────────────
    public function getRoomTypes() {
        return $this->db->query("SELECT rt.*, COUNT(r.id) room_count FROM room_types rt LEFT JOIN rooms r ON r.room_type_id=rt.id GROUP BY rt.id ORDER BY rt.price_per_night")->fetch_all(MYSQLI_ASSOC);
    }
    public function getRoomTypeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM room_types WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($r && $r['amenities']) $r['amenities'] = json_decode($r['amenities'],true);
        return $r;
    }
    public function createRoomType($name,$desc,$price,$cap,$amenities,$thumb) {
        $am = json_encode($amenities);
        $stmt = $this->db->prepare("INSERT INTO room_types (name,description,price_per_night,max_capacity,amenities,thumbnail_path) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('ssdiss',$name,$desc,$price,$cap,$am,$thumb);
        $stmt->execute(); $stmt->close();
    }
    public function updateRoomType($id,$name,$desc,$price,$cap,$amenities,$thumb=null) {
        $am = json_encode($amenities);
        if ($thumb) {
            $stmt = $this->db->prepare("UPDATE room_types SET name=?,description=?,price_per_night=?,max_capacity=?,amenities=?,thumbnail_path=? WHERE id=?");
            $stmt->bind_param('ssdissi',$name,$desc,$price,$cap,$am,$thumb,$id);
        } else {
            $stmt = $this->db->prepare("UPDATE room_types SET name=?,description=?,price_per_night=?,max_capacity=?,amenities=? WHERE id=?");
            $stmt->bind_param('ssdisi',$name,$desc,$price,$cap,$am,$id);
        }
        $stmt->execute(); $stmt->close();
    }
    public function deleteRoomType($id) {
        $stmt = $this->db->prepare("DELETE FROM room_types WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
    }

    // ── Rooms ──────────────────────────────────────────────
    public function getRooms($typeId=null) {
        $sql = "SELECT r.*, rt.name type_name FROM rooms r JOIN room_types rt ON rt.id=r.room_type_id";
        if ($typeId) $sql .= " WHERE r.room_type_id=$typeId";
        $sql .= " ORDER BY r.floor,r.room_number";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    public function getRoomById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }
    public function createRoom($typeId,$number,$floor,$status,$notes) {
        $stmt = $this->db->prepare("INSERT INTO rooms (room_type_id,room_number,floor,status,notes) VALUES (?,?,?,?,?)");
        $stmt->bind_param('iisss',$typeId,$number,$floor,$status,$notes);
        $stmt->execute(); $stmt->close();
    }
    public function updateRoom($id,$typeId,$number,$floor,$status,$notes) {
        $stmt = $this->db->prepare("UPDATE rooms SET room_type_id=?,room_number=?,floor=?,status=?,notes=? WHERE id=?");
        $stmt->bind_param('iisssi',$typeId,$number,$floor,$status,$notes,$id);
        $stmt->execute(); $stmt->close();
    }
    public function deleteRoom($id) {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id=? AND id NOT IN (SELECT room_id FROM bookings WHERE status IN ('confirmed','checked_in'))");
        $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
    }

    // ── Seasonal Pricing ───────────────────────────────────
    public function getSeasonalPricing() {
        return $this->db->query("SELECT sp.*,rt.name type_name FROM seasonal_pricing sp JOIN room_types rt ON rt.id=sp.room_type_id 
        ORDER BY sp.start_date DESC")->fetch_all(MYSQLI_ASSOC);
    }
    public function createSeasonalPricing($typeId,$label,$start,$end,$price) {
        $stmt = $this->db->prepare("INSERT INTO seasonal_pricing (room_type_id,label,start_date,end_date,price_per_night) VALUES (?,?,?,?,?)");
        $stmt->bind_param('isssd',$typeId,$label,$start,$end,$price);
        $stmt->execute(); $stmt->close();
    }
    public function updateSeasonalPricing($id,$typeId,$label,$start,$end,$price) {
        $stmt = $this->db->prepare("UPDATE seasonal_pricing SET room_type_id=?,label=?,start_date=?,end_date=?,price_per_night=? WHERE id=?");
        $stmt->bind_param('isssdi',$typeId,$label,$start,$end,$price,$id);
        $stmt->execute(); $stmt->close();
    }
    public function deleteSeasonalPricing($id) {
        $stmt = $this->db->prepare("DELETE FROM seasonal_pricing WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
    }

    // ── Staff Management ───────────────────────────────────
    public function getStaff($role=null) {
        $sql = "SELECT * FROM users WHERE role != 'guest'";
        if ($role) $sql .= " AND role='$role'";
        $sql .= " ORDER BY role, name";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r ?? [];
    }
    public function createStaff($name,$email,$phone,$role,$password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name,email,password_hash,phone,role,is_active) VALUES (?,?,?,?,?,1)");
        $stmt->bind_param('sssss',$name,$email,$hash,$phone,$role);
        $stmt->execute(); $stmt->close();
    }
    public function updateStaff($id,$name,$email,$phone) {
        $stmt = $this->db->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?");
        $stmt->bind_param('sssi',$name,$email,$phone,$id);
        $stmt->execute(); $stmt->close();
    }
    public function toggleUserActive($id,$active) {
        $stmt = $this->db->prepare("UPDATE users SET is_active=? WHERE id=?");
        $stmt->bind_param('ii',$active,$id); $stmt->execute(); $stmt->close();
    }

    // ── Guests ─────────────────────────────────────────────
    public function getGuests($search='') {
        if ($search) {
            $q = '%'.$search.'%';
            $stmt = $this->db->prepare("SELECT u.*,(SELECT COUNT(*) FROM bookings WHERE guest_id=u.id) booking_count FROM users u WHERE u.role='guest' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) ORDER BY u.created_at DESC");
            $stmt->bind_param('sss',$q,$q,$q); $stmt->execute();
            $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
        }
        return $this->db->query("SELECT u.*,(SELECT COUNT(*) FROM bookings WHERE guest_id=u.id) booking_count FROM users u WHERE u.role='guest' ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
    }

    // ── Bookings ───────────────────────────────────────────
    public function getAllBookings($filters=[]) {
        $where=[]; $params=[]; $types='';
        if (!empty($filters['status']))    { $where[]="b.status=?";      $types.='s'; $params[]=$filters['status']; }
        if (!empty($filters['room_type'])) { $where[]="b.room_type_id=?";$types.='i'; $params[]=(int)$filters['room_type']; }
        if (!empty($filters['source']))    { $where[]="b.source=?";      $types.='s'; $params[]=$filters['source']; }
        if (!empty($filters['date_from'])) { $where[]="b.checkin_date>=?";$types.='s';$params[]=$filters['date_from']; }
        if (!empty($filters['date_to']))   { $where[]="b.checkout_date<=?";$types.='s';$params[]=$filters['date_to']; }
        $sql = "SELECT b.*,u.name guest_name,rt.name type_name,r.room_number FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id LEFT JOIN rooms r ON r.id=b.room_id";
        if ($where) $sql.=' WHERE '.implode(' AND ',$where);
        $sql.=' ORDER BY b.created_at DESC LIMIT 200';
        if ($types) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types,...$params); $stmt->execute();
            $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
        }
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // ── Financial Reports ──────────────────────────────────
    public function getRevenueReport($period='month') {
        $groupBy = match($period) {
            'day'   => 'DATE(paid_at)',
            'week'  => 'YEARWEEK(paid_at)',
            default => 'DATE_FORMAT(paid_at,\'%Y-%m\')',
        };
        $label = match($period) {
            'day'   => 'DATE(paid_at)',
            'week'  => 'CONCAT(YEAR(paid_at),\'-W\',WEEK(paid_at))',
            default => 'DATE_FORMAT(paid_at,\'%b %Y\')',
        };
        return $this->db->query("SELECT $label period, COALESCE(SUM(base_amount),0) base, COALESCE(SUM(extras_amount),0) extras,
         COALESCE(SUM(discount_amount),0) discounts, COALESCE(SUM(total_amount),0) total, COUNT(*) txn_count FROM billing WHERE 
         payment_status='paid' GROUP BY $groupBy ORDER BY $groupBy DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);
    }
    public function getRevenueByRoomType() {
        return $this->db->query("SELECT rt.name, COALESCE(SUM(bi.total_amount),0) revenue, COUNT(bi.id) bookings FROM room_types rt
         LEFT JOIN bookings b ON b.room_type_id=rt.id LEFT JOIN billing bi ON bi.booking_id=b.id AND bi.payment_status='paid' GROUP BY 
         rt.id ORDER BY revenue DESC")->fetch_all(MYSQLI_ASSOC);
    }

    // ── Occupancy Reports ──────────────────────────────────
    public function getOccupancyReport() {
        $total = (int)$this->db->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
        $occ   = (int)$this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
        $peak  = $this->db->query("SELECT DATE_FORMAT(checkin_date,'%b %Y') mon, COUNT(*) cnt FROM bookings WHERE status != 'cancelled' GROUP BY mon ORDER BY cnt DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
        $popular = $this->db->query("SELECT rt.name, COUNT(b.id) cnt FROM bookings b JOIN room_types rt ON rt.id=b.room_type_id WHERE b.status != 'cancelled' GROUP BY rt.id ORDER BY cnt DESC")->fetch_all(MYSQLI_ASSOC);
        return compact('total','occ','peak','popular');
    }

    // ── Reviews ────────────────────────────────────────────
    public function getReviews($unanswered=false) {
        $sql = "SELECT rv.*,u.name guest_name,rt.name type_name,b.checkin_date,b.checkout_date FROM 
        reviews rv JOIN users u ON u.id=rv.guest_id JOIN bookings b ON b.id=rv.booking_id JOIN room_types rt ON rt.id=b.room_type_id";
        if ($unanswered) $sql .= " WHERE rv.admin_reply IS NULL";
        $sql .= " ORDER BY rv.created_at DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    public function replyToReview($id,$reply) {
        $stmt = $this->db->prepare("UPDATE reviews SET admin_reply=? WHERE id=?");
        $stmt->bind_param('si',$reply,$id); $stmt->execute(); $stmt->close();
    }
    public function getAvgRatings() {
        return $this->db->query("SELECT AVG(overall_rating) ov, AVG(cleanliness_rating) cl, AVG(service_rating) sv, 
        COUNT(*) cnt FROM reviews")->fetch_assoc();
    }

    // ── Service Requests Summary ───────────────────────────
    public function getServiceSummary() {
        return $this->db->query("SELECT service_type, status, COUNT(*) cnt FROM service_requests GROUP BY service_type, status ORDER BY service_type")->fetch_all(MYSQLI_ASSOC);
    }

    // ── Loyalty Points Report ──────────────────────────────
    public function getLoyaltyReport() {
        return $this->db->query("SELECT DATE_FORMAT(created_at,'%b %Y') mon, SUM(points_earned) earned, SUM(points_used) used FROM loyalty_points GROUP BY mon ORDER BY created_at DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);
    }

    // ── Announcements ──────────────────────────────────────
    public function getAnnouncements() {
        return $this->db->query("SELECT a.*,u.name author FROM announcements a JOIN users u ON u.id=a.created_by ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);
    }
    public function createAnnouncement($title,$msg,$userId) {
        $stmt = $this->db->prepare("INSERT INTO announcements (title,message,created_by) VALUES (?,?,?)");
        $stmt->bind_param('ssi',$title,$msg,$userId); $stmt->execute(); $stmt->close();
    }
    public function deleteAnnouncement($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();
    }
}
