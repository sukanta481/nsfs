<div class="col-md-3 left_col">
        <div class="left_col scroll-view">

          <div class="navbar nav_title" style="border: 0;">
            <a href="../admin/index.php" class="site_title"> <span>North Super Fast Service</span></a>
          </div>
          <div class="clearfix"></div>
          <!-- sidebar menu -->
          <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

            <div class="menu_section">
              <ul class="nav side-menu">
                <li><a style="cursor: pointer;"><i class="fa fa-home"></i> Admin Settings <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu" style="display: none">
                  	<li><a href="../admin/index.php">Dashboard</a>
                    </li>
                    <li><a href="changepassword.php?lp=ad&<?php echo session_name().'='.session_id();?>">Change Password</a>
                    </li>
                    <li><a href="logout.php?lp=ad&<?php echo session_name().'='.session_id();?>">Logout</a>
                    </li>
                     <li><a href="cmspage.php?type=edit_cms&lp=ord&<?php echo session_name().'='.session_id();?>&select=managepage">Manage CMS Page</a></li>
                     <li><a href="contacts.php?type=edit_contact&contact_id=1&lp=ac&<?php echo session_name().'='.session_id();?>">Manage Contact</a></li>
                       <li><a href="widget.php?type=edit_widget&widget_id=1&lp=ac&<?php echo session_name().'='.session_id();?>">Manage Widget</a></li>
                      <li><a href="social.php?type=edit_social&lp=ac&<?php echo session_name().'='.session_id();?>">Manage Social Link</a></li>
                  </ul>
                </li>
                
                
                <li><a style="cursor: pointer;"><i class="fa fa-edit"></i> Manage  Front End <span class="fa fa-chevron-down"></span></a>
			 
			  <ul class="nav child_menu" style="display: none">
<li><a href="site_feature.php?type=list_site_feature&lp=ac&<?php echo session_name().'='.session_id();?>">All  Site Feature</a></li>
<li><a href="why_choose.php?type=list_why_choose&lp=ac&<?php echo session_name().'='.session_id();?>">All  Why Choose</a></li>
<li><a href="why_choose.php?type=add_why_choose&lp=ac&<?php echo session_name().'='.session_id();?>">Add  Why Choose</a></li>
<li><a href="testimonial.php?type=list_testimonial&lp=ac&<?php echo session_name().'='.session_id();?>">All Testimonial</a></li>
					<li><a href="testimonial.php?type=add_testimonial&lp=ac&<?php echo session_name().'='.session_id();?>">Add Testimonial</a></li>
					<li><a href="team.php?type=list_team&lp=ac&<?php echo session_name().'='.session_id();?>">All Team</a></li>
					<li><a href="team.php?type=add_team&lp=ac&<?php echo session_name().'='.session_id();?>">Add Team</a></li>
					<li><a href="service_category.php?type=list_service_category&lp=ac&<?php echo session_name().'='.session_id();?>">All Services Category</a></li>
					<li><a href="service_category.php?type=add_service_category&lp=ac&<?php echo session_name().'='.session_id();?>">Add Services Category</a></li>
					<li><a href="service.php?type=list_service&lp=ac&<?php echo session_name().'='.session_id();?>">All Services</a></li>
					<li><a href="service.php?type=add_service&lp=ac&<?php echo session_name().'='.session_id();?>">Add Services</a></li>
					<li><a href="gallery_category.php?type=list_gallery_category&lp=ac&<?php echo session_name().'='.session_id();?>">All Gallery Category</a></li>
					 <li><a href="gallery.php?type=list_gallery&lp=ac&<?php echo session_name().'='.session_id();?>">All Gallery</a></li>
                    <li><a href="gallery.php?type=add_gallery&lp=ac&<?php echo session_name().'='.session_id();?>">Add Gallery</a></li>
  </ul>
  
   
				  
				</li> 
				
				

  
  <li><a style="cursor: pointer;"><i class="fa fa-edit"></i> Manage Back End <span class="fa fa-chevron-down"></span></a>
          <ul class="nav child_menu" style="display: none">
          	
          	<li><a href="car.php?type=list_car&lp=ac&<?php echo session_name().'='.session_id();?>">All Car</a></li>
            <li><a href="car.php?type=add_car&lp=ac&<?php echo session_name().'='.session_id();?>">Add Car</a></li>
              	<li><a href="driver.php?type=list_driver&lp=ac&<?php echo session_name().'='.session_id();?>">All Driver</a></li>
            <li><a href="driver.php?type=add_driver&lp=ac&<?php echo session_name().'='.session_id();?>">Add Driver</a></li>
            <li><a href="helper.php?type=list_helper&lp=ac&<?php echo session_name().'='.session_id();?>">All Helper</a></li>
            <li><a href="helper.php?type=add_helper&lp=ac&<?php echo session_name().'='.session_id();?>">Add Helper</a></li>
            	<li><a href="company.php?type=list_company&lp=ac&<?php echo session_name().'='.session_id();?>">All Consignor Company</a></li>
                    <li><a href="company.php?type=add_company&lp=ac&<?php echo session_name().'='.session_id();?>">Add Consignor Company</a></li>
                    <!-- <li><a href="client.php?type=list_client&lp=ac&<?php echo session_name().'='.session_id();?>">All Consignee Company</a></li>
                    <li><a href="client.php?type=add_client&lp=ac&<?php echo session_name().'='.session_id();?>">Add Consignee Company</a></li> -->
                    	<li><a href="delay_reason.php?type=list_delay_reason&lp=ac&<?php echo session_name().'='.session_id();?>">All Delay Reason</a></li>
            <li><a href="delay_reason.php?type=add_delay_reason&lp=ac&<?php echo session_name().'='.session_id();?>">Add Delay Reason</a></li>
            <li><a href="register.php?type=list_register&lp=ac&<?php echo session_name().'='.session_id();?>">All Register</a></li>
                    <li><a href="register.php?type=add_register&lp=ac&<?php echo session_name().'='.session_id();?>">Add Register</a></li>
                    	<li><a href="trip.php?type=list_trip&lp=ac&<?php echo session_name().'='.session_id();?>">All Tip</a></li>
            
          </ul>
          
            </div>
          </div>
          <!-- /sidebar menu -->
       </div>
      </div>