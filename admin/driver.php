<?php require 'top_header.php'; ?>
<style>
@media (max-width:768px) {
    .page-title h3 { font-size: 1.2rem; }
    .x_panel { padding: 10px !important; }
    .x_title h2 { font-size: 1.1rem; }
    table { font-size: 0.9rem; }
    table th, table td { padding: 8px 4px !important; }
    .btn { font-size: 0.9rem; padding: 4px 8px; }
    .form-control { font-size: 1rem; }
    .form-group label { font-size: 1rem; }
}

@media (max-width:576px) {
    .page-title h3 { font-size: 1.05rem; }
    .x_title h2 { font-size: 1rem; }
    table { font-size: 0.85rem; }
    table th, table td { padding: 6px 3px !important; }
    .btn { font-size: 0.85rem; padding: 4px 6px; min-width: 60px; }
    .form-control { font-size: 0.95rem; padding: 8px; }
    .form-group label { font-size: 0.95rem; }
}
</style>
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
		<?php if(isset($drivermsg) && !empty($drivermsg)): ?>
					<div class="" style="margin:2px;padding:3px;">
					<span style="margin-left:30px;"><?php echo $drivermsg;?></span>
					</div>
		<?php endif;?>
      <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
         	<?php 
						if(isset($type) && ($type=='list_driver')) {
							require 'list_driver.php';
						}elseif(isset($type) && ($type=='add_driver')){
							require 'add_driver.php';
						}elseif(isset($type) && ($type=='edit_driver')){
							require 'edit_driver.php';
						}else{
							//Do Nothing......................
						} 
			?>
              
            </div>
      </div>
     </div> 
<?php require 'footer.php';?>