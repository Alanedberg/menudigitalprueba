<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();
$stmt = $pdo->query("SELECT id, nombre, estado, orden, fecha_creacion FROM categories ORDER BY orden ASC, nombre ASC");
$rows = $stmt->fetchAll();
json_response(['ok' => true, 'data' => $rows]);
