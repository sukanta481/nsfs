<?php
$amenities_id = $_REQUEST['amenities_id'];
$sl = 'SELECT * FROM  tbl_amenities WHERE amenities_id = "'.$amenities_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listamenities()
	{
		location.href = "amenities.php?type=list_amenities&lp=cu";
	}
</script>
<script>
	function change_type(qry)
	{
		$.ajax({
		type: "POST",
		url: "ajax_change_type.php",
		dataType: 'html',
		data:"q="+qry,
		success: function(html){
		$("#sub_type_id").html(html);
		},error: function(){
		},complete: function(){
		}
		});
	}
</script>
<div class="x_panel">
	<div class="x_title">
	  <h2>Edit amenities </h2>
	  
	  <div class="clearfix"></div>
	</div>
	<div class="x_content">

	  <form id="edit_amenities_form" action="" method="post" name="edit_amenities_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
	 
		<div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Icon 
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
			<img src="post_img/<?php print $rc['amenities_image'];?>" height="100" width="100" />
			<input type="file" name="amenities_image" id="amenities_image" class="form-control col-md-7 col-xs-12" value=""   />
		  </div>
		</div>	
		<!-- <div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Icon2 
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
			<img src="post_img/<?php print $rc['amenities_image2'];?>" height="100" width="100" />
			<input type="file" name="amenities_image2" id="amenities_image2" class="form-control col-md-7 col-xs-12" value=""   />
		  </div>
		</div> -->			 
		<div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Text
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
		
		 <input type="text" name="amenities_text" value="<?php echo stripcslashes(html_entity_decode($rc['amenities_text']));?>" required="required" id="amenities_text" class="form-control col-md-7 col-xs-12"  />
		  </div>
		</div>
		<!-- <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Featured Amenities <span class="required">*</span>
          </label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="radio" name="featured_amenities" id="featured_amenities" class="flat" value="1" required="required" <?php if( $rc['featured_amenities']==1){ echo "checked";}?>/>Yes
            <input type="radio" name="featured_amenities" id="featured_amenities" class="flat" value="0" required="required" <?php if( $rc['featured_amenities']==0){ echo "checked";}?>/>No
          </div>
        </div> -->   
		<div class="ln_solid"></div>
		<div class="form-group">
		  <div class="col-md-6 col-md-offset-3">
			<input type="hidden" name="amenities_id" value="<?php print $rc['amenities_id']; ?>" />
			<input type="submit" name="edit_amenities" value="Update" onclick="return validate_form('edit_amenities_form');" class="btn btn-success btn-submit">  	
			<input type="button" name="edit_amenities_cancel" value="Cancel" onclick="listamenities();" class="btn btn-success btn-submit">
		  </div>
		</div>
	  </form>

	</div>
 </div>