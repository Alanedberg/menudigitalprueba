<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();

$id      = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre  = trim($_POST['nombre'] ?? '');
$estado  = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
$orden   = isset($_POST['orden']) ? intval($_POST['orden']) : 0;

if ($nombre === '') json_response(['ok'=>false,'msg'=>'Nombre requerido'], 400);

if ($id > 0) {
  $sql = "UPDATE categories SET nombre=?, estado=?, orden=? WHERE id=?";
  $pdo->prepare($sql)->execute([$nombre,$estado,$orden,$id]);
  json_response(['ok'=>true,'msg'=>'Categoría actualizada']);
} else {
  $sql = "INSERT INTO categories (nombre, estado, orden) VALUES (?,?,?)";
  $pdo->prepare($sql)->execute([$nombre,$estado,$orden]);
  json_response(['ok'=>true,'msg'=>'Categoría creada']);
}
