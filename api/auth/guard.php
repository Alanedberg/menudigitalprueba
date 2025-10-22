<?php
// /api/auth/guard.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user'])) {
  header('Location: ../admin/login.php'); // si entras por ruta distinta, ajusta el path
  exit;
}
