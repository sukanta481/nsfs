<?php $sl = 'SELECT * FROM  tbl_widget WHERE widget_id = 1';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function del_feature_val(fid)
	{
		var ca = confirm("Are you sure to delete");
		if(ca==true)
		{
		location.href='widget.php?type=edit_widget&lp=ac&fid='+fid+'&actftr=delftr';
		//return true;
		}
		else
		{
		return false;
		}
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Widget </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_widget_form" action="" method="post" name="edit_widget_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                	<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Shipping amount<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="widget_charge" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($rc['widget_charge']));?></textarea>
                      </div>
                    </div> -->
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Copyright<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="widget_copyright" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($rc['widget_copyright']));?></textarea>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Design and developed by<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	 
                      	   <textarea name="design_and_developed_by" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($rc['design_and_developed_by']));?></textarea>
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >USD Rate  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="number" name="usd_rate" id="usd_rate" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['usd_rate']));?>" required="required" />
                      </div>
                    </div> -->
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >site feature
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<?php
$oFCKeditor = new FCKeditor('widget_site_feature') ;
$oFCKeditor->BasePath	= 'fckeditor/';
$oFCKeditor->Width 		= '550px';
$oFCKeditor->Height 	= '300px';
$oFCKeditor->Value		= htmlspecialchars_decode($rc['widget_site_feature']);
$oFCKeditor->Create() ;
?>
                      	
                      
                      </div>
                    </div>  -->
                    	
                        <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Footer Content 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">


<textarea name="widget_site_info" class="form-control col-md-7 col-xs-12"><?= $rc['widget_site_info'];?></textarea>
                      </div>
                    </div>
                     <!-- <?php 
                    if($rc['widget_newsletter_left_sec_image']!=''){
                    ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 						<img src="post_img/<?php print $rc['widget_newsletter_left_sec_image'];?>" height="100" width="100" />
 
                      </div>
                    </div>
                    <?php }?> -->
                    
                      <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Contractor Account Banner Image	
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="widget_newsletter_left_sec_image" id="widget_newsletter_left_sec_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    
                    <!-- <?php 
                    if($rc['widget_sale_weekend_image']!=''){
                    ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 						<img src="post_img/<?php print $rc['widget_sale_weekend_image'];?>" height="100" width="100" />
 
                      </div>
                    </div>
                    <?php }?> -->
                    
                      <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Customer Account Banner Image	
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="widget_sale_weekend_image" id="widget_sale_weekend_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                     
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Site Slogan 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="widget_site_slogan"><?php echo stripslashes(html_entity_decode($rc['widget_site_slogan']));?></textarea>
 
                      </div>
                    </div> -->
                      
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Newsletter Text<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="widget_newsletter"><?php echo stripslashes(html_entity_decode($rc['widget_newsletter']));?></textarea>
                      </div>
                    </div>  -->
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Whats New Text<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="widget_what_new"><?php echo stripslashes(html_entity_decode($rc['widget_what_new']));?></textarea>
                      </div>
                    </div> -->
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Contact Link  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="widget_link" id="widget_link" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['widget_link']));?>" required="required" />
                      </div>
                    </div>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Admin Mail  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="reply_email" id="reply_email" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['reply_email']));?>" required="required" />
                      </div>
                    </div>
                            
                     <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="edit_widget" value="Update" onclick="return validate_form('edit_widget_form');" class="btn btn-success btn-submit">
                      	
                      	
                   
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>
             <script>
function del_attr_val(attr)
{

		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		$.ajax({
type: "GET",
url: "ajax_del_site_val.php",
dataType: 'html',
data: "q="+attr,
success: function(html){
alert(html);
location.reload(); 
},error: function(){
},complete: function(){
}
});
		}
		else
		{
		return false;
		}	

	}
</script>