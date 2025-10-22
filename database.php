<?php
require_once __DIR__ . '/config.php';

class Database {
  public static function connect(): PDO {
    static $pdo = null;
    if ($pdo === null) {
      $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
      $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    }
    return $pdo;
  }
}

function json_response($data, int $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

// Garantiza carpeta de uploads
if (!is_dir(UPLOAD_DIR)) {
  @mkdir(UPLOAD_DIR, 0775, true);
}

// Resize ligero (GD)
function save_resized_image(array $file): ?string {
  if ($file['error'] !== UPLOAD_ERR_OK) return null;
  $tmp = $file['tmp_name'];
  $info = getimagesize($tmp);
  if (!$info) return null;

  [$w, $h, $type] = $info;
  switch ($type) {
    case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($tmp); $ext = 'jpg'; break;
    case IMAGETYPE_PNG:  $src = imagecreatefrompng($tmp);  $ext = 'png'; break;
    case IMAGETYPE_WEBP: $src = imagecreatefromwebp($tmp); $ext = 'webp'; break;
    default: return null;
  }

  $maxW = IMG_MAX_WIDTH;
  if ($w > $maxW) {
    $newW = $maxW;
    $newH = intval(($h * $newW) / $w);
  } else {
    $newW = $w; $newH = $h;
  }

  $dst = imagecreatetruecolor($newW, $newH);
  imagecopyresampled($dst, $src, 0,0,0,0, $newW,$newH, $w,$h);

  $name = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $path = UPLOAD_DIR . '/' . $name;
  // Transparencia PNG/WebP
  if ($type === IMAGETYPE_PNG) {
    imagesavealpha($dst, true);
    imagepng($dst, $path);
  } elseif ($type === IMAGETYPE_WEBP) {
    imagewebp($dst, $path, 80);
  } else {
    imagejpeg($dst, $path, IMG_JPEG_QUALITY);
  }
  imagedestroy($src); imagedestroy($dst);
  return $name; // nombre de archivo relativo
}
