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

$currentPassword = (string)($_POST['current_password'] ?? '');
$newPassword     = (string)($_POST['new_password'] ?? '');
$confirmPassword = (string)($_POST['confirm_password'] ?? '');

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'All password fields are required.']);
  exit;
}

if ($newPassword !== $confirmPassword) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'New passwords do not match.']);
  exit;
}

if (strlen($newPassword) < 8) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'New password must be at least 8 characters.']);
  exit;
}

if (!preg_match('/[A-Z]/', $newPassword)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Password must include at least one uppercase letter.']);
  exit;
}

if (!preg_match('/[a-z]/', $newPassword)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Password must include at least one lowercase letter.']);
  exit;
}

if (!preg_match('/[0-9]/', $newPassword)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Password must include at least one number.']);
  exit;
}

if (!preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Password must include at least one special character.']);
  exit;
}

try {
  $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
  $stmt->execute([$userId]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'User not found.']);
    exit;
  }

  if (!password_verify($currentPassword, $user['password_hash'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Current password is incorrect.']);
    exit;
  }

  if (password_verify($newPassword, $user['password_hash'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'New password must be different from your current password.']);
    exit;
  }

  $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

  $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
  $upd->execute([$newHash, $userId]);

  echo json_encode(['ok' => true, 'message' => 'Password updated.']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error.']);
}