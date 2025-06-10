<?php require 'top_header.php'; ?>
<body class="nav-md">

  <div class="container body">

    <div class="main_container">
<?php require 'left_panel.php';?>
 <?php require 'header_banner.php';?>   
 <?php
$get_admin_email_sql="select * from tbl_administrator where id='".$_SESSION['admin']['id']."'";
$get_admin_email_rs=mysqli_query($conn,$get_admin_email_sql);
$get_admin_email_row=mysqli_fetch_array($get_admin_email_rs);
?>	   
      <!-- page content -->
      <div class="right_col" role="main">
      	<div class="">
      	<div class="page-title">
            <div class="title_left">
              <h3>
                   Teacher Panel
                </h3>
            </div>

            
          </div>
          <div class="clearfix"></div>
		<?php if(isset($passmessage) && !empty($passmessage)): ?>
					<div class="" style="margin:2px;padding:3px;">
					<span style="margin-left:30px;"><?php echo $passmessage;?></span>
					</div>
					<?php endif;?>
      <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
         	    
   			 <div class="x_panel">
                <div class="x_title">
                  <h2>Change Your Password</h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                
                  	
                  		<form id="changepassword" name="changepassword" action="<?php $PHP_SELF?>" method="post"  class="form-horizontal form-label-left" novalidate >
                  	
                   <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="admin_email">Enter Admin Email<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="admin_email" id="admin_email" value="<?php echo $get_admin_email_row['admin_email'];?>" class="form-control col-md-7 col-xs-12" required="required" />
                     
                      </div>
                    </div>
                    
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">Enter New Password
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="password" name="new_password1" id="new_password1" value="" class="form-control col-md-7 col-xs-12"  />
                        
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="new_password2">Confirm New Password
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="password" name="new_password2" id="new_password2" value="" class="form-control col-md-7 col-xs-12" />
                        
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="old_password">Enter Old Password
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="password" name="old_password" id="old_password" value="" class="form-control col-md-7 col-xs-12" />
                        
                      </div>
                    </div>
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
						<input type="hidden" name="pwdsubmit" value="Submit">
						<input type="submit" value="submit" onclick="return validate_form('changepassword');" class="btn btn-success btn-submit">
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>
              
            </div>
      </div>
     </div> 
<?php require 'footer.php';?>