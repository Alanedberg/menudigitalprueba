<?php
session_start();
if (isset($_SESSION['usuario'])) {
  header("Location: admin/");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="login.css" rel="stylesheet">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card shadow">
      <div class="text-center mb-4">
        <img src="../uploads/img_20251022_033945_39be5bb5.png" alt="Logo" class="login-logo mb-2">
        <h4 class="fw-bold text-brand">Menú Digital</h4>
        <p class="text-muted small mb-0">Accede al panel de administración</p>
      </div>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center py-2">
          <i class="fas fa-exclamation-circle me-1"></i> Usuario o clave incorrectos
        </div>
      <?php endif; ?>

      <form action="_auth.php" method="POST" class="mt-3">
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="clave" class="form-control" placeholder="Tu contraseña" required>
          </div>
        </div>
        <button type="submit" class="btn btn-login w-100 mt-3">
          <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
        </button>
      </form>

      <!-- Enlace al menú público -->
      <div class="text-center mt-4">
        <a href="../public/index.php" class="btn-link-menu">
          <i class="fas fa-utensils me-1"></i> Ir al menú digital
        </a>
      </div>

      <footer class="login-footer mt-4 text-center small">
        © <?= date('Y') ?> DevQuis — Soluciones Tecnológicas
      </footer>
    </div>
  </div>
</body>
</html>
