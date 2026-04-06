<?php
require __DIR__ . '/config.php'; // starts session & connects to DB

$timeoutMessage = '';
if (isset($_GET['reason']) && $_GET['reason'] === 'timeout') {
    $timeoutMessage = 'You were logged out due to inactivity.';
}

$isLoggedIn = !empty($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? '';

// Search / sort inputs
$q     = trim($_GET['q'] ?? '');
$sort  = $_GET['sort'] ?? 'az';

// Whitelist sorting
switch ($sort) {
    case 'za':
        $orderBy = 'v.name DESC';
        break;
    case 'ratingHigh':
        $orderBy = 'avg_rating DESC, review_count DESC, v.name ASC';
        break;
    case 'ratingLow':
        $orderBy = 'avg_rating ASC, review_count ASC, v.name ASC';
        break;
    case 'az':
    default:
        $orderBy = 'v.name ASC';
        break;
}

// Search condition
$where = '';
$params = [];

if ($q !== '') {
    $where = "WHERE v.name LIKE :search_name
              OR v.city LIKE :search_city
              OR v.state LIKE :search_state
              OR v.address LIKE :search_address";

    $searchTerm = '%' . $q . '%';

    $params[':search_name'] = $searchTerm;
    $params[':search_city'] = $searchTerm;
    $params[':search_state'] = $searchTerm;
    $params[':search_address'] = $searchTerm;
}

// Main venue query
$sql = "
    SELECT
        v.id,
        v.name,
        v.image_url,
        v.city,
        v.state,
        v.address,
        COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
        COUNT(r.id) AS review_count,
        MAX(r.created_at) AS last_reviewed_at
    FROM venues v
    LEFT JOIN reviews r ON r.venue_id = v.id
    $where
    GROUP BY v.id, v.name, v.image_url, v.city, v.state, v.address
    ORDER BY $orderBy
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backstage</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css?=v5">
</head>
<body>

<nav class="row align-items-center">
    <div class="col-8">
        <a href="index.php">
            <img src="https://placehold.co/65x65" class="d-block d-md-none">
        </a>
        <a href="index.php">
            <img src="https://placehold.co/285x65" class="d-none d-md-block">
        </a>
    </div>
    <div class="col-4 text-center">
        <div class="row d-none d-lg-flex" id="navLinks">
            <div class="col-3">
                <a href="index.php">HOME</a>
            </div>
            <div class="col-3">
                <a href="my_account.php">ACCOUNT</a>
            </div>
            <div class="col-3">
                <a href="venue-search.php">VENUES</a>
            </div>
            <div class="col-3">
                <?php if ($isLoggedIn): ?>
                    <div class="small">
                        <div class="fw-semibold">Welcome, <?= htmlspecialchars($userName) ?></div>
                        <a href="logout.php" class="text-danger">LOGOUT</a>
                    </div>
                <?php else: ?>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">LOGIN</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row d-flex d-lg-none">
            <div class="dropdown">
                <button type="button" id="hamburger" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
                    </svg>
                </button>
                <ul class="dropdown-menu" aria-labelledby="hamburger">
                    <li><a class="dropdown-item" href="index.php">Home</a></li>
                    <li><a class="dropdown-item" href="my_account.php">Account</a></li>
                    <li><a class="dropdown-item" href="venue-search.php">Venues</a></li>

                    <?php if ($isLoggedIn): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">Logged in as <?= htmlspecialchars($userName) ?></span></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <h2 class="mb-4">Find a Venue</h2>

    <div class="card p-3 mb-4">
        <form method="get" class="row g-3">
            <div class="col-lg-5">
                <label class="form-label">Search</label>
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Search venues, city, state, address..."
                    value="<?= htmlspecialchars($q) ?>"
                >
            </div>

            <div class="col-lg-3">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="az" <?= $sort === 'az' ? 'selected' : '' ?>>A-Z</option>
                    <option value="za" <?= $sort === 'za' ? 'selected' : '' ?>>Z-A</option>
                    <option value="ratingHigh" <?= $sort === 'ratingHigh' ? 'selected' : '' ?>>Highest Rating</option>
                    <option value="ratingLow" <?= $sort === 'ratingLow' ? 'selected' : '' ?>>Lowest Rating</option>
                </select>
            </div>

            <div class="col-lg-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">Search</button>
            </div>
        </form>
    </div>

    <div class="results-count">
  		<span class="results-number"><?= count($venues) ?></span>
  		result<?= count($venues) === 1 ? '' : 's' ?>
	</div>

    <div class="row g-4">
    <?php if (!empty($venues)): ?>
        <?php foreach ($venues as $venue): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="venue-page.php?id=<?= (int)$venue['id'] ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm venue-card">

                        <img
                            src="<?= htmlspecialchars($venue['image_url'] ?: 'venue-image/image1.jpg', ENT_QUOTES, 'UTF-8') ?>"
                            class="card-img-top venue-card-img"
                            alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <div class="card-body d-flex flex-column">

                            <!-- Title -->
                            <h5 class="card-title venue-card-title mb-1">
                                <?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h5>

                            <!-- Location -->
                            <p class="venue-card-location mb-3">
                                <?= htmlspecialchars($venue['city'] ?? '') ?>
                                <?= !empty($venue['state']) ? ', ' . htmlspecialchars($venue['state']) : '' ?>
                            </p>

                            <!-- Bottom meta row -->
                            <div class="d-flex justify-content-between align-items-center mt-auto venue-card-meta">

                                <!-- Rating -->
                                <span class="badge bg-primary venue-rating-badge">
                                    <?= number_format((float)$venue['avg_rating'], 1) ?> / 5
                                </span>

                                <!-- Review count -->
                                <span class="venue-review-count">
                                    <?= (int)$venue['review_count'] ?> review<?= (int)$venue['review_count'] === 1 ? '' : 's' ?>
                                </span>

                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center text-muted">
            No venues found
        </div>
    <?php endif; ?>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>