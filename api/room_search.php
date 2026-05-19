<?php
require_once __DIR__ . '/../includes/init.php';
requireRole('guest');
require_once __DIR__ . '/../guest/models/GuestModel.php';

header('Content-Type: application/json');

$checkin  = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests   = (int)($_GET['guests'] ?? 1);

if (!$checkin || !$checkout) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$model = new GuestModel();
$rooms = $model->searchAvailableRoomTypes($checkin, $checkout, $guests);

echo json_encode(['rooms' => $rooms]);
