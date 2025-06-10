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
		<?php if(isset($why_choosemsg) && !empty($why_choosemsg)): ?>
					<div class="" style="margin:2px;padding:3px;">
					<span style="margin-left:30px;"><?php echo $why_choosemsg;?></span>
					</div>
		<?php endif;?>
      <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
         	<?php 
						if(isset($type) && ($type=='list_why_choose')) {
							require 'list_why_choose.php';
						}elseif(isset($type) && ($type=='add_why_choose')){
							require 'add_why_choose.php';
						}elseif(isset($type) && ($type=='edit_why_choose')){
							require 'edit_why_choose.php';
						}else{
							//Do Nothing......................
						} 
			?>
              
            </div>
      </div>
     </div> 
<?php require 'footer.php';?>