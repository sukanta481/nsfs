<?php
$company_id = $_REQUEST['company_id'];
$sl = 'SELECT * FROM  tbl_company WHERE company_id = "'.$company_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listcompany()
	{
		location.href = "company.php?type=list_company&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Consignor Company </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_company_form" action="" method="post" name="edit_company_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Title <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_title" value="<?php echo stripcslashes(html_entity_decode($rc['company_title']));?>" required="required" id="company_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Address <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_address" value="<?php echo stripcslashes(html_entity_decode($rc['company_address']));?>" required="required" id="company_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Phone <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_phone" value="<?php echo stripcslashes(html_entity_decode($rc['company_phone']));?>" required="required" id="company_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['company_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="company_image" id="company_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_link" value="<?php echo stripcslashes(html_entity_decode($rc['company_link']));?>" required="required" id="company_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Description <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <textarea name="company_short_desc"  required="required" class="form-control col-md-7 col-xs-12"  /><?php echo $rc['company_short_desc'];?></textarea>
                     
                       
 
                      </div>
                    </div> -->
                              
            
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                  <input type="hidden" name="company_id" value="<?php print $rc['company_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_company" value="Update" onclick="return validate_form('edit_company_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_company_cancel" value="Cancel" onclick="listcompany();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>