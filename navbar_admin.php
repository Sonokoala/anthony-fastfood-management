<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4" id="main-navbar">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Admin Navigation</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Home Link -->
        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'home.php') echo 'active'; ?>" href="home.php">Home</a>
        </li>
        <!-- Staff Management Link -->
        <li class="nav-item">
          <a class="nav-link <?php if(in_array(basename($_SERVER['PHP_SELF']), ['staff_create.php', 'staff_update.php', 'staff_delete.php'])) echo 'active'; ?>" href="staff_create.php">Manage Staff</a>
        </li>
        <!-- Availability Link -->
        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'availability.php') echo 'active'; ?>" href="availability.php">Availability</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="btn btn-outline-dark ms-2" href="logout.php">Log Out</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
