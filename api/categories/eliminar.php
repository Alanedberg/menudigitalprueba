<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) json_response(['ok'=>false,'msg'=>'ID inválido'], 400);

// Verifica si hay platos
$cnt = $pdo->prepare("SELECT COUNT(*) c FROM platos WHERE categoria_id=?");
$cnt->execute([$id]);
if ($cnt->fetchColumn() > 0) {
  json_response(['ok'=>false,'msg'=>'No se puede eliminar: tiene platos asociados'], 400);
}

$pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
json_response(['ok'=>true,'msg'=>'Categoría eliminada']);
