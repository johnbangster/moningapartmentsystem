<?php
// session_start();
require 'config/dbcon.php';

// Default unread notifications count
$unread_total = 0;
// $notif_result = null;


if (isset($_SESSION['auth_user'])) {
    $user_id   = intval($_SESSION['auth_user']['user_id']);
    $auth_role = $_SESSION['auth_role'] ?? '';

    // Count unread notifications
    if ($auth_role === 'admin' || $auth_role === 'employee' || $auth_role === 'renter') {
        $count_sql = "SELECT COUNT(*) AS total FROM notifications 
                      WHERE type = 'bill_paid' AND is_read = 0";
    } else {
        $count_sql = "SELECT COUNT(*) AS total FROM notifications 
                      WHERE user_id = $user_id AND type = 'bill_created' AND is_read = 0";
    }

    $count_result = mysqli_query($con, $count_sql);
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $unread_total = $count_row['total'];
    }

    // Fetch latest 5 notifications for dropdown
    if ($auth_role === 'admin' || $auth_role === 'employee' || $auth_role === 'renter') {
        $notif_sql = "SELECT * FROM notifications 
                      WHERE type = 'bill_paid' 
                      ORDER BY created_at DESC LIMIT 5";
    } else {
        $notif_sql = "SELECT * FROM notifications 
                      WHERE user_id = $user_id AND type = 'bill_created' 
                      ORDER BY created_at DESC LIMIT 5";
    }

    $notif_result = mysqli_query($con, $notif_sql);
}
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="index.php">Monings System</a>

    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" 
            id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Right Side-->
    <ul class="navbar-nav ms-auto me-md-0 me-3 me-lg-4">

        <!-- Notifications Dropdown-->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" 
             data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell"></i>
            <span id="notifCount" class="badge bg-success"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" id="notifList">
            <li><span class="dropdown-item">Loading...</span></li>
          </ul>
        </li>
        <!-- End Notifications -->

        <!-- User Dropdown -->
        <?php if (isset($_SESSION['auth_user'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" 
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <?= htmlspecialchars($_SESSION['auth_user']['user_name']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                    <li>
                        <form action="/logout.php" method="POST">
                            <button type="submit" name="logout_btn" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="../../../moningsrental/login.php">Login</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>



