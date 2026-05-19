<?php
class HousekeepingModel {
    private $db;
    public function __construct() { $this->db = getDB(); }

    public function getDashboardStats($userId) {
        $today  = date('Y-m-d');
        $stats  = [];
        $stats['dirty']      = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='dirty'")->fetch_assoc()['c'];
        $stats['maintenance']= $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='maintenance'")->fetch_assoc()['c'];
        $stats['open_issues']= $this->db->query("SELECT COUNT(*) c FROM maintenance_reports WHERE status='open'")->fetch_assoc()['c'];
        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM housekeeping_tasks WHERE assigned_to=? AND status='done' AND DATE(completed_at)=?");
        $stmt->bind_param('is',$userId,$today); $stmt->execute();
        $stats['done_today'] = $stmt->get_result()->fetch_assoc()['c']; $stmt->close();
        $stats['pending_tasks'] = $this->db->query("SELECT COUNT(*) c FROM housekeeping_tasks WHERE status='pending'")->fetch_assoc()['c'];
        return $stats;
    }

    public function getAllRooms() {
        return $this->db->query("SELECT r.*, rt.name type_name FROM rooms r JOIN room_types rt ON rt.id=r.room_type_id ORDER BY r.floor, r.room_number")->fetch_all(MYSQLI_ASSOC);
    }

