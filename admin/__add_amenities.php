<script type="text/javascript">
	function listamenities()
	{
		location.href = "amenities.php?type=list_amenities&lp=cu";
	}
</script>
<div class="x_panel">
	<div class="x_title">
	  <h2>Add New amenities </h2>
	  
	  <div class="clearfix"></div>
	</div>
	<div class="x_content">

	  <form id="add_amenities_form" action="" method="post" name="add_amenities_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
		
		<div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Image 
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
				<input type="file" name="amenities_image" id="amenities_image" value="" class="form-control col-md-7 col-xs-12"  />
		  </div>
		</div>
		<!-- <div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Icon2 
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
			<input type="file" name="amenities_image" id="amenities_image" class="form-control col-md-7 col-xs-12" value=""   />
		  </div>
		</div> -->
		 
		<div class="item form-group">
		  <label class="control-label col-md-3 col-sm-3 col-xs-12" >amenities Text 
		  </label>
		  <div class="col-md-6 col-sm-6 col-xs-12">
		 <input type="text" name="amenities_text" value="" required="required" id="amenities_text" class="form-control col-md-7 col-xs-12"  />
		  </div>
		</div>
		<!-- <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Featured Amenities <span class="required">*</span>
          </label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="radio" name="featured_amenities" id="featured_amenities" class="flat" value="1" required="required"/>Yes
            <input type="radio" name="featured_amenities" id="featured_amenities" class="flat" value="0" required="required"/>No
          </div>
        </div> -->
		<div class="ln_solid"></div>
		<div class="form-group">
		  <div class="col-md-6 col-md-offset-3">
			
			<input type="submit" name="save_amenities" value="Save" onclick="return validate_form('add_amenities_form');" class="btn btn-success btn-submit">
			<input type="button" name="add_amenities_cancel" value="Cancel" onclick="listamenities();" class="btn btn-success btn-submit">
	 
			
		  </div>
		</div>
	  </form>

	</div>
  </div>