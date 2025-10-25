<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();

$sql = "SELECT p.id, p.categoria_id, c.nombre AS categoria_nombre, p.nombre, p.descripcion, p.precio,
               p.imagen, p.activo, p.destacado, p.orden, p.fecha_creacion
        FROM platos p
        INNER JOIN categories c ON c.id = p.categoria_id
        ORDER BY p.destacado DESC, p.orden ASC, p.nombre ASC";
$rows = $pdo->query($sql)->fetchAll();

// settings
$set = $pdo->query("SELECT nombre_negocio, whatsapp, logo FROM settings WHERE id=1")->fetch();

json_response(['ok'=>true, 'data'=>$rows, 'settings'=>$set, 'uploadsBase'=>UPLOAD_URL]);
