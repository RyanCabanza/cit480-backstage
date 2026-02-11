<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Email and password are required.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Invalid email.']);
  exit;
}

try {
  $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([strtolower($email)]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($pass, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid credentials.']);
    exit;
  }

  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['user_name'] = $user['name'];
  $_SESSION['user_email'] = $user['email'];

  echo json_encode(['ok' => true, 'message' => 'Logged in.']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error.']);
}
