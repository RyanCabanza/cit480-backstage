
<?php
require __DIR__ . '/config.php'; // PDO + session

// Get venue id from query string: /venue-page.php?id=123
$venueId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($venueId <= 0) {
  http_response_code(400);
  exit('Missing or invalid venue id.');
}

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

 //sorting 
$sort = $_GET['sort'] ?? 'recent';

switch ($sort) {
    case 'rating_desc': // highest rated first
        $orderBy = 'r.rating DESC, r.created_at DESC';
        break;
    case 'rating_asc': // lowest rated first
        $orderBy = 'r.rating ASC, r.created_at DESC';
        break;
    case 'recent':
    default:
        $orderBy = 'r.created_at DESC';
        break;
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
$stats = $pdo->prepare('
  SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS cnt
  FROM reviews
  WHERE venue_id = ?
');
$stats->execute([$venueId]);
$stats = $stats->fetch() ?: ['avg_rating' => null, 'cnt' => 0];

$totalReviews = (int)$stats['cnt'];
$totalPages = max(1, (int)ceil($totalReviews / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;


// Recent reviews (show 10)
$rx = $pdo->prepare("
  SELECT
    r.id,
    r.rating,
    r.comment,
    r.created_at,
    u.name AS user_name,
    u.profile_image_path
  FROM reviews r
  JOIN users u ON r.user_id = u.id
  WHERE r.venue_id = ?
  ORDER BY $orderBy
  LIMIT $perPage OFFSET $offset
");

$rx->execute([$venueId]);
$reviews = $rx->fetchAll();
?>



<!DOCTYPE html>
<html>
  <head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Backstage</title>

		<!-- Indie fonts -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">


		<!-- Bootstrap -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">


		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="styles.css?=v5">
	</head>
      <body class="venue-page">
        <div class="container-fluid">

    <!-- Navigation -->
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
					<a href="#">VENUES</a>
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
								<li><a class="dropdown-item" href="#">Account</a></li>
								<li><a class="dropdown-item" href="#">Venues</a></li>

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
              </div>
              <div class="container-fluid venue-content">

      <!-- HERO / BANNER ( reuse existing banner styles here) -->
      <header class="row py-4 border-bottom" id="venue-hero">
        <div class="col-12 col-lg-8">

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
               <h2 class="h5 mb-3">AI Overview</h2>

              <?php
                // ai fields expected on $venue: ai_summary, ai_summary_updated_at, ai_summary_status
                $aiSummary = $venue['ai_summary'] ?? null;
                $aiUpdated = $venue['ai_summary_updated_at'] ?? null;
                $aiStatus  = $venue['ai_summary_status'] ?? 'idle';
              ?>

              <?php if ($aiStatus === 'pending'): ?>
                <div class="alert alert-warning py-2" role="alert">
                  AI overview is being generated — check back in a moment.
                </div>
              <?php elseif ($aiStatus === 'error'): ?>
                <div class="alert alert-danger py-2" role="alert">
                  AI overview temporarily unavailable. We're looking into it.
                </div>
              <?php endif; ?>

              <?php if (!empty($aiSummary)): ?>
                <article class="ai-overview mb-3">
                  <?php
                    // Split into paragraphs on double newlines and safely escape
                    $paragraphs = preg_split("/\r?\n\r?\n/", trim($aiSummary));
                    foreach ($paragraphs as $p) {
                        // trim then escape
                        $p = trim($p);
                        if ($p === '') continue;
                        echo '<p class="mb-2">' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>';
                    }
                  ?>
                  <footer class="ai-meta">
                    <small class="text-muted">
                      Updated <?php echo htmlspecialchars($aiUpdated ? date('M j, Y, g:ia', strtotime($aiUpdated)) : '—'); ?>
                    </small>
                  </footer>
                </article>
              <?php else: ?>
                <div class="mb-3 text-muted">
                  <p class="mb-2">Short description of the venue. Mention what kinds of events happen here, what the vibe is like, and anything a first-time visitor should know.</p>
                  <p class="mb-0">No AI overview is available yet for this venue.</p>
                </div>
              <?php endif; ?>

            </div>
          </div>

      
         

          <!-- Reviews header + filter row -->
          <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
            <div id="reviews"></div>
            <h2 class="h5 mb-0">Reviews</h2>
            <div class="d-flex gap-2">
              <!--
              <select class="form-select form-select-sm" style="max-width: 180px;">
                <option selected>Sort by: Most recent</option>
                <option>Highest rated</option>
                <option>Lowest rated</option>
              </select>
              -->
              <select
                  id="sortSelect"
                  class="form-select form-select-sm"
                  style="max-width: 180px;"
              >
                  <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Sort by: Most recent</option>
                  <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Highest rated</option>
                  <option value="rating_asc" <?= $sort === 'rating_asc' ? 'selected' : '' ?>>Lowest rated</option>
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
                    <?= csrf_input() ?>
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
                  <?php
                  $avatar = !empty($rev['profile_image_path'])
                  ? $rev['profile_image_path']
                 : 'https://placehold.co/48x48';
              ?>
 

  <article class="card mb-3">
    <div class="card-body">

      <div class="d-flex justify-content-between">
        <div class="d-flex align-items-center gap-3">

          <img
            src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
            alt="Profile picture"
            class="review-avatar"
            onerror="this.onerror=null;this.src='Images/default-avatar.png';"
        />

          <div>
            <p class="mb-0 fw-semibold">
              <?= htmlspecialchars($rev['user_name']) ?>
            </p>
            <p class="mb-0 text-muted small">
              Posted: <?= htmlspecialchars(date('M j, Y', strtotime($rev['created_at']))) ?>
            </p>
          </div>

        </div>

        <div class="text-end">
          <p class="mb-0 fw-semibold">
            <?= (float)$rev['rating'] ?> / 5
          </p>
        </div>
      </div>

      <hr>

      <p class="mb-2">
        <?= nl2br(htmlspecialchars($rev['comment'] ?? '')) ?>
      </p>

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

         <?php if ($totalReviews > 0 && $totalPages > 1): ?>
              <nav aria-label="Review pages" class="mt-3">
                <ul class="pagination">

                  <!-- Previous -->
                  <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                      href="venue-page.php?id=<?= (int)$venueId ?>&page=<?= max(1, $page - 1) ?>#reviews">
                      Previous
                    </a>
                  </li>

                  <?php
                    // show up to 5 page buttons
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);

                    // keep it 5 wide when near edges
                    if (($end - $start) < 4) {
                      $start = max(1, $end - 4);
                      $end   = min($totalPages, $start + 4);
                    }
                  ?>

                  <?php if ($start > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="venue-page.php?id=<?= (int)$venueId ?>&page=1#reviews">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                      <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                      <a class="page-link" href="venue-page.php?id=<?= (int)$venueId ?>&page=<?= $p ?>#reviews">
                        <?= $p ?>
                      </a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?>
                      <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                      <a class="page-link" href="venue-page.php?id=<?= (int)$venueId ?>&page=<?= $totalPages ?>#reviews">
                        <?= $totalPages ?>
                      </a>
                    </li>
                  <?php endif; ?>

                  <!-- Next -->
                  <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                      href="venue-page.php?id=<?= (int)$venueId ?>&page=<?= min($totalPages, $page + 1) ?>#reviews">
                      Next
                    </a>
                  </li>

                </ul>
              </nav>
            <?php endif; ?>
        </section>

        <!-- RIGHT COLUMN: rating breakdown, location, quick facts -->
        <aside class="col-12 col-lg-4 mt-4 mt-lg-0">

          <!-- Rating breakdown 
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
                        -->
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

          <!-- Quick highlights
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
                    -->
        </aside>
        	
      </main>

      <footer>
				<p>Designed and developed by Zack Ahadi &middot; Ryan Cabanza &middot; Joey Frumento &middot; Martin Rodriguez &middot; Evelyn Tran.   Have a question? 
					<a href="contact.html">Contact Us</a>
				</p>
			</footer>

    </div>
<!-- LOGIN MODAL -->
		<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header justify-content-center">
		      	<h1 class="modal-title fs-5" id="loginModalLabel">Login</h1>
		        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		      </div>
		      <div class="modal-body">
		       <form id="loginForm" method="post" action="login.php">
            <?= csrf_input() ?>
  				<div class="mb-3">
    			<label for="loginEmail" class="form-label">Email address</label>
    			<input type="email" class="form-control" id="loginEmail" name="email" required>
  				</div>
  				<div class="mb-3">
   				 <label for="loginPassword" class="form-label">Password</label>
   				 <input type="password" class="form-control" id="loginPassword" name="password" required>
 			 </div>
  			<button type="submit">Login</button>
			</form>

		      </div>
		      <div class="modal-footer">
		        <p class="mb-0 small">New around here? <a href="#" data-bs-toggle="modal" data-bs-target="#signUpModal">Sign up</a></p>
		        <p class="mb-0 small"><a href="#">Forgot password?</a></p>
		      </div>
		    </div>
		  </div>
		</div>


		<!-- SIGN UP MODAL -->
		<div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="signUpModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header justify-content-center">
		      	<h1 class="modal-title fs-5" id="signUpModalLabel">Sign Up</h1>
		        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		      </div>
		      <div class="modal-body">
		        <form id="signUpForm" method="post" action="signup.php">
             <?= csrf_input() ?>
  				<div class="mb-3">
    				<label for="signUpUser" class="form-label">Name or Username</label>
    				<input type="text" class="form-control" id="signUpUser" name="username" required>
  				</div>
  				<div class="mb-3">
    				<label for="signUpEmail" class="form-label">Email address</label>
    				<input type="email" class="form-control" id="signUpEmail" name="email" required>
 				 </div>
  				<div class="mb-3">
   					 <label for="signUpPassword" class="form-label">Password</label>
   					 <input type="password" class="form-control" id="signUpPassword" name="password" required>
 				 </div>
 				 <div class="mb-3">
   					 <label for="passwordConfirmation" class="form-label">Password Confirmation</label>
    					<input type="password" class="form-control" id="passwordConfirmation" name="password_confirm" required>
  				</div>
 				 <button type="submit">Sign Up</button>
				</form>

		      </div>
		      <div class="modal-footer">
		        <p class="mb-0 small">Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></p>
		      </div>
		    </div>
		  </div>
		</div>


		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<!-- Login + Signup AJAX -->

<script>
document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const data = new FormData(loginForm);
        const res = await fetch('login.php', {
          method: 'POST',
          body: data
        });

        const text = await res.text();
        const json = JSON.parse(text);

        if (json.ok) {
          bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide();
          location.reload();
        } else {
          alert(json.error || 'Login failed.');
        }
      } catch (err) {
        console.error('Login error:', err);
        alert('Login failed.');
      }
    });
  }

  const signUpForm = document.getElementById('signUpForm');
  if (signUpForm) {
    signUpForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      try {
        const data = new FormData(signUpForm);
        const res = await fetch('signup.php', {
          method: 'POST',
          body: data
        });

        const text = await res.text();
        const json = JSON.parse(text);

        if (json.ok) {
          bootstrap.Modal.getInstance(document.getElementById('signUpModal'))?.hide();
          location.reload();
        } else {
          alert(json.error || 'Sign up failed.');
        }
      } catch (err) {
        console.error('Signup error:', err);
        alert('Sign up failed.');
      }
    });
  }
});

</script>

<!-- Sort dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sortSelect = document.getElementById('sortSelect');
  if (!sortSelect) return;

  sortSelect.addEventListener('change', function () {
    const sort = this.value;
    window.location.href =
      "venue-page.php?id=<?= (int)$venueId ?>&sort=" + sort + "&page=1#reviews";
  });
});
</script>

</body>
</html>