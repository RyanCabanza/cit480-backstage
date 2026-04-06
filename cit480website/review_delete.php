<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
  exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
  exit;
}

$reviewId = (int)($_POST['review_id'] ?? 0);
if ($reviewId <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Invalid review id.']);
  exit;
}

try {
  $check = $pdo->prepare('SELECT id FROM reviews WHERE id = ? AND user_id = ? LIMIT 1');
  $check->execute([$reviewId, $userId]);

  if (!$check->fetch()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'You cannot delete this review.']);
    exit;
  }

  $del = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
  $del->execute([$reviewId]);

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error.']);
}