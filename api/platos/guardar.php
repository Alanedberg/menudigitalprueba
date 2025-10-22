<?php
require_once __DIR__ . '/../../database.php';
$pdo = Database::connect();

$id           = isset($_POST['id']) ? intval($_POST['id']) : 0;
$categoria_id = intval($_POST['categoria_id'] ?? 0);
$nombre       = trim($_POST['nombre'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$precio       = floatval($_POST['precio'] ?? 0);
$activo       = isset($_POST['activo']) ? intval($_POST['activo']) : 1;
$destacado    = isset($_POST['destacado']) ? intval($_POST['destacado']) : 0;
$orden        = isset($_POST['orden']) ? intval($_POST['orden']) : 0;

if ($categoria_id <= 0 || $nombre === '') {
  json_response(['ok'=>false,'msg'=>'Categoría y nombre son requeridos'], 400);
}

$imgName = null;
if (!empty($_FILES['imagen']['name'])) {
  $imgName = save_resized_image($_FILES['imagen']);
  if (!$imgName) json_response(['ok'=>false,'msg'=>'Imagen inválida'], 400);
}

if ($id > 0) {
  if ($imgName) {
    // elimina anterior
    $old = $pdo->prepare("SELECT imagen FROM platos WHERE id=?");
    $old->execute([$id]);
    $prev = $old->fetchColumn();
    if ($prev && file_exists(UPLOAD_DIR . '/' . $prev)) @unlink(UPLOAD_DIR . '/' . $prev);

    $sql = "UPDATE platos SET categoria_id=?, nombre=?, descripcion=?, precio=?, imagen=?, activo=?, destacado=?, orden=? WHERE id=?";
    $pdo->prepare($sql)->execute([$categoria_id,$nombre,$descripcion,$precio,$imgName,$activo,$destacado,$orden,$id]);
  } else {
    $sql = "UPDATE platos SET categoria_id=?, nombre=?, descripcion=?, precio=?, activo=?, destacado=?, orden=? WHERE id=?";
    $pdo->prepare($sql)->execute([$categoria_id,$nombre,$descripcion,$precio,$activo,$destacado,$orden,$id]);
  }
  json_response(['ok'=>true,'msg'=>'Plato actualizado']);
} else {
  $sql = "INSERT INTO platos (categoria_id,nombre,descripcion,precio,imagen,activo,destacado,orden)
          VALUES (?,?,?,?,?,?,?,?)";
  $pdo->prepare($sql)->execute([$categoria_id,$nombre,$descripcion,$precio,$imgName,$activo,$destacado,$orden]);
  json_response(['ok'=>true,'msg'=>'Plato creado']);
}
