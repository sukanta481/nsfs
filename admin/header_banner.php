<!-- Modern Top Navigation Header -->
<div class="modern-top-header">
  <div class="header-left">
    <div class="nav toggle">
      <a id="menu_toggle"><i class="fa fa-bars"></i></a>
    </div>
    <div class="header-brand">
      <img src="images/logo.png" alt="NSFS Logo" class="header-logo">
      <h1 class="header-title">NSFS CMS</h1>
    </div>
  </div>
  
  <div class="header-right">
    <a href="users.php" class="header-action-btn btn-users">
      <i class="fa fa-users-cog"></i> Manage Users
    </a>
    <a href="add_trip_modern.php" class="header-action-btn btn-new-docket">
      <i class="fa fa-plus"></i> New Trip
    </a>
    <div class="header-dropdown">
      <a href="javascript:;" class="header-action-btn btn-admin dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-user-circle"></i> 
        <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'System Administrator'; ?>
        <span class="fa fa-angle-down"></span>
      </a>
      <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
        <li><a href="index.php"><i class="fa fa-home"></i> Dashboard</a></li>
        <li><a href="../index.php" target="_blank"><i class="fa fa-globe"></i> Visit Site</a></li>
        <li><a href="changepassword.php"><i class="fa fa-key"></i> Change Password</a></li>
        <li class="divider"></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Log Out</a></li>
      </ul>
    </div>
    <a href="logout.php" class="header-action-btn btn-logout">
      <i class="fa fa-sign-out"></i> Logout
    </a>
  </div>
</div>

<style>
/* Modern Header Styles */
.modern-top-header {
  background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
  padding: 18px 35px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  position: fixed;
  top: 0;
  left: 280px;
  right: 0;
  z-index: 1000;
  transition: left 0.3s ease;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav.toggle a {
  color: #fff;
  font-size: 1.8rem;
  padding: 10px;
  cursor: pointer;
  transition: all 0.3s;
}

.nav.toggle a:hover {
  color: #ffc107;
  transform: scale(1.1);
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-logo {
  width: 45px;
  height: 45px;
  object-fit: contain;
  background: rgba(255,255,255,0.2);
  padding: 8px;
  border-radius: 10px;
}

.header-truck-icon {
  font-size: 2rem;
  color: #fff;
  background: rgba(255,255,255,0.2);
  padding: 12px;
  border-radius: 10px;
}

.header-title {
  color: #fff;
  font-size: 1.8rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
  font-family: 'Inter', sans-serif;
}

.header-right {
  display: flex;
  gap: 12px;
  align-items: center;
}

.header-action-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 700;
  text-decoration: none !important;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.3px;
}

.btn-users {
  background: #ffc107;
  color: #000;
}

.btn-users:hover {
  background: #ffb300;
  color: #000;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255,193,7,0.4);
}

.btn-new-docket {
  background: #fff;
  color: #2b5876;
}

.btn-new-docket:hover {
  background: #f0f0f0;
  color: #2b5876;
  transform: translateY(-2px);
}

.btn-admin {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.3);
}

.btn-admin:hover {
  background: rgba(255,255,255,0.25);
  color: #fff;
}

.btn-logout {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.3);
}

.btn-logout:hover {
  background: rgba(255,255,255,0.1);
  color: #fff;
}

.header-dropdown {
  position: relative;
}

.dropdown-menu.dropdown-usermenu {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  border: none;
  margin-top: 10px;
  min-width: 220px;
}

.dropdown-menu.dropdown-usermenu li a {
  padding: 12px 20px;
  color: #2c3e50;
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.2s;
}

.dropdown-menu.dropdown-usermenu li a:hover {
  background: #f8f9fa;
  color: #2b5876;
  padding-left: 25px;
}

.dropdown-menu.dropdown-usermenu li a i {
  width: 18px;
  text-align: center;
}

.dropdown-menu.dropdown-usermenu .divider {
  margin: 5px 0;
  border-top: 1px solid #ecf0f1;
}

/* Hide old top_nav if it exists */
.top_nav {
  display: none !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .modern-top-header {
    padding: 15px 20px;
  }
  
  .header-title {
    font-size: 1.5rem;
  }
  
  .header-action-btn {
    padding: 8px 15px;
    font-size: 0.85rem;
  }
}

@media (max-width: 992px) {
  .header-right {
    gap: 8px;
  }
  
  .header-action-btn span {
    display: none;
  }
  
  .btn-users, .btn-new-docket {
    display: none;
  }
}

@media (max-width: 768px) {
  .modern-top-header {
    flex-wrap: wrap;
    gap: 15px;
    padding: 12px 15px;
  }
  
  .header-brand {
    gap: 10px;
  }
  
  .header-truck-icon {
    font-size: 1.5rem;
    padding: 8px;
  }
  
  .header-title {
    font-size: 1.2rem;
  }
  
  .header-right {
    width: 100%;
    justify-content: flex-end;
  }
  
  .header-action-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .header-action-btn i {
    margin: 0;
  }
}

@media (max-width: 576px) {
  .header-truck-icon {
    display: none;
  }
  
  .header-title {
    font-size: 1rem;
  }
  
  .btn-logout {
    padding: 6px 10px;
  }
}

/* Body padding for sticky header */
.right_col {
  padding-top: 80px !important;
}

/* Responsive header positioning */
@media (max-width: 992px) {
  .modern-top-header {
    left: 0;
  }
}
</style>
<!-- /Modern Top Navigation Header -->