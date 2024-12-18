<?php
// session_header.php
function initSessionHeader() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
    
    // If email is not in session, fetch it
    if (!isset($_SESSION['user_email'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();
        $_SESSION['user_email'] = $userData['email'] ?? '';
    }
    
    // If full name is not in session, fetch it
    if (!isset($_SESSION['full_name'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch();
        if ($profile) {
            $_SESSION['full_name'] = $profile['first_name'] . ' ' . $profile['last_name'];
        }
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_email' => $_SESSION['user_email'] ?? 'No email set',
        'full_name' => $_SESSION['full_name'] ?? 'User',
        'user_type' => $_SESSION['user_type'] ?? 'client'
    ];
}

// Get user info at the start of the file
$userInfo = initSessionHeader();

// Navigation Component
function renderNavigation($activeMenu = '') {
    global $userInfo; 
}
    ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BAMS Beauty</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="../assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../assets/images/icon.png" />
  </head>
  <body>
    <div class="container-scroller">
      
      <!-- partial:partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
          <a class="navbar-brand brand-logo" href="index.html">
            <img src="../assets/images/bams.png" alt="logo" class="logo-dark" />
            <img src="../assets/images/bams.png" alt="logo-light" class="logo-light">
          </a>
          <a class="navbar-brand brand-logo-mini" href="index.html"><img src="../assets/images/bams.png" alt="logo" /></a>
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
          </button>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center">
          <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Welcome <?php echo htmlspecialchars($userInfo['full_name']); ?> to BAMS Beauty!</h5>
          <ul class="navbar-nav navbar-nav-right">
            
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
              <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle ms-2" src="../assets/images/profile.webp"  alt="Profile image"> <span class="font-weight-normal"> <?php echo htmlspecialchars($userInfo['full_name']); ?> </span></a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="../assets/images/profile.webp" width="80px" alt="Profile image">
                  <p class="mb-1 mt-3"><?php echo htmlspecialchars($userInfo['full_name']); ?></p>
                  <p class="font-weight-light text-muted mb-0"><?php echo htmlspecialchars($userInfo['user_email']); ?></p>
                </div>
                <a class="dropdown-item" href="../client/profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a>
                <a class="dropdown-item" href="../includes/logout.php"><i class="dropdown-item-icon icon-power text-primary"></i>Sign Out</a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
          </button>
        </div>
      </nav>
