<?php
require __DIR__ . '/config.php'; // starts session & connects to DB

$isLoggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userEmail  = $_SESSION['user_email'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);
$userImage = $_SESSION ['profile_image_path'] ?? '';

// Reviews the user wrote (1 per venue)
$rx = $pdo->prepare("
  SELECT
    r.rating,
    r.comment,
    r.created_at,
    v.id   AS venue_id,
    v.name AS venue_name
  FROM reviews r
  JOIN venues v ON v.id = r.venue_id
  WHERE r.user_id = ?
  ORDER BY r.created_at DESC
");
$rx->execute([$userId]);
$userReviews = $rx->fetchAll();


if (!$isLoggedIn) {
  header('Location: index.php'); // 
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Backstage · My Account</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <!-- NAV -->
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
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
      <h2 class="mb-0">My Account</h2>

      <!-- Optional: quick profile summary -->
      <div class="small text-muted">
        Signed in as: <span id="summaryEmail"><?= htmlspecialchars($userEmail) ?></span>
      </div>
    </div>

    <div class="row g-4">
      <!-- PROFILE -->
      <div class="col-lg-6">
        <div class="card p-3">
          <h4 class="mb-3">Profile</h4>

          <div id="profileAlert" class="alert d-none" role="alert"></div>

          <form id="profileForm" method="post" action="account_update.php" enctype="multipart/form-data">
          <input type="hidden" name="remove_image" id="removeImageField" value="0">

            <!-- Profile Picture -->
<div class="mb-3">
  <label class="form-label">Profile Picture</label>

  <div class="d-flex align-items-center gap-3 flex-wrap">
    <img
      id="profilePreview"
      src="<?= htmlspecialchars($userImage ?: 'https://placehold.co/96x96') ?>"
      alt="Profile picture preview"
      width="96"
      height="96"
      style="border-radius: 999px; object-fit: cover; border: 2px solid rgba(255,255,255,0.15);"
    >

    <div class="d-flex gap-2 flex-wrap">
      <input
        class="form-control"
        type="file"
        id="profileImageInput"
        name="profile_image"
        accept="image/*"
        style="max-width: 320px;"
      >
      <button class="btn btn-outline-secondary" type="button" id="removeProfileImageBtn">
        Remove
      </button>
    </div>
  </div>

  <div class="form-text">
    JPG/PNG recommended. (This is preview-only for now; later it will upload to the server.)
  </div>
</div>
            <div class="mb-3">
              <label class="form-label">User Name</label>
              <input class="form-control" id="nameInput" name="name" value="<?= htmlspecialchars($userName) ?>" required readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" id="emailInput" name="email" type="email" value="<?= htmlspecialchars($userEmail) ?>" required readonly>
            </div>

            <button class="btn btn-primary" type="submit">Save Changes</button>
            <button class="btn btn-outline-secondary ms-2" type="button" id="resetProfileBtn">Edit</button>
          </form>
        </div>
      </div>

      <!-- PASSWORD -->
      <div class="col-lg-6">
        <div class="card p-3">
          <h4 class="mb-3">Change Password</h4>

          <div id="passwordAlert" class="alert d-none" role="alert"></div>

          <form id="passwordForm">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input class="form-control" name="current_password" type="password" placeholder="••••••••" required>
            </div>

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input class="form-control" id="newPass" name="new_password" type="password" minlength="8" required>
              <div class="form-text">Minimum 8 characters.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input class="form-control" id="confirmPass" name="confirm_password" type="password" minlength="8" required>
            </div>

            <button class="btn btn-primary" type="submit">Update Password</button>
          </form>
        </div>
      </div>

      <!-- FAVORITES -->
      <div class="col-lg-6">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Pinned / Favorite Venues</h4>
            <button class="btn btn-sm btn-outline-primary" type="button" id="addMockFavoriteBtn">+ Add Mock</button>
          </div>

          <p class="text-muted small mb-3">
            (Mock data for layout/testing. Later you’ll load this from DB.)
          </p>

          <ul class="list-group" id="favoritesList">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="venue.html?id=1">insert link to page and source name </a>
              <button class="btn btn-sm btn-outline-danger" type="button" data-remove-fav>Remove</button>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="venue.html?id=2">insert link to page and source name </a>
              <button class="btn btn-sm btn-outline-danger" type="button" data-remove-fav>Remove</button>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="venue.html?id=3">insert link to page and source name</a>
              <button class="btn btn-sm btn-outline-danger" type="button" data-remove-fav>Remove</button>
            </li>
          </ul>

          <div id="favEmpty" class="text-muted mt-3 d-none">
            You don’t have any favorites yet.
          </div>
        </div>
      </div>

      <!-- COMMENTED -->
      <div class="col-lg-6">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Venues You Commented On</h4>
          </div>

          <p class="text-muted small mb-3">
            (Mock data for layout/testing. Later you’ll load this from your reviews table.)
          </p>

         <ul class="list-group" id="commentedList">
  <?php if (!empty($userReviews)): ?>
    <?php foreach ($userReviews as $rev): ?>
      <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-start gap-3">
          <div class="flex-grow-1">
            <a href="venue-page.php?id=<?= (int)$rev['venue_id'] ?>" class="fw-semibold">
              <?= htmlspecialchars($rev['venue_name']) ?>
            </a>

            <div class="small text-muted mt-2">
              <div><strong>Rating:</strong> <?= (int)$rev['rating'] ?></div>
              <div>
                <strong>Review:</strong>
                <?= nl2br(htmlspecialchars($rev['comment'] ?: '—')) ?>
              </div>
            </div>
          </div>

          <span class="small text-muted text-nowrap">
            <?= htmlspecialchars(date('M j, Y', strtotime($rev['created_at']))) ?>
          </span>
        </div>
      </li>
    <?php endforeach; ?>
  <?php else: ?>
    <li class="list-group-item text-muted">
      No reviews yet. Go leave your first one!
    </li>
  <?php endif; ?>
</ul>

          <div id="commentEmpty" class="text-muted mt-3 d-none">
            No reviews yet. Go leave your first one!
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script>
  
    // --- Helpers ---
    function showAlert(el, msg, type) {
      el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
      el.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
      el.textContent = msg;
    }
    function hideAlert(el) {
      el.classList.add('d-none');
      el.textContent = '';
    }
    function updateEmptyStates() {
      const favList = document.getElementById('favoritesList');
      const favEmpty = document.getElementById('favEmpty');
      favEmpty.classList.toggle('d-none', favList.children.length !== 0);

      const cList = document.getElementById('commentedList');
      const cEmpty = document.getElementById('commentEmpty');
      cEmpty.classList.toggle('d-none', cList.children.length !== 0);
    }

    // --- Profile edit ---
const profileForm   = document.getElementById('profileForm');
const profileAlert  = document.getElementById('profileAlert');
const editBtn       = document.getElementById('resetProfileBtn');
const nameInput     = document.getElementById('nameInput');
const emailInput    = document.getElementById('emailInput');
const profileImageInput = document.getElementById('profileImageInput');
const profilePreview = document.getElementById('profilePreview');
const removeProfileImageBtn = document.getElementById('removeProfileImageBtn');
const DEFAULT_AVATAR = "https://placehold.co/96x96";


// store initial values
let initialProfile = {
  name: nameInput.value,
  email: emailInput.value
};

function setEditing(isEditing) {
  nameInput.readOnly = !isEditing;
  emailInput.readOnly = !isEditing;

  editBtn.textContent = isEditing ? 'Cancel' : 'Edit';

  if (isEditing) {
    nameInput.focus();
  }
}

// start locked
setEditing(false);

// Edit / Cancel button
editBtn.addEventListener('click', () => {
  hideAlert(profileAlert);

  const currentlyEditing = !nameInput.readOnly;

  if (currentlyEditing) {
    // Cancel
    nameInput.value = initialProfile.name;
    emailInput.value = initialProfile.email;
    setEditing(false);
  } else {
    setEditing(true);
  }
});

// Save Changes
profileForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  hideAlert(profileAlert);
  const editingText =!nameInput.readOnly;
  const removeField = document.getElementById('removeImageField');
  const removingImage = removeField && removeField.value === '1'; 
  const uploadingImage = profileImageInput && profileImageInput.files && profileImageInput.files.length > 0;

  if (!editingText && !removingImage && !uploadingImage) {
    showAlert(profileAlert, 'Click Edit first. (or upload/remove an image).', 'danger');
    return;
  }
  const data = new FormData(profileForm);

  const res = await fetch('account_update.php', {
    method: 'POST',
    body: data,
    credentials: 'same-origin'
  });

  const json = await res.json();

  if (json.ok) {
    document.getElementById('navUserName').textContent = json.name;
    document.getElementById('summaryEmail').textContent = json.email;
    document.getElementById('removeImageField').value = '0';

    initialProfile = { name: json.name, email: json.email };

    setEditing(false);
    showAlert(profileAlert, 'Profile updated.', 'success');
  } else {
    showAlert(profileAlert, json.error || 'Update failed.', 'danger');
  }
    });

    // --- Password mock save ---
    const passwordForm = document.getElementById('passwordForm');
    const passwordAlert = document.getElementById('passwordAlert');
    const newPass = document.getElementById('newPass');
    const confirmPass = document.getElementById('confirmPass');

    passwordForm.addEventListener('submit', (e) => {
      e.preventDefault();
      hideAlert(passwordAlert);

      if (newPass.value !== confirmPass.value) {
        showAlert(passwordAlert, 'New passwords do not match.', 'danger');
        return;
      }
      if (newPass.value.length < 8) {
        showAlert(passwordAlert, 'New password must be at least 8 characters.', 'danger');
        return;
      }

      showAlert(passwordAlert, 'Password updated (mock). Later this will update the database.', 'success');
      passwordForm.reset();
    });

    // --- Favorites remove ---
    function wireRemoveFavButtons() {
      document.querySelectorAll('[data-remove-fav]').forEach(btn => {
        if (btn._wired) return;
        btn._wired = true;
        btn.addEventListener('click', () => {
          btn.closest('li').remove();
          updateEmptyStates();
        });
      });
    }
    wireRemoveFavButtons(); 

    updateEmptyStates();

      // Profile image preview (mock)

   /*profileImageInput?.addEventListener('change', () => {
    const file = profileImageInput.files && profileImageInput.files[0]; */
   profileImageInput?.addEventListener('change', () => {
    const removeField = document.getElementById('removeImageField');
    if (removeField) removeField.value = '0';
    const file = profileImageInput.files && profileImageInput.files[0];
   
   
    if (!file) return;

    // Basic safety: only images, and keep it reasonable size for preview
    if (!file.type.startsWith('image/')) {
      alert('Please choose an image file.');
      profileImageInput.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('Please choose an image under 5MB.');
      profileImageInput.value = '';
      return;
    }

    const url = URL.createObjectURL(file);
    profilePreview.src = url;

    // Optional: clean up object URL later
    profilePreview.onload = () => URL.revokeObjectURL(url);
  });

removeProfileImageBtn.addEventListener('click', (e) => {
  e.preventDefault();

  const removeField = document.getElementById('removeImageField');
  if (removeField) {
    removeField.value = '1';
  }

  profileImageInput.value = '';
  profilePreview.src = DEFAULT_AVATAR;

  profileForm.requestSubmit();
});
  </script>
</body>
</html>