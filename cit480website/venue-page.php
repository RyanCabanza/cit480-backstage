
<?php
require __DIR__ . '/config.php'; // PDO + session

// Get venue id from query string: /venue-page.php?id=123
$venueId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($venueId <= 0) {
  http_response_code(400);
  exit('Missing or invalid venue id.');
}

// Logged-in state
$isLoggedIn = !empty($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? '';
$userId     = $_SESSION['user_id'] ?? null;

// Fetch venue
$vx = $pdo->prepare('SELECT * FROM venues WHERE id = ? LIMIT 1');
$vx->execute([$venueId]);
$venue = $vx->fetch();
if (!$venue) {
  http_response_code(404);
  exit('Venue not found.');
}

// Stats: average rating + count
$stats = $pdo->prepare('SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE venue_id = ?');
$stats->execute([$venueId]);
$stats = $stats->fetch() ?: ['avg_rating' => null, 'cnt' => 0];

// Recent reviews (show 10)
$rx = $pdo->prepare('
  SELECT r.id, r.rating, r.comment, r.created_at, u.name AS user_name
  FROM reviews r
  JOIN users u ON r.user_id = u.id
  WHERE r.venue_id = ?
  ORDER BY r.created_at DESC
  LIMIT 10
');
$rx->execute([$venueId]);
$reviews = $rx->fetchAll();
?>


<!DOCTYPE html>
<html>
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Backstage · Venue</title>

  <!-- Indie fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="styles.css?v=3">
    </head>
      <body class="venue-page">
        <div class="container-fluid">

    <!-- Navigation -->
    <nav class="container-fluid">
      <div class="row align-items-center">
        <!-- Logo -->
        <div class="col-4 text-start">
          <a href="index.php">
            <img src="Images/logo.png" alt="Backstage Logo" height="60">
          </a>
        </div>

        <!-- Links -->
        <div class="col-4 text-center">
          <div class="row d-none d-lg-flex" id="navLinks">
            <div class="col-3">
              <a href="index.php">HOME</a>
            </div>
            <div class="col-3">
              <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">ACCOUNT</a>
            </div>
            <div class="col-3">
              <a href="#">REVIEWS</a>
            </div>
            <div class="col-3">
              <a href="#">VENUES</a>
            </div>
          </div>
        </div>

        <!-- Hamburger for small screens -->
        <div class="col-4 text-end">
          <button id="hamburger" class="d-lg-none">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>
      </div>
    </nav>

      <!-- HERO / BANNER ( reuse existing banner styles here) -->
      <header class="row py-4 border-bottom" id="venue-hero">
        <div class="col-12 col-lg-8">
          <!-- Breadcrumb / back link -->
          <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Venues</a></li>
              <li class="breadcrumb-item active" aria-current="page"> <?= htmlspecialchars($venue['name']) ?></li>
            </ol>
          </nav>

          <!-- Venue title + meta -->
          <h1 class="mb-1">
              <?php if (!empty($venue['venue_url'])): ?>
                <a id="venueWebsiteLink" href="<?= htmlspecialchars($venue['venue_url']) ?>" target="_blank" rel="noopener noreferrer">
                  <?= htmlspecialchars($venue['name']) ?>
                </a>
              <?php else: ?>
                <?= htmlspecialchars($venue['name']) ?>
              <?php endif; ?>
              </h1>
              <p class="mb-1 text-muted">
                <?= htmlspecialchars($venue['address'] ?? '') ?>
                <?= !empty($venue['city']) ? ' · ' . htmlspecialchars($venue['city']) : '' ?>
                <?= !empty($venue['state']) ? ', ' . htmlspecialchars($venue['state']) : '' ?>
              </p>
        </div>

        <!-- Overall rating + CTA on the right -->
        <div class="col-12 col-lg-4 d-flex align-items-end justify-content-lg-end mt-3 mt-lg-0">
          <div class="text-lg-end">
            <div class="d-flex justify-content-start justify-content-lg-end align-items-baseline">
              <span class="display-5 fw-bold"><?= $stats['avg_rating'] ?: '—' ?></span>
              <span class="ms-2">/ 5</span>
           </div>
              <p class="text-muted mb-2">Based on <?= (int)$stats['cnt'] ?> reviews</p>
              <a class="btn btn-primary" href="#write-review">
              Write a Review
            </a>
          </div>
        </div>
      </header>

      <!-- MAIN CONTENT GRID -->
      <main class="row mt-4">

        <!-- LEFT COLUMN: overview, upcoming events, reviews -->
        <section class="col-12 col-lg-8">

          <!-- Visual / cover image area -->
          <div class="card mb-4">
                          <img
                src="<?= htmlspecialchars($venue['image_url'] ?: 'venue-image/image1.jpg', ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>"
                class="img-fluid rounded w-100"
                          >

          </div>

          <!-- Venue overview / key info -->
          <div class="card mb-4">
            <div class="card-body">
              <h2 class="h5 mb-3">Overview</h2>
              <p class="mb-3">
                Short description of the venue. Mention what kinds of events happen here,
                what the vibe is like, and anything a first-time visitor should know.
              </p>

              <div class="row">
                <div class="col-6 col-md-3 mb-3">
                  <p class="mb-1 text-muted small">Capacity</p>
                  <p class="mb-0 fw-semibold">15,000</p>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <p class="mb-1 text-muted small">Type</p>
                  <p class="mb-0 fw-semibold">Indoor Arena</p>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <p class="mb-1 text-muted small">Parking</p>
                  <p class="mb-0 fw-semibold">On-site & lots</p>
                </div>
                <div class="col-6 col-md-3 mb-3">
                  <p class="mb-1 text-muted small">Accessibility</p>
                  <p class="mb-0 fw-semibold">Wheelchair access</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming events list (simple wireframe-y list) -->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 mb-0">Upcoming Events</h2>
                <a id="venueEventsLink" href="https://examplevenue.com/events" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                  More Info
                </a>
              </div>

              <!-- Event row -->
              <div class="d-flex mb-3 pb-3 border-bottom">
                <div class="me-3 text-center">
                  <div class="fw-bold">NOV</div>
                  <div class="fs-4 fw-bold">12</div>
                </div>
                <div class="flex-grow-1">
                  <p class="mb-1 fw-semibold">Artist Name · Tour Title</p>
                  <p class="mb-0 text-muted small">Doors 7:00 PM · Show 8:00 PM</p>
                </div>
              </div>

              <div class="d-flex mb-3 pb-3 border-bottom">
                <div class="me-3 text-center">
                  <div class="fw-bold">NOV</div>
                  <div class="fs-4 fw-bold">18</div>
                </div>
                <div class="flex-grow-1">
                  <p class="mb-1 fw-semibold">Home Team vs. Away Team</p>
                  <p class="mb-0 text-muted small">Tip-off 6:30 PM</p>
                </div>
              </div>

              <div class="d-flex">
                <div class="me-3 text-center">
                  <div class="fw-bold">DEC</div>
                  <div class="fs-4 fw-bold">02</div>
                </div>
                <div class="flex-grow-1">
                  <p class="mb-1 fw-semibold">Comedy Night Live</p>
                  <p class="mb-0 text-muted small">8:00 PM · 18+</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviews header + filter row -->
          <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
            <h2 class="h5 mb-0">Reviews</h2>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" style="max-width: 180px;">
                <option selected>Sort by: Most recent</option>
                <option>Highest rated</option>
                <option>Lowest rated</option>
              </select>
              <select class="form-select form-select-sm" style="max-width: 160px;">
                <option selected>All event types</option>
                <option>Concert</option>
                <option>Sports</option>
                <option>Comedy</option>
              </select>
            </div>
          </div>

          <!-- Write-a-review form (anchor target) -->
            <div id="write-review" class="card mb-3">
              <div class="card-body">
                <h3 class="h6 mb-3">Write a review for this venue</h3>

                <?php if ($isLoggedIn): ?>
                  <!-- Logged-in users can post -->
                  <form id="reviewForm" method="post" action="review_create.php">
                    <input type="hidden" name="venue_id" value="<?= (int)$venueId ?>">

                    <div class="mb-3">
                      <label for="reviewName" class="form-label">Reviewer</label>
                      <input type="text" class="form-control" id="reviewName"
                            value="<?= htmlspecialchars($userName) ?>" readonly>
                    </div>

                    <div class="mb-3">
                      <label for="reviewRating" class="form-label">Your rating (1–5)</label>
                      <input type="number" class="form-control" id="reviewRating" name="rating"
                            min="1" max="5" step="1" required>
                    </div>

                    <div class="mb-3">
                      <label for="reviewText" class="form-label">Your review</label>
                      <textarea class="form-control" id="reviewText" name="comment" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit review</button>
                  </form>

                <?php else: ?>
                  <!-- Guests see message only -->
                  <p class="text-muted mb-0">
                    You must <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">log in</a> to write a review.
                  </p>
                <?php endif; ?>
              </div>
            </div>

          <!-- Individual review cards -->

              <!-- Existing reviews from the database -->
              <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                  <article class="card mb-3">
                    <div class="card-body">
                      <div class="d-flex justify-content-between">
                        <div>
                          <p class="mb-0 fw-semibold"><?= htmlspecialchars($rev['user_name']) ?></p>
                          <p class="mb-0 text-muted small">
                            Posted: <?= htmlspecialchars(date('M j, Y', strtotime($rev['created_at']))) ?>
                          </p>
                        </div>
                        <div class="text-end">
                          <p class="mb-0 fw-semibold"><?= (float)$rev['rating'] ?> / 5</p>
                        </div>
                      </div>
                      <hr>
                      <p class="mb-2"><?= nl2br(htmlspecialchars($rev['comment'] ?? '')) ?></p>
                    </div>
                  </article>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted">No reviews yet. Be the first to write one!</p>
              <?php endif; ?>


          <!--<article class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="mb-0 fw-semibold">Username1</p>
                  <p class="mb-0 text-muted small">Attended: Oct 12, 2024 · Concert</p>
                </div>
                <div class="text-end">
                  <p class="mb-0 fw-semibold">4.5 / 5</p>
                  <p class="mb-0 text-muted small">Sound, View, Crowd</p>
                </div>
              </div>
              <hr>
              <p class="mb-2">
                Review text goes here. Talk about the lines, staff, sound quality,
                seating, and anything that would help someone decide to go.
              </p>
              <ul class="small mb-0">
                <li><strong>Sound:</strong> Clear, loud but not harsh.</li>
                <li><strong>View:</strong> Great from upper bowl.</li>
                <li><strong>Tip:</strong> Get there early for parking.</li>
              </ul>
            </div>
          </article>

          <article class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="mb-0 fw-semibold">Username2</p>
                  <p class="mb-0 text-muted small">Attended: Sep 5, 2024 · Basketball</p>
                </div>
                <div class="text-end">
                  <p class="mb-0 fw-semibold">4.0 / 5</p>
                  <p class="mb-0 text-muted small">Facilities, Parking</p>
                </div>
              </div>
              <hr>
              <p class="mb-2">
                Another sample review. Mention concession prices, bathroom lines,
                and how easy it was to get in and out.
              </p>
            </div>
          </article> -->

          <!-- Pagination placeholder -->
          <nav aria-label="Review pages" class="mt-3">
            <ul class="pagination">
              <li class="page-item disabled">
                <span class="page-link">Previous</span>
              </li>
              <li class="page-item active">
                <span class="page-link">1</span>
              </li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Next</a>
              </li>
            </ul>
          </nav>
        </section>

        <!-- RIGHT COLUMN: rating breakdown, location, quick facts -->
        <aside class="col-12 col-lg-4 mt-4 mt-lg-0">

          <!-- Rating breakdown -->
          <div class="card mb-4">
            <div class="card-body">
              <h2 class="h6 mb-3">Rating Breakdown</h2>

              <div class="mb-2">
                <div class="d-flex justify-content-between small">
                  <span>Sound quality</span>
                  <span>4.7</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: 90%;"></div>
                </div>
              </div>

              <div class="mb-2">
                <div class="d-flex justify-content-between small">
                  <span>View of stage</span>
                  <span>4.5</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: 85%;"></div>
                </div>
              </div>

              <div class="mb-2">
                <div class="d-flex justify-content-between small">
                  <span>Facilities</span>
                  <span>4.0</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: 80%;"></div>
                </div>
              </div>

              <div class="mb-0">
                <div class="d-flex justify-content-between small">
                  <span>Location / parking</span>
                  <span>3.8</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar" style="width: 75%;"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Location / map block -->
          <div class="card mb-4">
            <div class="card-body">
              <h2 class="h6 mb-3">Location</h2>
                  <p class="mb-1">
                    <?= htmlspecialchars($venue['address'] ?? '') ?><br>
                    <?= htmlspecialchars($venue['city'] ?? '') ?><?= !empty($venue['state']) ? ', ' . htmlspecialchars($venue['state']) : '' ?>
                  </p>

              <p class="text-muted small mb-3">
                Parking garages and public transit stops nearby.
              </p>
              <div class="ratio ratio-4x3">

                <?php
                  $fullAddress = trim(($venue['address'] ?? '') . ', ' . ($venue['city'] ?? '') . ', ' . ($venue['state'] ?? ''));
                  $embedUrl = 'https://www.google.com/maps?q=' . urlencode($fullAddress) . '&output=embed';
                ?>
              <iframe
                  src="<?= htmlspecialchars($embedUrl) ?>"
                  style="border:0;"
                  allowfullscreen=""
                  loading="lazy">
              </iframe>
              </div>
            </div>
          </div>

          <!-- Quick highlights -->
          <div class="card">
            <div class="card-body">
              <h2 class="h6 mb-3">Highlights</h2>
              <ul class="small mb-0">
                <li>Great for concerts and sports</li>
                <li>Multiple bars and food options</li>
                <li>Family-friendly seating sections</li>
                <li>Nearby hotels and restaurants</li>
              </ul>
            </div>
          </div>
        </aside>
      </main>

      <footer class="row mt-5 py-4 border-top">
        <div class="col text-center small text-muted">
          Backstage · Venue details prototype
        </div>
      </footer>

    </div>

 <!--Javascript code for future use integration with the backend -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
  </body>
</html>