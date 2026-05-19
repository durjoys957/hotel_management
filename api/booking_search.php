<?php
require_once __DIR__ . '/../includes/init.php';
requireRole('receptionist');
require_once __DIR__ . '/../receptionist/models/ReceptionistModel.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (!$q) { echo json_encode(['bookings' => []]); exit; }

$model    = new ReceptionistModel();
$bookings = $model->searchBooking($q);
echo json_encode(['bookings' => $bookings]);
