<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) json_response(['ok'=>false,'msg'=>'ID inválido'], 400);

$old = $pdo->prepare("SELECT imagen FROM platos WHERE id=?");
$old->execute([$id]);
$prev = $old->fetchColumn();
if ($prev && file_exists(UPLOAD_DIR . '/' . $prev)) @unlink(UPLOAD_DIR . '/' . $prev);

$pdo->prepare("DELETE FROM platos WHERE id=?")->execute([$id]);
json_response(['ok'=>true,'msg'=>'Plato eliminado']);
