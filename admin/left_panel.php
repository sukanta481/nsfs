<?php
// Get user permissions for menu visibility
if (file_exists('check_auth.php')) {
    require_once 'check_auth.php';
}
// Updated: Changed "Offices" to "Office" in Companies submenu
?>
<div class="modern-sidebar" id="modernSidebar">
  <div class="sidebar-header">
    <div class="sidebar-brand">
      <img src="images/logo.png" alt="NSFS Logo" class="brand-logo">
      <span class="brand-text">NSFS</span>
    </div>
    <button class="sidebar-close" id="sidebarClose">
      <i class="fa fa-times"></i>
    </button>
  </div>

  <div class="sidebar-menu">
    <ul class="menu-list">
      <!-- Dashboard -->
      <?php if (hasPermission('dashboard_view')): ?>
      <li class="menu-item">
        <a href="../admin/index.php" class="menu-link">
          <i class="fa fa-tachometer"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <?php endif; ?>

      <!-- Dockets Management -->
      <?php if (hasPermission('docket_view') || hasPermission('docket_create') || hasPermission('docket_status_update') || hasPermission('special_docket_create')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-file-text"></i>
          <span>Dockets</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <?php if (hasPermission('docket_create')): ?>
          <li><a href="add_trip_modern.php">Create New Trip</a></li>
          <?php endif; ?>
          <?php if (hasPermission('docket_create') || hasPermission('special_docket_create')): ?>
          <li><a href="add_special_docket.php"><i class="fas fa-star"></i> Special Docket</a></li>
          <?php endif; ?>
          <?php if (hasPermission('docket_view')): ?>
          <li><a href="register.php?type=list_register&lp=ac">All Dockets</a></li>
          <?php endif; ?>
          <?php if (hasPermission('trip_view')): ?>
          <li><a href="trip.php?type=list_trips">All Trips</a></li>
          <?php endif; ?>
          <?php if (hasPermission('docket_status_update')): ?>
          <li><a href="delivery_status.php">📦 Update Status</a></li>
          <?php endif; ?>
          <?php if (hasPermission('docket_view')): ?>
          <li><a href="barcode_scanner.php"><i class="fa fa-barcode"></i> Barcode Scanner</a></li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Manifest -->
      <?php if (hasPermission('manifest_view') || hasPermission('manifest_receive')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-clipboard"></i>
          <span>Manifest</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <?php if (hasPermission('manifest_view')): ?>
          <li><a href="manifest.php"><i class="fa fa-plus"></i> Create Manifest</a></li>
          <li><a href="register.php?type=list_manifest"><i class="fa fa-list"></i> All Manifests</a></li>
          <?php endif; ?>
          <?php if (hasPermission('manifest_receive')): ?>
          <li><a href="receive_manifest.php"><i class="fa fa-inbox"></i> Receive Manifest</a></li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Tracking Management -->
      <?php if (hasPermission('tracking_management') || hasPermission('tracking_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-map-marker"></i>
          <span>Tracking</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <?php if (hasPermission('tracking_management')): ?>
          <li><a href="tracking_management.php">📍 Tracking Dashboard</a></li>
          <?php endif; ?>
          <?php if (hasPermission('tracking_view')): ?>
          <li><a href="tracking_management.php">📦 All Shipments</a></li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Fleet Management -->
      <?php if (hasPermission('vehicle_view') || hasPermission('staff_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-truck"></i>
          <span>Fleet</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <?php if (hasPermission('vehicle_view')): ?>
          <li><a href="car_crud.php">Cars</a></li>
          <?php endif; ?>
          <?php if (hasPermission('staff_view')): ?>
          <li><a href="staff_crud.php">Staff</a></li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Companies -->
      <?php if (hasPermission('client_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-building"></i>
          <span>Companies</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <li><a href="consignors.php">All Consignors</a></li>
          <li><a href="offices.php">Office</a></li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Database Management -->
      <?php if (isSuperAdmin()): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-database"></i>
          <span>Database Manager</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <li><a href="database/schema_sync.php"><i class="fa fa-table"></i> Schema Sync</a></li>
          <li><a href="database/selective_data_sync.php"><i class="fa fa-filter"></i> Selective Data Sync</a></li>
          <li><a href="database/database_sync.php"><i class="fa fa-archive"></i> Full Backup/Restore</a></li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Settings -->
      <?php if (hasPermission('settings_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-cog"></i>
          <span>Settings</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <li><a href="delay_reason_crud.php">Delay Reasons</a></li>
          <li><a href="contact_settings.php">Contact Settings</a></li>
          <li><a href="changepassword.php?lp=ad">Change Password</a></li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- User Management -->
      <?php if (hasPermission('user_view') || hasPermission('role_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-users-cog"></i>
          <span>User Management</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <?php if (hasPermission('user_view')): ?>
          <li><a href="users.php">All Users</a></li>
          <?php endif; ?>
          <?php if (hasPermission('user_create')): ?>
          <li><a href="add_user.php">Add New User</a></li>
          <?php endif; ?>
          <?php if (hasPermission('role_view') || hasPermission('role_manage')): ?>
          <li><a href="roles.php">Roles & Permissions</a></li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Website Management -->
      <?php if (hasPermission('settings_view')): ?>
      <li class="menu-item has-submenu">
        <a href="javascript:void(0)" class="menu-link">
          <i class="fa fa-globe"></i>
          <span>Website</span>
          <i class="fa fa-chevron-down submenu-arrow"></i>
        </a>
        <ul class="submenu">
          <li><a href="site_features_crud.php">Site Features</a></li>
          <li><a href="testimonials_crud.php">Testimonials</a></li>
          <li><a href="services_crud.php">Services</a></li>
          <li><a href="gallery_crud.php">Gallery</a></li>
          <li><a href="team_crud.php">Team</a></li>
          <li><a href="cmspage.php?type=edit_cms&lp=ord&select=managepage">CMS Pages</a></li>
          <li><a href="social_links_modern.php">Social Links</a></li>
        </ul>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

/* Modern Sidebar */
.modern-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 280px;
  height: 100vh;
  background: linear-gradient(180deg, #2c4a61 0%, #1e3a52 100%);
  color: #fff;
  z-index: 999;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(0,0,0,0.15);
  transition: transform 0.3s ease;
  overflow-y: auto;
  font-family: 'Inter', sans-serif;
}

.modern-sidebar::-webkit-scrollbar {
  width: 8px;
}

.modern-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 4px;
}

.modern-sidebar::-webkit-scrollbar-track {
  background: rgba(0,0,0,0.2);
}

.sidebar-header {
  padding: 25px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(0,0,0,0.25);
  min-height: 80px;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 15px;
  font-size: 1.7rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: 2px;
}

.brand-logo {
  width: 50px;
  height: 50px;
  object-fit: contain;
  filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
}

.sidebar-brand i {
  font-size: 2.2rem;
  color: #ffc107;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.brand-text {
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.sidebar-close {
  display: none;
  background: rgba(255,255,255,0.1);
  border: none;
  color: #fff;
  font-size: 1.8rem;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.2s;
}

.sidebar-close:hover {
  background: rgba(255,255,255,0.2);
  transform: rotate(90deg);
}

.sidebar-menu {
  flex: 1;
  padding: 25px 0;
  overflow-y: auto;
}

.menu-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-item {
  margin-bottom: 6px;
}

.menu-link {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  color: rgba(255,255,255,0.9);
  text-decoration: none;
  transition: all 0.3s;
  font-size: 1.1rem;
  font-weight: 600;
  position: relative;
  letter-spacing: 0.5px;
}

.menu-link:hover {
  background: rgba(255,255,255,0.12);
  color: #fff;
  padding-left: 28px;
  text-decoration: none;
}

.menu-link.active {
  background: rgba(255,193,7,0.2);
  color: #ffc107;
  border-left: 5px solid #ffc107;
  font-weight: 700;
}

.menu-link i {
  width: 28px;
  margin-right: 15px;
  font-size: 1.4rem;
  text-align: center;
}

.submenu-arrow {
  margin-left: auto;
  font-size: 1rem;
  transition: transform 0.3s;
  opacity: 0.8;
}

.menu-item.active .submenu-arrow {
  transform: rotate(180deg);
}

.submenu {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
  background: rgba(0,0,0,0.25);
}

.menu-item.active .submenu {
  max-height: 1000px;
}

.submenu li {
  margin: 0;
}

.submenu a {
  display: block;
  padding: 14px 20px 14px 63px;
  color: rgba(255,255,255,0.75);
  text-decoration: none;
  font-size: 1.05rem;
  transition: all 0.25s;
  font-weight: 500;
  letter-spacing: 0.3px;
}

.submenu a:hover {
  background: rgba(255,255,255,0.08);
  color: #ffc107;
  padding-left: 68px;
  text-decoration: none;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255,255,255,0.15);
  background: rgba(0,0,0,0.25);
  margin-top: auto;
  display: none;
}

.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px 20px;
  background: rgba(231,76,60,0.25);
  color: #fff;
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.3s;
  font-weight: 700;
  font-size: 1.1rem;
  border: 2px solid rgba(231,76,60,0.4);
  letter-spacing: 0.5px;
}

.logout-btn:hover {
  background: #e74c3c;
  color: #fff;
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(231,76,60,0.4);
  text-decoration: none;
  border-color: #e74c3c;
}

.logout-btn i {
  margin-right: 12px;
  font-size: 1.3rem;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  z-index: 998;
  display: none;
  opacity: 0;
  transition: opacity 0.3s;
}

.sidebar-overlay.active {
  display: block;
  opacity: 1;
}

/* Hide old sidebar */
.left_col {
  display: none !important;
}

.col-md-3.left_col {
  display: none !important;
}

/* Adjust main content area */
.right_col {
  margin-left: 280px !important;
  transition: margin-left 0.3s ease;
  width: calc(100% - 280px) !important;
}

/* Header adjustment */
.top_nav {
  margin-left: 280px !important;
  width: calc(100% - 280px) !important;
}

/* Container adjustments */
.main_container {
  padding-left: 0 !important;
}

.container.body {
  padding: 0 !important;
}

/* Responsive Design */
@media (max-width: 992px) {
  .modern-sidebar {
    transform: translateX(-100%);
  }

  .modern-sidebar.active {
    transform: translateX(0);
  }

  .sidebar-close {
    display: block;
  }

  .right_col {
    margin-left: 0 !important;
    width: 100% !important;
  }

  .top_nav {
    margin-left: 0 !important;
    width: 100% !important;
  }
}

@media (max-width: 576px) {
  .modern-sidebar {
    width: 300px;
  }

  .menu-link {
    font-size: 1.05rem;
    padding: 15px 18px;
  }

  .submenu a {
    font-size: 1rem;
    padding: 13px 18px 13px 58px;
  }
}
</style>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('modernSidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const sidebarClose = document.getElementById('sidebarClose');
  const menuToggle = document.getElementById('menu_toggle');

  // Toggle sidebar on mobile
  if(menuToggle) {
    menuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
    });
  }

  // Close sidebar
  if(sidebarClose) {
    sidebarClose.addEventListener('click', function() {
      sidebar.classList.remove('active');
      sidebarOverlay.classList.remove('active');
    });
  }

  // Close on overlay click
  if(sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      sidebarOverlay.classList.remove('active');
    });
  }

  // Submenu toggle
  const menuItems = document.querySelectorAll('.has-submenu');
  menuItems.forEach(item => {
    const menuLink = item.querySelector('.menu-link');
    menuLink.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Close other submenus
      menuItems.forEach(otherItem => {
        if(otherItem !== item) {
          otherItem.classList.remove('active');
        }
      });
      
      // Toggle current submenu
      item.classList.toggle('active');
    });
  });

  // Set active menu based on current URL
  const currentUrl = window.location.href;
  const allLinks = document.querySelectorAll('.menu-link, .submenu a');
  allLinks.forEach(link => {
    if(link.href === currentUrl) {
      link.classList.add('active');
      // Open parent submenu if it's a submenu link
      const parentSubmenu = link.closest('.has-submenu');
      if(parentSubmenu) {
        parentSubmenu.classList.add('active');
      }
    }
  });
});
</script>