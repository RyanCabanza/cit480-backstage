<?php
// review_create.php
declare(strict_types=1);
require __DIR__ . '/config.php'; // $pdo + session

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

// Must be logged in
if (empty($_SESSION['user_id'])) {
  // Optional: remember where to come back to
  $fallbackId = isset($_POST['venue_id']) ? (int)$_POST['venue_id'] : 0;
  $_SESSION['flash_errors'] = ['You must be logged in to post a review.'];
  header('Location: ' . ($fallbackId ? "venue-page.php?id={$fallbackId}" : 'index.php'));
  exit;
}

$userId  = (int)$_SESSION['user_id'];
$venueId = isset($_POST['venue_id']) ? (int)$_POST['venue_id'] : 0;
$rating  = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = trim((string)($_POST['comment'] ?? ''));

$errors = [];

// Validate input
if ($venueId <= 0) $errors[] = 'Missing venue.';
if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';
if ($comment === '') $errors[] = 'Please write a short review.';
if (mb_strlen($comment) > 4000) $errors[] = 'Review is too long.';

// Ensure venue exists (defensive)
if (!$errors) {
  $stmt = $pdo->prepare('SELECT id FROM venues WHERE id = ? LIMIT 1');
  $stmt->execute([$venueId]);
  if (!$stmt->fetch()) $errors[] = 'That venue does not exist.';
}

// (Optional) Prevent duplicate review by same user per venue
// If you want “one review per user per venue”, keep this block and add a unique index (see below).
if (!$errors) {
  $dupx = $pdo->prepare('SELECT id FROM reviews WHERE venue_id = ? AND user_id = ? LIMIT 1');
  $dupx->execute([$venueId, $userId]);
  if ($dupx->fetch()) $errors[] = 'You have already reviewed this venue.';
}

if ($errors) {
  // flash errors + old input, then bounce back
  $_SESSION['flash_errors'] = $errors;
  $_SESSION['flash_old'] = [
    'rating' => $rating,
    'comment' => $comment
  ];
  header("Location: venue-page.php?id={$venueId}");
  exit;
}

// Insert review
$ins = $pdo->prepare('
  INSERT INTO reviews (venue_id, user_id, rating, comment, created_at)
  VALUES (?, ?, ?, ?, NOW())
');
$ins->execute([$venueId, $userId, $rating, $comment]);

$_SESSION['flash_success'] = 'Thanks for your review!';
header("Location: venue-page.php?id={$venueId}");
exit;
