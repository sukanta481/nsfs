<?php require 'top_header.php'; ?>
<body class="nav-md">

  <div class="container body">

    <div class="main_container">
<?php require 'left_panel.php';?>
 <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main">
      	<div class="">
      	<div class="page-title">
            <div class="title_left">
              <h3>
                   Admin Panel
                </h3>
            </div>

            
          </div>
          <div class="clearfix"></div>
		<?php if(isset($registermsg) && !empty($registermsg)): ?>
					<div class="" style="margin:2px;padding:3px;">
					<span style="margin-left:30px;"><?php echo $registermsg;?></span>
					</div>
		<?php endif;?>
      <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
         	<?php 
						if(isset($type) && ($type=='list_register')) {
							require 'list_register.php';
						}elseif(isset($type) && ($type=='add_register')){
							require 'add_register.php';
						}elseif(isset($type) && ($type=='edit_register')){
							require 'edit_register.php';
						}else{
							//Do Nothing......................
						} 
			?>
              
            </div>
      </div>
     </div> 
<?php require 'footer.php';?>