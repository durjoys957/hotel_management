<?php
require_once __DIR__ . '/../includes/init.php';

// Allow any logged-in role
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$db    = getDB();
$rooms = $db->query("SELECT r.id, r.room_number, r.floor, r.status, rt.name as type_name FROM rooms r JOIN room_types rt ON rt.id = r.room_type_id ORDER BY r.floor, r.room_number")->fetch_all(MYSQLI_ASSOC);

echo json_encode(['rooms' => $rooms]);
