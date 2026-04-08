<?php
// config.php
declare(strict_types=1);

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_NAME'] ?? 'testdb3';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (Throwable $e) {
  http_response_code(500);
  exit('DB connection failed.');
}

// ---- Gemini AI config ----
// Put your Gemini key in an environment variable if possible:
$geminiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '';
if ($geminiKey === '') {
  throw new Exception('Missing GEMINI_API_KEY.');
}

//test dump key
//var_dump(getenv('GEMINI_API_KEY'));

define('GEMINI_API_KEY', $geminiKey);
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

function gemini_generate_venue_overview(string $venueName, array $reviews): string {
  // Keep prompt small/cheap
  $sample = array_slice($reviews, 0, 12);

  $reviewsText = "";
  foreach ($sample as $i => $r) {
    $rating = isset($r['rating']) ? "(" . (int)$r['rating'] . "/5) " : "";
    $comment = trim(preg_replace("/\s+/", " ", (string)($r['comment'] ?? '')));
    if ($comment === '') continue;
    $reviewsText .= ($i+1) . ". " . $rating . $comment . "\n";
  }

  $prompt =
    "Write a single AI overview for the venue page.\n" .
    "Venue: {$venueName}\n\n" .
    "Requirements:\n" .
    "- Plain text only\n" .
    "- 120–220 words\n" .
    "- Start with one-line overall sentiment (e.g., Mostly positive — 4/5: ...)\n" .
    "- Mention common praises and common complaints\n" .
    "- Include 2 short representative quotes in quotes\n" .
    "- Do NOT include personal user info\n" .
    "- If there are no reviews, output exactly: No reviews yet for this venue.\n\n" .
    "Reviews:\n{$reviewsText}\n";

  if (GEMINI_API_KEY === '') {
    throw new Exception('Missing GEMINI_API_KEY.');
  }

  $url = GEMINI_API_URL . '?key=' . urlencode(GEMINI_API_KEY);

  $payload = [
    "contents" => [
      ["parts" => [["text" => $prompt]]]
    ],
    "generationConfig" => [
      "temperature" => 0.2,
      "maxOutputTokens" => 3000
    ]
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
  ]);

  $resp = curl_exec($ch);
  $err  = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false) throw new Exception("Gemini curl error: " . $err);
  if ($code < 200 || $code >= 300) throw new Exception("Gemini HTTP {$code}: " . $resp);

  $json = json_decode($resp, true);
  $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

  return trim((string)$text);
}


require_once __DIR__ . '/db_session_handler.php';
$handler = new DbSessionHandler($pdo);
session_set_save_handler($handler, true);

session_set_cookie_params ([
'lifetime' => 0,
'path' => '/',
'httponly' => true,
'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
'samesite' => 'Lax',
]);

ini_set('session.gc_maxlifetime', 300); // 5 minute

session_start();


//CSRF protection
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
  return $_SESSION['csrf_token'] ?? '';
}

function csrf_input(): string {
  return '<input type="hidden" name="csrf_token" value="' .
    htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
    '">';
}

function verify_csrf(): void {
  $sessionToken = $_SESSION['csrf_token'] ?? '';
  $formToken = $_POST['csrf_token'] ?? '';

  if (
    !is_string($formToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $formToken)
  ) {
    http_response_code(419);
    header('Content-Type: application/json');
    echo json_encode([
      'ok' => false,
      'error' => 'Invalid CSRF token.'
    ]);
    exit;
  }
}

$timeout = 300;

if (isset($_SESSION['LAST_ACTIVITY']) &&
  (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();

    header("Location: index.php?reason=timeout");
    exit;
  }

  $_SESSION['LAST_ACTIVITY'] = time();