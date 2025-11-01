<script type="text/javascript">
	function listservice_category()
	{
		location.href = "service_category.php?type=list_service_category&lp=cu";
	}
</script>
<script>
	function add_more_data()
	{
		var Ajax=$.ajax({
		type: "POST",
		url: "ajax_add_more_data.php",
		dataType: 'html',
		data:"q=1",
		success: function(html){
		$("#data_section").append(html);
		//alert(html);
		},error: function(){
		},complete: function(){
		}
		});
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Add New service category </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_service_category_form" action="" method="post" name="add_service_category_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                      
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service category Title<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="service_category_title" required="required" id="service_category_title" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_category_image" id="service_category_image" value="" class="form-control col-md-7 col-xs-12"  required="required"/>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Sort Details</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<textarea name="service_category_srt_desc" id="service_category_srt_desc" class="form-control col-md-7 col-xs-12"/></textarea>
                      </div>
                    </div>
                    
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_service_category" value="Save" onclick="return validate_form('add_service_category_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_service_category_cancel" value="Cancel" onclick="listservice_category();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>