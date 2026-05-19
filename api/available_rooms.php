<?php
require_once __DIR__ . '/../includes/init.php';
requireRole('receptionist');

header('Content-Type: application/json');

$typeId = (int)($_GET['type_id'] ?? 0);
if (!$typeId) {
    echo json_encode(['rooms' => []]);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, room_number, floor, status FROM rooms WHERE room_type_id = ? AND status = 'available' ORDER BY room_number");
$stmt->bind_param('i', $typeId);
$stmt->execute();
$rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['rooms' => $rooms]);
