<?php
require_once __DIR__ . '/../includes/init.php';
requireRole('admin');

header('Content-Type: application/json');

$db    = getDB();
$today = date('Y-m-d');

$total    = (int)$db->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'];
$occupied = (int)$db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch_assoc()['c'];
$revenue  = (float)$db->query("SELECT COALESCE(SUM(total_amount),0) v FROM billing WHERE payment_status='paid' AND DATE(paid_at)='$today'")->fetch_assoc()['v'];

echo json_encode([
    'occupancy_pct' => $total > 0 ? round($occupied / $total * 100) : 0,
    'occupied'      => $occupied,
    'available'     => $total - $occupied,
    'today_revenue' => $revenue,
]);
