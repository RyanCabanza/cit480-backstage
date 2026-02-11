<?php
require __DIR__ . '/config.php'; // session + $pdo

// Only allow POST submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: contact.html");
  exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
if ($name === '' || $email === '' || $message === '') {
  header("Location: contact.html?error=missing");
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: contact.html?error=email");
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO contact (name, email, phone, subject, message)
    VALUES (:name, :email, :phone, :subject, :message)
  ");

  $stmt->execute([
    ':name'    => $name,
    ':email'   => $email,
    ':phone'   => $phone,
    ':subject' => $subject,
    ':message' => $message,
  ]);

  header("Location: contact.html?success=1");
  exit;

} catch (Exception $e) {
  header("Location: contact.html?error=db");
  exit;
}
