<?php
// admin/_auth.php — Validación de login usando Database::connect()
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

// Detectar la carpeta actual (/MENU_DIGITAL/admin)
$here = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = trim($_POST['usuario'] ?? '');
  $clave   = trim($_POST['clave'] ?? '');

  if ($usuario === '' || $clave === '') {
    header("Location: login.php?error=1");
    exit;
  }

  try {
    $db  = new Database();
    $pdo = $db->connect();

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($clave, $user['clave'])) {
      $_SESSION['usuario'] = [
        'id'       => $user['id'],
        'usuario'  => $user['usuario'],
        'inicio'   => date('Y-m-d H:i:s')
      ];

      // ✅ Redirigir correctamente al panel admin
      header("Location: ./");
      exit;
    } else {
      // ❌ Error de login → vuelve al mismo login del admin
      header("Location: login.php?error=1");
      exit;
    }

  } catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    header("Location: login.php?error=2");
    exit;
  }

} else {
  // ✅ Protección al incluir _auth.php en páginas internas
  if (empty($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
  }
}
