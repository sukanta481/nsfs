<?php 
require 'top_header.php'; 

// Get the type parameter from URL
$type = $_GET['type'] ?? '';
$lp = $_GET['lp'] ?? 'ac';
?>
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
					require 'list_register_new.php';
				}elseif(isset($type) && ($type=='add_register')){
					require 'add_register.php';
				}elseif(isset($type) && ($type=='view_register')){
					require 'view_register.php';
				}elseif(isset($type) && ($type=='edit_register')){
					require 'edit_register.php';
				}elseif(isset($type) && ($type=='delete_register')){
					$id = intval($_GET['id'] ?? 0);
					if($id > 0) {
						$delete_sql = "DELETE FROM docket_details WHERE docket_id = $id";
						if(mysqli_query($conn, $delete_sql)) {
							echo "<script>alert('Docket deleted successfully!'); window.location.href='register.php?type=list_register';</script>";
						} else {
							echo "<script>alert('Error deleting docket: " . mysqli_error($conn) . "'); window.location.href='register.php?type=list_register';</script>";
						}
					} else {
						echo "<script>alert('Invalid docket ID!'); window.location.href='register.php?type=list_register';</script>";
					}
					exit;
				}
				// ---- NEW: TEST CRUD PAGE ----
				elseif(isset($type) && ($type=='crud_register')){
					require 'register_crud.php'; // <--- New AJAX CRUD file
				}
				// ---- END NEW ----
				else{
					//Do Nothing......................
				} 
			?>

              
            </div>
      </div>
     </div> 
<?php require 'footer.php';?>