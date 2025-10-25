<?php
require_once __DIR__ . '/../../database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = Database::connect();
$stmt = $pdo->query("SELECT id, nombre, estado, orden, fecha_creacion FROM categories ORDER BY orden ASC, nombre ASC");
$rows = $stmt->fetchAll();

echo json_encode(['ok' => true, 'data' => $rows]);
