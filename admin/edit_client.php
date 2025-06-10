<?php
$client_id = $_REQUEST['client_id'];
$sl = 'SELECT * FROM  tbl_client WHERE client_id = "'.$client_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listclient()
	{
		location.href = "client.php?type=list_client&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Consignee Company </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_client_form" action="" method="post" name="edit_client_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Title <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="client_title" value="<?php echo stripcslashes(html_entity_decode($rc['client_title']));?>" required="required" id="client_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Address <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="client_address" value="<?php echo stripcslashes(html_entity_decode($rc['client_address']));?>"  id="client_address" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Phone <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="client_phone" value="<?php echo stripcslashes(html_entity_decode($rc['client_phone']));?>"  id="client_phone" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['client_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="client_image" id="client_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="client_link" value="<?php echo stripcslashes(html_entity_decode($rc['client_link']));?>" required="required" id="client_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Description <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <textarea name="client_short_desc"  required="required" class="form-control col-md-7 col-xs-12"  /><?php echo $rc['client_short_desc'];?></textarea>
                     
                       
 
                      </div>
                    </div> -->
                              
            
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                  <input type="hidden" name="client_id" value="<?php print $rc['client_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_client" value="Update" onclick="return validate_form('edit_client_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_client_cancel" value="Cancel" onclick="listclient();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>