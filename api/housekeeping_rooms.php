<?php
require_once __DIR__ . '/../includes/init.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$db     = getDB();
$typeId = (int)($_GET['type_id'] ?? 0);

if ($typeId) {
    $stmt = $db->prepare("SELECT id, room_number, floor, status FROM rooms WHERE room_type_id = ? AND status NOT IN ('occupied','blocked') ORDER BY room_number");
    $stmt->bind_param('i', $typeId);
    $stmt->execute();
    $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $rooms = $db->query("SELECT r.id, r.room_number, r.floor, r.status, rt.name type_name FROM rooms r JOIN room_types rt ON rt.id=r.room_type_id ORDER BY r.floor, r.room_number")->fetch_all(MYSQLI_ASSOC);
}

echo json_encode(['rooms' => $rooms]);