    public function getRoomById($id) {
        $stmt = $this->db->prepare("SELECT r.*, rt.name type_name FROM rooms r JOIN room_types rt ON rt.id=r.room_type_id WHERE r.id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc(); $stmt->close(); return $r;
    }

    public function getTodayTasks($userId = null, $filterStatus = null, $filterPriority = null) {
        $today = date('Y-m-d');
        $sql = "SELECT ht.*, r.room_number, r.floor, rt.name type_name, u.name assigned_name FROM housekeeping_tasks ht JOIN rooms r ON r.id=ht.room_id JOIN room_types rt ON rt.id=r.room_type_id JOIN users u ON u.id=ht.assigned_to WHERE ht.scheduled_date=?";
        $params = [$today]; $types = 's';
        if ($filterStatus)   { $sql .= " AND ht.status=?";   $types .= 's'; $params[] = $filterStatus; }
        if ($filterPriority) { $sql .= " AND ht.priority=?"; $types .= 's'; $params[] = $filterPriority; }
        $sql .= " ORDER BY ht.priority='urgent' DESC, ht.status ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types,...$params); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getAllTasks($filterStatus = null) {
        $sql = "SELECT ht.*, r.room_number, r.floor, rt.name type_name, u.name assigned_name FROM housekeeping_tasks ht JOIN rooms r ON r.id=ht.room_id JOIN room_types rt ON rt.id=r.room_type_id JOIN users u ON u.id=ht.assigned_to";
        if ($filterStatus) $sql .= " WHERE ht.status='$filterStatus'";
        $sql .= " ORDER BY ht.scheduled_date DESC, ht.priority='urgent' DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function createTask($roomId, $assignedTo, $taskType, $priority, $notes, $scheduledDate) {
        $stmt = $this->db->prepare("INSERT INTO housekeeping_tasks (room_id,assigned_to,task_type,priority,status,notes,scheduled_date) VALUES (?,?,?,?,'pending',?,?)");
        $stmt->bind_param('iissss',$roomId,$assignedTo,$taskType,$priority,$notes,$scheduledDate);
        $stmt->execute(); $stmt->close();
    }

    public function updateTaskStatus($taskId, $status, $notes = null) {
        $completed = $status === 'done' ? date('Y-m-d H:i:s') : null;
        if ($notes) {
            $stmt = $this->db->prepare("UPDATE housekeeping_tasks SET status=?,notes=CONCAT(COALESCE(notes,''),'\n',?),completed_at=? WHERE id=?");
            $stmt->bind_param('sssi',$status,$notes,$completed,$taskId);
        } else {
            $stmt = $this->db->prepare("UPDATE housekeeping_tasks SET status=?,completed_at=? WHERE id=?");
            $stmt->bind_param('ssi',$status,$completed,$taskId);
        }
        $stmt->execute(); $stmt->close();
    }

    public function markRoomClean($roomId) {
        $stmt = $this->db->prepare("UPDATE rooms SET status='available' WHERE id=?");
        $stmt->bind_param('i',$roomId); $stmt->execute(); $stmt->close();
    }

    public function markRoomMaintenance($roomId) {
        $stmt = $this->db->prepare("UPDATE rooms SET status='maintenance' WHERE id=?");
        $stmt->bind_param('i',$roomId); $stmt->execute(); $stmt->close();
    }

    public function createMaintenanceReport($roomId, $userId, $desc, $severity) {
        $stmt = $this->db->prepare("INSERT INTO maintenance_reports (room_id,reported_by,description,severity,status) VALUES (?,?,?,?,'open')");
        $stmt->bind_param('iiss',$roomId,$userId,$desc,$severity); $stmt->execute(); $stmt->close();
        $this->markRoomMaintenance($roomId);
    }

    public function getMaintenanceReports($status = null) {
        $sql = "SELECT mr.*, r.room_number, r.floor, rt.name type_name, u.name reporter FROM maintenance_reports mr JOIN rooms r ON r.id=mr.room_id JOIN room_types rt ON rt.id=r.room_type_id JOIN users u ON u.id=mr.reported_by";
        if ($status) $sql .= " WHERE mr.status='$status'";
        $sql .= " ORDER BY mr.severity='high' DESC, mr.reported_at DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function updateMaintenanceReport($id, $status) {
        $resolved = $status === 'resolved' ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare("UPDATE maintenance_reports SET status=?,resolved_at=? WHERE id=?");
        $stmt->bind_param('ssi',$status,$resolved,$id); $stmt->execute(); $stmt->close();
        if ($status === 'resolved') {
            // Restore room
            $stmt2 = $this->db->prepare("SELECT room_id FROM maintenance_reports WHERE id=?");
            $stmt2->bind_param('i',$id); $stmt2->execute();
            $row = $stmt2->get_result()->fetch_assoc(); $stmt2->close();
            if ($row) $this->markRoomClean($row['room_id']);
        }
    }

    public function getUpcomingCheckouts($days = 2) {
        $end = date('Y-m-d', strtotime("+$days days"));
        $stmt = $this->db->prepare("SELECT b.*, u.name guest_name, r.room_number, rt.name type_name FROM bookings b JOIN users u ON u.id=b.guest_id JOIN rooms r ON r.id=b.room_id JOIN room_types rt ON rt.id=b.room_type_id WHERE b.checkout_date BETWEEN ? AND ? AND b.status='checked_in' ORDER BY b.checkout_date");
        $today = date('Y-m-d');
        $stmt->bind_param('ss',$today,$end); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getUpcomingCheckins($days = 2) {
        $end = date('Y-m-d', strtotime("+$days days"));
        $stmt = $this->db->prepare("SELECT b.*, u.name guest_name, rt.name type_name FROM bookings b JOIN users u ON u.id=b.guest_id JOIN room_types rt ON rt.id=b.room_type_id WHERE b.checkin_date BETWEEN ? AND ? AND b.status='confirmed' ORDER BY b.checkin_date");
        $today = date('Y-m-d');
        $stmt->bind_param('ss',$today,$end); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getTaskHistoryForRoom($roomId) {
        $stmt = $this->db->prepare("SELECT ht.*, u.name assigned_name FROM housekeeping_tasks ht JOIN users u ON u.id=ht.assigned_to WHERE ht.room_id=? ORDER BY ht.scheduled_date DESC LIMIT 20");
        $stmt->bind_param('i',$roomId); $stmt->execute();
        $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $r;
    }

    public function getHousekeepingStaff() {
        return $this->db->query("SELECT id,name FROM users WHERE role='housekeeping' AND is_active=1")->fetch_all(MYSQLI_ASSOC);
    }

    public function getDailyReport($date) {
        $report = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM housekeeping_tasks WHERE scheduled_date=?");
        $stmt->bind_param('s',$date); $stmt->execute();
        $report['total'] = $stmt->get_result()->fetch_assoc()['c']; $stmt->close();

        $stmt2 = $this->db->prepare("SELECT COUNT(*) c FROM housekeeping_tasks WHERE scheduled_date=? AND status='done'");
        $stmt2->bind_param('s',$date); $stmt2->execute();
        $report['done'] = $stmt2->get_result()->fetch_assoc()['c']; $stmt2->close();

        $stmt3 = $this->db->prepare("SELECT COUNT(*) c FROM housekeeping_tasks WHERE scheduled_date=? AND status='pending'");
        $stmt3->bind_param('s',$date); $stmt3->execute();
        $report['pending'] = $stmt3->get_result()->fetch_assoc()['c']; $stmt3->close();

        $stmt4 = $this->db->prepare("SELECT COUNT(*) c FROM housekeeping_tasks WHERE scheduled_date=? AND status='in_progress'");
        $stmt4->bind_param('s',$date); $stmt4->execute();
        $report['in_progress'] = $stmt4->get_result()->fetch_assoc()['c']; $stmt4->close();

        $report['rooms_cleared'] = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch_assoc()['c'];
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
}
