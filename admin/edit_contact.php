<?php $sl = 'SELECT * FROM  tbl_contact WHERE contact_id = 1';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>


<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Contact </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_contact_form" action="" method="post" name="edit_contact_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                      <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Banner Image <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input name="contact_banner" type="file">
                        <img src="post_img/<?php echo stripslashes(html_entity_decode($rc['contact_banner']));?>" width="100%" height="200" />
                      </div>
                    </div> -->
                 <!--    <div class="item form-group">

                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Office Timing <span class="required">*</span>

                      </label>

                      <div class="col-md-6 col-sm-6 col-xs-12">

                        <textarea name="contact_office_timing" required="required" rows="5" cols="400"><?php echo stripslashes(html_entity_decode($rc['contact_office_timing']));?></textarea>

                      </div>

                    </div>   -->
                   <!--  <div class="item form-group">

                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Caption <span class="required">*</span>

                      </label>

                      <div class="col-md-6 col-sm-6 col-xs-12">

                        <textarea name="contact_banner_caption" required="required" rows="5" cols="400"><?php echo stripslashes(html_entity_decode($rc['contact_banner_caption']));?></textarea>

                      </div>

                    </div>  -->
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="contact_phone" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone']));?></textarea>
                      </div>
                    </div>   
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number Inner <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea name="contact_phone_inner" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone_inner']));?></textarea>
                      </div>
                    </div>                                                           
                    
                    <div class="item form-group">                      
                    	<label class="control-label col-md-3 col-sm-3 col-xs-12" >Invoice Mobile Numbers <span class="required">*</span>                      </label>                      
                    	<div class="col-md-6 col-sm-6 col-xs-12"> 
                    		<textarea name="contact_phone2"  rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone2']));?></textarea>                      
                    	</div>                    
                    </div>   
                     <!-- <div class="item form-group">                      
                    	<label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number2 Inner <span class="required">*</span>                      </label>                      
                    	<div class="col-md-6 col-sm-6 col-xs-12"> 
                    		<textarea name="contact_phone2_inner"  rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone2_inner']));?></textarea>                      
                    	</div>                    
                    </div>    -->                                                       
                  <!-- <div class="item form-group">                      
<label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number3 <span class="required">*</span></label>
<div class="col-md-6 col-sm-6 col-xs-12"> 
	<textarea name="contact_phone3" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone3']));?></textarea>
</div>
</div> -->
<!-- <div class="item form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" >Phone Number4 <span class="required">*</span>
</label>
<div class="col-md-6 col-sm-6 col-xs-12"> 
<textarea name="contact_phone4" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_phone4']));?></textarea>
</div>
</div> -->
                     
                      
                    
                   <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Email <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
 <textarea name="contact_email" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_email']));?></textarea>
                      </div>
                    </div>
                    
                    
                   <div class="item form-group">                  
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Invoice Email <span class="required">*</span>      
                    </label>                  
       <div class="col-md-6 col-sm-6 col-xs-12">  <textarea name="contact_email2" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_email2']));?></textarea>      
       	                </div>                   
       	                 </div>   
       	                 
       	                 <!-- <div class="item form-group">             
       	              <label class="control-label col-md-3 col-sm-3 col-xs-12" >Email3 <span class="required">*</span>      
       	              	 </label>                  
       	     <div class="col-md-6 col-sm-6 col-xs-12">  <textarea name="contact_email3" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_email3']));?></textarea>      
       	     	                </div>   
       	     	                   </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Address <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea name="contact_address"  rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_address']));?></textarea>
 
                      </div>
                    </div>
                    
                    <!--  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Address2 <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea name="contact_address2" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_address2']));?></textarea>
 
                      </div>
                    </div> -->
                   <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Address3 <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea name="contact_address3" required="required" rows="5" cols="100"><?php echo stripslashes(html_entity_decode($rc['contact_address3']));?></textarea>
 
                      </div>
                    </div> -->
                   <!--  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Contact Inforamtion <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea name="contact_info" rows="10" cols="200" required="required"><?php echo stripslashes(html_entity_decode($rc['contact_info']));?></textarea>
 
                      </div>
                    </div> -->
                   <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Map 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea name="contact_map" rows="10" cols="200" ><?php echo stripslashes(html_entity_decode($rc['contact_map']));?></textarea>
 
                      </div>
                    </div> -->
                 <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="edit_contact" value="Update" onclick="return validate_form('edit_contact_form');" class="btn btn-success btn-submit">
                      	
                      	
                   
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>