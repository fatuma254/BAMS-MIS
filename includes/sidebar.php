<div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item navbar-brand-mini-wrapper">
              <a class="nav-link navbar-brand brand-logo-mini" href="index.html"><img src="../assets/images/bams.png" width="40px" style="margin-top: 30px;" alt="logo" /></a>
            </li>
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="profile-image">
                  <img class="img-xs rounded-circle" src="../assets/images/profile.webp" alt="profile image">
                  <div class="dot-indicator bg-success"></div>
                </div>
                <div class="text-wrapper">
                  <p class="profile-name"><?php echo htmlspecialchars($userInfo['full_name']); ?></p>
                  <p class="designation"><?php echo htmlspecialchars($userInfo['user_type']); ?></p>
                </div>
                <div class="icon-container">
                  <i class="icon-bubbles"></i>
                  <div class="dot-indicator bg-danger"></div>
                </div>
              </a>
            </li>
            
            <?php if($userInfo['user_type'] == 'admin'){ echo'
            <li class="nav-item nav-category">
              <span class="nav-link">Dashboard</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">
                <span class="menu-title">Dashboard</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>
            <li class="nav-item nav-category"><span class="nav-link">MODULES</span></li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <span class="menu-title">User Management</span>
                <i class="icon-layers menu-icon"></i>
              </a>
              <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="users.php">All Users</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
                <span class="menu-title">Financial Tracking</span>
                <i class="icon-grid menu-icon"></i>
              </a>
              <div class="collapse" id="icons">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="finance.php">Finance</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#forms" aria-expanded="false" aria-controls="forms">
                <span class="menu-title">Talent Profiles</span>
                <i class="icon-book-open menu-icon"></i>
              </a>
              <div class="collapse" id="forms">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="manage_talents.php">Talents</a></li>
                  <li class="nav-item"> <a class="nav-link" href="manage_bookings.php">Bookings</a></li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <span class="menu-title">Task Scheduling</span>
                <i class="icon-chart menu-icon"></i>
              </a>
              <div class="collapse" id="charts">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="tasks.php">Tasks</a></li>
                </ul>
              </div>
            </li>
';} 
          elseif($userInfo['user_type'] == 'professional'){ echo'
            <li class="nav-item nav-category">
              <span class="nav-link">Home</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">
                <span class="menu-title">Home</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>

              <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                    <span class="menu-title">Talents</span>
                    <i class="icon-chart menu-icon"></i>
                  </a>
                  <div class="collapse" id="charts">
                    <ul class="nav flex-column sub-menu">
                      <li class="nav-item"> <a class="nav-link" href="view_talents.php">Explore Talents</a></li>
                      <li class="nav-item"> <a class="nav-link" href="add_talent.php">Apply for Telent Profile</a></li>
                    </ul>
                  </div>
                </li>
                <li class="nav-item">
                  <a class="nav-link"  href="../professional/profile.php" aria-expanded="false" aria-controls="charts">
                    <span class="menu-title">My Profile</span>
                    <i class="icon-user menu-icon"></i>
                  </a>
                </li>
';}

 else{ echo'
                 <li class="nav-item nav-category">
              <span class="nav-link">Home</span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">
                <span class="menu-title">Home</span>
                <i class="icon-screen-desktop menu-icon"></i>
              </a>
            </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                    <span class="menu-title">Talents</span>
                    <i class="icon-chart menu-icon"></i>
                  </a>
                  <div class="collapse" id="charts">
                    <ul class="nav flex-column sub-menu">
                      <li class="nav-item"> <a class="nav-link" href="view_talents.php">Explore Talents</a></li>
                      <li class="nav-item"> <a class="nav-link" href="add_talent.php">Apply for Telent Profile</a></li>
                    </ul>
                  </div>
                </li>
                <li class="nav-item">
                  <a class="nav-link"  href="../client/profile.php" aria-expanded="false" aria-controls="charts">
                    <span class="menu-title">My Profile</span>
                    <i class="icon-user menu-icon"></i>
                  </a>
                </li>

            ';} ?>

          </ul>
        </nav>
        <!-- partial -->
        <div class="main-panel">
