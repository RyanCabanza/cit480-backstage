<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
  exit;
}

$name  = trim((string)($_POST['name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

if ($name === '' || $email === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Name and email are required.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Invalid email.']);
  exit;
}

$uploadDir = __DIR__ . '/user-image';
if (!is_dir($uploadDir)) {
  
  @mkdir($uploadDir, 0755, true);
}

$newImageRelPath = null;


if (!empty($_FILES['profile_image']) && is_array($_FILES['profile_image'])) {
  $f = $_FILES['profile_image'];

  if ($f['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($f['error'] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Image upload failed.']);
      exit;
    }


    if ($f['size'] > 5 * 1024 * 1024) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Image must be under 5MB.']);
      exit;
    }

    
    $fi = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($f['tmp_name']);

    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
      http_response_code(400);
      echo json_encode(['ok' => false, 'error' => 'Only JPG, PNG, or WEBP images allowed.']);
      exit;
    }

    $ext = $allowed[$mime];


    $rand = bin2hex(random_bytes(8));
    $filename = "u_{$userId}_{$rand}.{$ext}";

    $destAbs = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $destAbs)) {
      http_response_code(500);
      echo json_encode(['ok' => false, 'error' => 'Could not save image.']);
      exit;
    }

    $newImageRelPath = 'user-image/' . $filename;
  }
}




try {
  
  $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
  $check->execute([$email, $userId]);
  if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'That email is already in use.']);
    exit;
  }

  
 // Always get the current image from DB (not session)
$get = $pdo->prepare("SELECT profile_image_path FROM users WHERE id = ? LIMIT 1");
$get->execute([$userId]);
$currentImage = $get->fetchColumn() ?: null;

$imageToStore = $currentImage;

// If removing image
if ($removeImage) {
  // delete file from disk if it exists
  if ($currentImage) {
    $abs = __DIR__ . '/' . ltrim($currentImage, '/');
    if (is_file($abs)) {
      @unlink($abs);
    }
  }
  $imageToStore = null;
}

// If uploading a new image
if ($newImageRelPath !== null) {
  // delete old image file
  if ($currentImage) {
    $abs = __DIR__ . '/' . ltrim($currentImage, '/');
    if (is_file($abs)) {
      @unlink($abs);
    }
  }
  $imageToStore = $newImageRelPath;
}

  $upd = $pdo->prepare('UPDATE users SET name = ?, email = ?, profile_image_path = ? WHERE id = ?');
  $upd->execute([$name, $email, $imageToStore, $userId]);

  
  $_SESSION['user_name'] = $name;
  $_SESSION['user_email'] = $email;
  $_SESSION['profile_image_path'] = $imageToStore;

  echo json_encode([
    'ok' => true,
    'name' => $name,
    'email' => $email,
    'profile_image_path' => $imageToStore
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error.']);
}