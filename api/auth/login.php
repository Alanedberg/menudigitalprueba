<?php
// api/auth/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ⚠️ Cambia esto por tu validación real (BD). De momento, demo:
$USUARIO = 'admin';
$CLAVE   = '1234';

$user = $_POST['usuario'] ?? '';
$pass = $_POST['clave'] ?? '';

if ($user === $USUARIO && $pass === $CLAVE) {
  $_SESSION['user'] = ['usuario'=>$user, 'ts'=>time()];
  echo json_encode(['ok'=>true]);
} else {
  echo json_encode(['ok'=>false, 'msg'=>'Usuario o clave incorrectos']);
}
