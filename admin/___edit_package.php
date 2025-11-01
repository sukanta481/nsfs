<?php
$package_id = $_REQUEST['package_id'];
$sl = 'SELECT * FROM  tbl_package WHERE package_id = "'.$package_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listpackage()
	{
		location.href = "package.php?type=list_package&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Package </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_package_form" action="" method="post" name="edit_package_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
		                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Tour Type 
		                      </label>
		                     <?php
		                     $pkg_sql="select * from tbl_package_type";
							 $pkg_qry=mysqli_query($conn,$pkg_sql);
							 
		                     
		                     ?>
		                     
		                      <div class="col-md-6 col-sm-6 col-xs-12">
		 	
		    					<select id="package_type_id" name="package_type_id" class="form-control col-md-7 col-xs-12">
		    						<option value="">--select--</option>
		    						<?php
		    						while ($pkg_exe=mysqli_fetch_assoc($pkg_qry)) {
									?>
									<option value=<?php echo $pkg_exe['package_type_id'];?> <?php echo $rc['package_type_id']==$pkg_exe['package_type_id']?"selected":"";?>><?php echo $pkg_exe['package_type_name'];?></option>
									<?php	
									}
		    						?>
		    					</select>
		
		 						
		                      </div>
                    </div>
                       
                     <div class="item form-group">
		                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Client Name 
		                      </label>
		                     <?php
		                     $client_sql="select * from  tbl_client order by client_title asc";
							 $client_qry=mysqli_query($conn,$client_sql);
							 
		                     
		                     ?>
		                     
		                      <div class="col-md-6 col-sm-6 col-xs-12">
		 	
		    					<select id="client_id" name="client_id" class="form-control col-md-7 col-xs-12">
		    						<option value="">--select--</option>
		    						<?php
		    						while ($client_exe=mysqli_fetch_assoc($client_qry)) {
									?>
									<option value=<?php echo $client_exe['client_id'];?> <?php echo $rc['client_id']==$client_exe['client_id']?"selected":"";?>><?php echo $client_exe['client_title'];?></option>
									<?php	
									}
		    						?>
		    					</select>
		
		 						
		                      </div>
                    </div>  
                       
                       
                       
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="images/<?php print $rc['package_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="package_image" id="package_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    
                    
                     
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="images/<?php print $rc['package_banner_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Banner Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="package_banner_image" id="package_banner_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                              
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Banner Text <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_banner_text" id="package_banner_text" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['package_banner_text']));?>" required="required" />
                      </div>
                    </div>  -->       
                                 
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Heading  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_heading" id="package_heading" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_heading']));?>" />
                      </div>
                    </div> -->
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Name  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
  <input type="text" name="package_name" id="package_name" class="form-control col-md-7 col-xs-12" value="<?php echo $rc['package_name'];?>" required="required"/>
                      </div>
                    </div>
                      
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Departure Location  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_departure_location" id="package_departure_location" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_departure_location']));?>" required="required"/>
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Location Map  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
 <textarea name="package_location_map" id="package_location_map" class="form-control col-md-7 col-xs-12"><?php echo stripcslashes(htmlspecialchars_decode($rc['package_location_map']));?></textarea>
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Duration  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	
No. of Nights:<input type="text" name="package_duration_night" id="package_duration_night" class="" value="<?php echo $rc['no_of_nights']; ?>" />
No. of days:<input type="text" name="package_duration_days" id="package_duration_days" class="" value="<?php echo $rc['no_of_days']; ?>" />
                     
                      </div>
                    </div> -->
                    
                      <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Base Price <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_starting_price" id="package_starting_price" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_starting_price']));?>" required="required"/>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount Price 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_discount_price" id="package_discount_price" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_discount_price']));?>" />
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Addon Price <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="package_addon_price" id="package_addon_price" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_addon_price']));?>" required="required"/>
                      </div>
                    </div>
                    <?php
                    $get_package_amenities_rel_sql="select * from  tbl_package_amenities_rel where package_id='".$rc['package_id']."'";
					$get_package_amenities_rel_rs=mysqli_query($conn,$get_package_amenities_rel_sql);
					$amenities_id=array();
					while($get_package_amenities_rel_row=mysqli_fetch_array($get_package_amenities_rel_rs))
					{
						$amenities_id[]=$get_package_amenities_rel_row['amenities_id'];
					}
                    ?>
                      <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Select Amenities <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
						<?php
						$get_amenities_sql="select * from  tbl_amenities order by amenities_text asc";
						$get_amenities_rs=mysqli_query($conn, $get_amenities_sql);
						while($get_amenities_row= mysqli_fetch_array($get_amenities_rs))
						{
						?>
						<li>
						 <input type="checkbox" name="amenities_id[]" id="amenities_id"   value="<?= $get_amenities_row['amenities_id'];?>" <?php if(in_array($get_amenities_row['amenities_id'],$amenities_id)){ echo "checked"; }?>/> <?= $get_amenities_row['amenities_text'];?></li>
						<?php	
						}
						?>
                      </div>
                    </div>
                      <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Status  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
 <input type="radio" name="package_rating" id="package_rating1"  <?php echo $rc['package_rating']==1?'checked':'';?> value="1"/>Best
 <input type="radio" name="package_rating" id="package_rating2"  <?php echo $rc['package_rating']==0?'checked':'';?> value="0"/>Normal
 
                      </div>
                    </div>  -->                                           	 
                    <!-- <div class="item form-group">                      
                    	<label class="control-label col-md-3 col-sm-3 col-xs-12" >Starting Date  <span class="required">*</span> </label>                      
                    	<div class="col-md-6 col-sm-6 col-xs-12"> 					
                    		<input type="text" name="package_start_date" id="datepicker2" class="form-control col-md-7 col-xs-12" value="<?php echo stripcslashes(htmlspecialchars_decode($rc['package_start_date']));?>" required="required" />                      
                    </div>          
                    </div>   -->                        
                    
                    
             <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="testimonial_date" id="datepicker" value="<?php echo stripslashes(html_entity_decode($rc['testimonial_date']));?>" required="required" id="testimonial_date" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    
                       <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Package Short Description
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
		<?php
			$oFCKeditor = new FCKeditor('package_short_desc') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= htmlspecialchars_decode($rc['package_short_desc']);
			$oFCKeditor->Create() ;
		?>
		</div>
                </div>
                    </div>   
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Package Overview
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
		<?php
			$oFCKeditor = new FCKeditor('package_long_desc') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= htmlspecialchars_decode($rc['package_long_desc']);
			$oFCKeditor->Create() ;
		?>
		</div>
                </div>
                    </div>                                                
                    <div class="item form-group">                      
                    	<label class="control-label col-md-3 col-sm-3 col-xs-12" > Package On Arrival</label>                      
                    	<div class="col-md-6 col-sm-6 col-xs-12"> 					
                    		           
                    		<?php
			$oFCKeditor = new FCKeditor('package_on_arrival') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= htmlspecialchars_decode($rc['package_on_arrival']);
			$oFCKeditor->Create() ;
		?>           
                    		</div>          
                    		</div>                             
                    		<div class="item form-group">                      
                    			<label class="control-label col-md-3 col-sm-3 col-xs-12" > Package On Return</label>                      
                    			<div class="col-md-6 col-sm-6 col-xs-12"> 					
                    				
                    				<?php
			$oFCKeditor = new FCKeditor('package_on_return') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= htmlspecialchars_decode($rc['package_on_return']);
			$oFCKeditor->Create() ;
		?>                        
                    				</div>          
                    		</div>  
                    		
                    	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Covered  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
 <input type="radio" name="covered" id="covered"  <?php echo $rc['covered']==1?'checked':'';?> value="1"/>Yes
 <input type="radio" name="covered" id="covered"  <?php echo $rc['covered']==0?'checked':'';?> value="0"/>No
 
                      </div>
                    </div>	                                                                                                                                                                            
                   
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Recommended  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
 <input type="radio" name="popular" id="popular"  <?php echo $rc['popular']==1?'checked':'';?> value="1"/>Yes
 <input type="radio" name="popular" id="popular"  <?php echo $rc['popular']==0?'checked':'';?> value="0"/>No
 
                      </div>
                    </div>     
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                  <input type="hidden" name="package_id" value="<?php print $rc['package_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_package" value="Update" onclick="return validate_form('edit_package_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="edit_package_cancel" value="Cancel" onclick="listpackage();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>