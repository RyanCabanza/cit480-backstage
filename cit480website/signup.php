<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$name     = trim((string)($_POST['username'] ?? '')); // your modal label says “Name or Username”
$email    = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$confirm  = (string)($_POST['password_confirm'] ?? '');

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'All fields are required.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Invalid email.']);
  exit;
}
if ($password !== $confirm) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Passwords do not match.']);
  exit;
}
if (strlen($password) < 8) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'Password must be at least 8 chars.']);
  exit;
}

try {
  // unique email check
  $q = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  $q->execute([$email]);
  if ($q->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'Email already registered.']);
    exit;
  }

  $hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt/argon auto

  $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
  $ins->execute([$name, strtolower($email), $hash]);

  $_SESSION['user_id'] = (int)$pdo->lastInsertId();
  $_SESSION['user_name'] = $name;
  $_SESSION['user_email'] = strtolower($email);

  echo json_encode(['ok' => true, 'message' => 'Signed up successfully.']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error.']);
}
