<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();
$row = $pdo->query("SELECT nombre_negocio, whatsapp, logo FROM settings WHERE id=1")->fetch();
json_response(['ok'=>true,'data'=>$row, 'uploadsBase'=>UPLOAD_URL]);
