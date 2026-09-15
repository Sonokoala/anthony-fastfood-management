<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">User Navigation</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Home Link -->
<li class="nav-item">
  <a class="nav-link" href="home.php">Home</a>
</li>
        <!-- Availability Link -->
        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'availability.php') echo 'active'; ?>" href="availability.php">Availability</a>
        </li>
        <?php if ($_SESSION['roleID'] == 3): ?>
        <!-- Edit Profile Link (一般スタッフのみ表示) -->
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'staff_update.php' ? 'active' : '' ?>" 
             href="staff_update.php?id=<?= $_SESSION['staffID'] ?>">
             Edit Profile
          </a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="btn btn-outline-dark ms-2" href="logout.php">Log Out</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
