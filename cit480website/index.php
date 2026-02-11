
<?php
require __DIR__ . '/config.php'; // starts session & connects to DB

$isLoggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Fetch venues
$stmt = $pdo->query("
    SELECT id, name, image_url
    FROM venues
    ORDER BY created_at DESC
    LIMIT 6
");
$venues = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
  SELECT v.id, v.name, v.image_url, MAX(r.created_at) AS last_reviewed_at
  FROM venues v
  JOIN reviews r ON r.venue_id = v.id
  GROUP BY v.id, v.name, v.image_url
  ORDER BY last_reviewed_at DESC
  LIMIT 6
");
$recentlyReviewed = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
  SELECT v.id, v.name, v.image_url,
         AVG(r.rating) AS avg_rating,
         COUNT(*) AS review_count
  FROM venues v
  JOIN reviews r ON r.venue_id = v.id
  GROUP BY v.id, v.name, v.image_url
  HAVING COUNT(*) >= 3
  ORDER BY avg_rating DESC, review_count DESC
  LIMIT 6
");
$popularVenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
		<link rel="stylesheet" type="text/css" href="styles.css?=v3">
	</head>
	
	<body>
		<div class="container-fluid">
			<div class="row" id="banner">
				<div id="banner-text-box">
					<div id="banner-text-content">
						<h1>Save the night. Share the story.</h1>
						<h3>Explore, Log, and Share any live event experience.</h3>
					</div>
				</div>
				<div id="search-container">
					<form action="/search" method="get">
						<input id="input-field" type="search" name="q" placeholder="Search an event, venue, or location...">
						<button type="submit">Search</button>
					</form>
				</div>
			</div>
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
  						<?php if ($isLoggedIn): ?>
    						<div class="col-6 text-end">
      							<span class="fw-semibold">Welcome, <?= htmlspecialchars($userName) ?>!</span>
    						</div>
    						<div class="col-6 text-start">
      						<a href="logout.php" class="text-danger">LOGOUT</a>
    						</div>
  						<?php else: ?>
   							<div class="col-3">
      							<a href="index.php">HOME</a>
    						</div>
    					<div class="col-3">
      						<a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">LOGIN</a>
    					</div>
    					<div class="col-3">
      						<a href="#">REVIEWS</a>
    					</div>
    					<div class="col-3">
      						<a href="#">VENUES</a>
    					</div>
  						<?php endif; ?>
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
								<li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
								<li><a class="dropdown-item" href="#">Reviews</a></li>
								<li><a class="dropdown-item" href="#">Venues</a></li>
							</ul>
						</div>
					</div>
				</div>
			</nav>
			<div class="row about">
				<div class="col-lg-4" id="aboutItem1">
					<img src="https://placehold.co/50x50">
					<h4>Track your events</h4>
					<p class="aboutDesc">Build an archive of past concerts, sporting events, comedy shows, or any live events you've attended, and create wishlists for future events.</p>
				</div>
				<div class="col-lg-4" id="aboutItem2">
					<img src="https://placehold.co/50x50">
					<h4>Write and share reviews</h4>
					<p class="aboutDesc">Share your thoughts, give ratings, and post any photos or videos on different venues, and follow friends and other members to view theirs.</p>
				</div>
				<div class="col-lg-4" id="aboutItem3">
					<img src="https://placehold.co/50x50">
					<h4>View venue details</h4>
					<p class="aboutDesc">Get information on the next venue you attend, including location, parking information, upcoming events, ratings, reviews, and much more.</p>
				</div>
			</div>
			<div class="row">
				<div class="col" id="signUp">
					<h5>Join Backstage today!</h5>
					<button type="submit" data-bs-toggle="modal" data-bs-target="#signUpModal">Sign Up</button>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="sectionHeader">
						<h4>Recently Reviewed</h4>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="table-responsive">
						<table class="table carousel">
							<tr>
							<?php foreach ($recentlyReviewed as $row): ?>
								<td>
								<a href="venue-page.php?id=<?= (int)$row['id'] ?>">
									<div class="card" style="width: 300px;">
									<img
										src="<?= htmlspecialchars($row['image_url'] ?? 'venue-image/image1.jpg', ENT_QUOTES, 'UTF-8') ?>"
										class="card-img-top"
										alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>"
									>
									<div class="card-body">
										<h5><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></h5>
									</div>
									</div>
								</a>
								</td>
							<?php endforeach; ?>
							</tr>

						</table>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="sectionHeader">
						<h4>Popular Venues</h4>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="table-responsive">
						<table class="table carousel">
							<tr>
							<?php foreach ($popularVenues as $row): ?>
								<td>
								<a href="venue-page.php?id=<?= (int)$row['id'] ?>">
									<div class="card" style="width: 300px;">
									<img
										src="<?= htmlspecialchars($row['image_url'] ?? 'venue-image/image1.jpg', ENT_QUOTES, 'UTF-8') ?>"
										class="card-img-top"
										alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>"
									>
									<div class="card-body">
										<h5><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></h5>
									</div>
									</div>
								</a>
								</td>
							<?php endforeach; ?>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<footer>
				<p>Designed and developed by Zack Ahadi &middot; Ryan Cabanza &middot; Joey Frumento &middot; Martin Rodriguez &middot; Evelyn Tran</p>
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
		        <p class="mb-0 small"><a href="#">Didn't receive confirmation email?</a></p>
		      </div>
		    </div>
		  </div>
		</div>


		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
  			const loginForm = document.getElementById('loginForm');
  		if (loginForm) {
    		loginForm.addEventListener('submit', async (e) => {
     		 e.preventDefault();
      		const data = new FormData(loginForm);
     		 const res = await fetch('login.php', { method: 'POST', body: data });
     		 const json = await res.json();
     	 if (json.ok) {
       		 bootstrap.Modal.getInstance(document.getElementById('loginModal'))?.hide();
       		 location.reload();
     	} else {
        alert(json.error || 'Login failed.');
     	 }
    });
  }
	 		const signUpForm = document.getElementById('signUpForm');
  		if (signUpForm) {
    		signUpForm.addEventListener('submit', async (e) => {
     		 e.preventDefault();
      		const data = new FormData(signUpForm);
      		const res = await fetch('signup.php', { method: 'POST', body: data });
      		const json = await res.json();
      	if (json.ok) {
        		bootstrap.Modal.getInstance(document.getElementById('signUpModal'))?.hide();
        		location.reload();
      } else {
        alert(json.error || 'Sign up failed.');
      	}
    });
  }
});
</script>
	</body>
</html>