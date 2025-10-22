<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();

$nombre_negocio = trim($_POST['nombre_negocio'] ?? '');
$whatsapp       = preg_replace('/\D+/', '', $_POST['whatsapp'] ?? ''); // solo dígitos
if ($nombre_negocio === '' || $whatsapp === '') json_response(['ok'=>false,'msg'=>'Nombre y WhatsApp son requeridos'], 400);

$imgName = null;
if (!empty($_FILES['logo']['name'])) {
  $imgName = save_resized_image($_FILES['logo']);
  if (!$imgName) json_response(['ok'=>false,'msg'=>'Logo inválido'], 400);

  // elimina logo anterior
  $old = $pdo->query("SELECT logo FROM settings WHERE id=1")->fetchColumn();
  if ($old && file_exists(UPLOAD_DIR . '/' . $old)) @unlink(UPLOAD_DIR . '/' . $old);

  $sql = "UPDATE settings SET nombre_negocio=?, whatsapp=?, logo=? WHERE id=1";
  $pdo->prepare($sql)->execute([$nombre_negocio,$whatsapp,$imgName]);
} else {
  $sql = "UPDATE settings SET nombre_negocio=?, whatsapp=? WHERE id=1";
  $pdo->prepare($sql)->execute([$nombre_negocio,$whatsapp]);
}

json_response(['ok'=>true,'msg'=>'Configuración guardada']);
