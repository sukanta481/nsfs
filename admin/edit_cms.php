<script type="text/javascript">
	function reload(form){
		var val = form.category.options[form.category.options.selectedIndex].value;
		self.location = 'cmspage.php?type=edit_cms&lp=adp&pgid='+val;
	}
</script>
<script>
$(document).ready(function(){
	var counter = parseInt($("#hiddencount").val());
	$("#addButton").click(function () {
		var newTextBoxDiv = $(document.createElement('div'))
	 	.attr("id", 'TextBoxDiv' + counter);
		$.ajax({
			type: "POST",
			url: "ajax_add_field.php",
			dataType: 'html',
			data: "counter="+counter,
			success: function(html){
				newTextBoxDiv.after().html(html);
				newTextBoxDiv.appendTo("#TextBoxesGroup");
				counter++;
		  		$("#hiddencount").val(counter);
			},error: function(){
			},complete: function(){
			}
		});
	});
});
</script>
<div class="x_panel">
    <div class="x_title">
      <h2>Edit Content </h2>
      <div class="clearfix"></div>
    </div>
    <div class="x_content">
       <form id="edit_home_form" action="" method="post" name="edit_home_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
          	<div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" >Select Page  <span class="required">*</span></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
				 <select name="category" id="category" onChange="reload(this.form);" required class="form-control col-md-7 col-xs-12">
					  <option value="">Select Page</option>
					  <?php
						$category = 'SELECT * FROM '.TABLE_PREFIX.'page where id in(1,2,3,5,6,7,8) order by page asc';
						$category_query = g_db_query($category);
						while ($result = g_db_fetch_array($category_query)){
						?>
					  <option value="<?php print $result['id']; ?>" <?php print $result['id'] == $_REQUEST['pgid'] ? 'selected' : '' ; ?>><?php print $result['page']; ?></option>
					  <?php } ?>
				
				 </select>
              </div>
            </div>
		  	<?php 
				$pageSql = "select * from tbl_pagecontent where id='".$_REQUEST['pgid']."'";
				$pageSqlExe = mysqli_query($conn,$pageSql);
				$pageRes = mysqli_fetch_array($pageSqlExe);
				$page_id = $pageRes['id'];
			?>                  	
      	    <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Title  <span class="required">*</span></label>
		        <div class="col-md-6 col-sm-6 col-xs-12">
		 			<input type="text" name="meta_title" id="meta_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($pageRes['meta_title']));?>" required="required" />
		        </div>
        	</div>   
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Keyword  <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="meta_keyword" id="meta_keyword" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($pageRes['meta_keyword']));?>" required="required" />
                </div>
            </div>
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Description</label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<textarea name="meta_desc" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($pageRes['meta_desc']));?></textarea>
              	</div>
            </div>
                  
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Heading  <span class="required">*</span></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="heading" id="heading" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($pageRes['heading']));?>" required="required" />
              	</div>
            </div>
            <!-- <div class="item form-group">
            	<label class="control-label col-md-3 col-sm-3 col-xs-12" >Short Description</label>
            	<div class="col-md-6 col-sm-6 col-xs-12"> 
            		<textarea name="short_desc" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($pageRes['short_desc']));?></textarea>
            	</div>
            </div>  -->
              <?php if($_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7)
             { 
             ?>
            <?php if($pageRes['page_image']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				<img src="post_img/<?php print $pageRes['page_image'];?>" height="100" width="100" />
 			  </div>
            </div>
            <?php }?>
            <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Banner Image</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_image" id="page_image" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div>
            <?php }?>
            
             <!-- <?php if($pageRes['page_image_mobile']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				<img src="post_img/<?php print $pageRes['page_image_mobile'];?>" height="100" width="100" />
 			  </div>
            </div>
            <?php }?>
            <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Banner Image Mobile</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
               	
					<input type="file" name="page_image_mobile" id="page_image_mobile" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div> -->
            <!-- <?php if($pageRes['page_webp']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				<img src="post_img/<?php print $pageRes['page_webp'];?>" height="100" width="100" />
 			  </div>
            </div>
            <?php }?> -->
            <!-- <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Banner Image Webp</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_webp" id="page_webp" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div> -->
             <?php if($_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7)
             { 
             ?>
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" > Banner Text</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="txtarea-wysiwyg">
					<?php
						$banner_heading= stripslashes(html_entity_decode($pageRes['banner_heading']));
						$oFCKeditor = new FCKeditor('banner_heading') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($banner_heading);
						$oFCKeditor->Config['EnterMode'] = 'br';
						$oFCKeditor->Create() ;
					?>
					</div>
                </div>
            </div>
            <?php
            }
            ?>
             <?php if($_REQUEST['pgid']==1){ ?>
            <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Banner Link</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="banner_link" id="banner_link" class="form-control col-md-7 col-xs-12" value="<?= stripslashes(html_entity_decode($pageRes['banner_link'])); ?>"   />
                </div>
            </div>
             <?php }?>
             <?php if($_REQUEST['pgid']==1){ ?>
            <!-- <?php if($pageRes['page_video']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				
 				<video loop="" style="" muted="true" playsinline="" autoplay="" data-wf-ignore="true" data-autoplay id="myVideo1" width="100">
			
			<source src="post_img/<?php print $pageRes['page_video'];?>" data-wf-ignore="true"/>
				</video>
 			  </div>
            </div>
            <?php }?> -->
            <!-- <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Video</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_video" id="page_video" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div> -->
            <!-- <?php if($pageRes['page_video_webm']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				
 				<video loop="" style="" muted="true" playsinline="" autoplay="" data-wf-ignore="true" data-autoplay id="myVideo2" width="100">
			
			<source src="post_img/<?php print $pageRes['page_video_webm'];?>" data-wf-ignore="true"/>
				</video>
 			  </div>
            </div>
            <?php }?> -->
            <!-- <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Video webm</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_video_webm" id="page_video_webm" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div> -->
             <?php }?>
             
             
             <?php if($_REQUEST['pgid']==1 || $_REQUEST['pgid']==2 || $_REQUEST['pgid']==3){ ?>     
              <?php if($pageRes['page_details_image']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				<img src="post_img/<?php print $pageRes['page_details_image'];?>" height="100" width="100" />
 			  </div>
            </div>
            <?php }?>
            <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Details Image</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_details_image" id="page_details_image" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div>
            <?php
			 }
            ?>
           
           <!-- <?php if($pageRes['page_details_image_mobile']!=''){ ?>
            <div class="item form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              <div class="col-md-6 col-sm-6 col-xs-12">
 				<img src="post_img/<?php print $pageRes['page_details_image_mobile'];?>" height="100" width="100" />
 			  </div>
            </div>
            <?php }?> -->
            <!-- <div class="item form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Details Image2</label>
               <div class="col-md-6 col-sm-6 col-xs-12">
					<input type="file" name="page_details_image_mobile" id="page_details_image_mobile" class="form-control col-md-7 col-xs-12" value=""   />
               </div>
            </div>  -->
            
            
            
            <div class="item form-group">
                 <label class="control-label col-md-3 col-sm-3 col-xs-12" > Page Details</label>
                 <div class="col-md-6 col-sm-6 col-xs-12">
                     <div class="txtarea-wysiwyg">
					 <?php
						$pageSql = "select * from tbl_pagecontent where id='".$_REQUEST['pgid']."'";
						$pageSqlExe = mysqli_query($conn,$pageSql);
						$pageRes = mysqli_fetch_array($pageSqlExe);
						$page_desc = stripslashes(html_entity_decode($pageRes['content']));
						$oFCKeditor = new FCKeditor('page_desc') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($page_desc);
						$oFCKeditor->Create() ;
					?>
					</div>
                </div>
            </div>
 <?php if($_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=3 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>           
             <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Page Link</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="page_link" id="page_link" class="form-control col-md-7 col-xs-12" value="<?= stripslashes(html_entity_decode($pageRes['page_link'])); ?>"   />
                </div>
            </div>
<?php }?>           
			<?php if($_REQUEST['pgid']==1 || $_REQUEST['pgid']==3 || $_REQUEST['pgid']==5 || $_REQUEST['pgid']==8){ ?>
   			<div class="item form-group">
		          <label class="control-label col-md-3 col-sm-3 col-xs-12" > Additional Content1</label>
		          <div class="col-md-6 col-sm-6 col-xs-12">
		              <div class="txtarea-wysiwyg">
					  <?php
						$add_cont1 = stripslashes(html_entity_decode($pageRes['add_cont1']));
						$oFCKeditor = new FCKeditor('add_cont1') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($add_cont1);
						$oFCKeditor->Create() ;
					  ?>
					</div>
			     </div>
			</div>
			<?php 
			}
			?>
  			<?php if($_REQUEST['pgid']!=2  && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
    		<div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" > Additional Content2</label>
				  <div class="col-md-6 col-sm-6 col-xs-12">
					<div class="txtarea-wysiwyg">
					<?php
						$add_cont2 = stripslashes(html_entity_decode($pageRes['add_cont2']));
						$oFCKeditor = new FCKeditor('add_cont2') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($add_cont2);
						$oFCKeditor->Create() ;
					?>
					</div>
			     </div>
			</div>
			<?php 
			}
			?>
			<?php if($_REQUEST['pgid']!=2  && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
  			<div class="item form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" > Additional Content3</label>
                 	<div class="col-md-6 col-sm-6 col-xs-12">
						<div class="txtarea-wysiwyg">
						<?php
							$add_cont3 = stripslashes(html_entity_decode($pageRes['add_cont3']));
							$oFCKeditor = new FCKeditor('add_cont3') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($add_cont3);
							$oFCKeditor->Create() ;
						?>
						</div>
 					</div>
  			</div>
  			<?php 
			}
			?>
  			<?php if($_REQUEST['pgid']!=1 && $_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
  			<div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" > Additional Content4</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                  	<div class="txtarea-wysiwyg">
					<?php
						$add_cont4 = stripslashes(html_entity_decode($pageRes['add_cont4']));
						$oFCKeditor = new FCKeditor('add_cont4') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($add_cont4);
						$oFCKeditor->Create() ;
					?>
					</div>
			     </div>
			</div>
<?php 
			}
			?>
			<!-- <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" > Additional Content5</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                  	<div class="txtarea-wysiwyg">
					<?php
						$add_cont5 = stripslashes(html_entity_decode($pageRes['add_cont5']));
						$oFCKeditor = new FCKeditor('add_cont5') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($add_cont5);
						$oFCKeditor->Create() ;
					?>
					</div>
			     </div>
			</div> -->  
			
		<?php if($_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
            <?php if($pageRes['feature_image']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image'];?>" height="100" width="100" />
 				</div>
            </div>
            <?php }?>
           
          	<div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image" id="feature_image" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div>
           
            
           <?php if($_REQUEST['pgid']==1){ ?>
            <!-- <?php if($pageRes['feature_image_webp']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image_webp'];?>" height="100" width="100" />
 				</div>
            </div>
            <?php }?> -->
           
          	<!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image 2</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image_webp" id="feature_image_webp" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div> -->
           <?php } ?>
          <?php } ?>   
            
           
           <?php if($_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7  && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
            
            <div class="item form-group">
		          <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Text</label>
		          <div class="col-md-6 col-sm-6 col-xs-12">
		              <div class="txtarea-wysiwyg">
					  <?php
						$feature_text = stripslashes(html_entity_decode($pageRes['feature_text']));
						$oFCKeditor = new FCKeditor('feature_text') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($feature_text);
						$oFCKeditor->Create() ;
					  ?>
					</div>
			     </div>
			</div>
			<?php if($_REQUEST['pgid']==1 || $_REQUEST['pgid']==8){ ?>
			 <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Link</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="feature_link" id="feature_link" class="form-control col-md-7 col-xs-12" value="<?= stripslashes(html_entity_decode($pageRes['feature_link'])); ?>"   />
                </div>
            </div>
            <?php } ?>
			<?php } ?>
			
         
      <?php if($_REQUEST['pgid']!=1 && $_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12)
          { 
          ?>   
            
            <?php if($pageRes['feature_image1']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image1'];?>" height="100" width="100" />
 				</div>
            </div>
           <?php }?> 
           
          	<div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image1</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image1" id="feature_image1" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div>
         <?php
		  }
         ?>  
            
            <!-- <?php if($_REQUEST['pgid']==1){ ?>
            <?php if($pageRes['feature_image_webp1']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image_webp1'];?>" height="100" width="100" />
 				</div>
            </div>
            <?php }?>
           
          	<div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image Mobile1</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image_webp1" id="feature_image_webp1" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div>
            <?php } ?> -->
          <?php if($_REQUEST['pgid']!=1 && $_REQUEST['pgid']!=2  && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12)
          { 
          ?>      
            <div class="item form-group">
		          <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Text1</label>
		          <div class="col-md-6 col-sm-6 col-xs-12">
		              <div class="txtarea-wysiwyg">
					  <?php
						$feature_text1 = stripslashes(html_entity_decode($pageRes['feature_text1']));
						$oFCKeditor = new FCKeditor('feature_text1') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($feature_text1);
						$oFCKeditor->Create() ;
					  ?>
					</div>
			     </div>
			</div>
			<?php
		  	}
			?>
			<!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Link1</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="feature_link1" id="feature_link1" class="form-control col-md-7 col-xs-12" value="<?= stripslashes(html_entity_decode($pageRes['feature_link1'])); ?>"   />
                </div>
            </div> -->
			
			<!-- <?php if($pageRes['feature_image2']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image2'];?>" height="100" width="100" />
 				</div>
            </div>
            <?php }?> -->
           
          	<!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image2</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image2" id="feature_image2" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div> -->
          
            
            <!-- <?php if($_REQUEST['pgid']==1){ ?>
            <?php if($pageRes['feature_image_webp2']!=''){ ?>
            <div class="item form-group">
              	<label class="control-label col-md-3 col-sm-3 col-xs-12" ></label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<img src="post_img/<?php print $pageRes['feature_image_webp2'];?>" height="100" width="100" />
 				</div>
            </div>
            <?php }?>
           
          	<div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Image Mobile2</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="file" name="feature_image_webp2" id="feature_image_webp2" class="form-control col-md-7 col-xs-12" value=""   />
                </div>
            </div>
            <?php } ?> -->
            
            
            <!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Link2</label>
          		<div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="feature_link2" id="feature_link2" class="form-control col-md-7 col-xs-12" value="<?= stripslashes(html_entity_decode($pageRes['feature_link2'])); ?>"   />
                </div>
            </div> -->
            
            
            
            <!-- <div class="item form-group">
		          <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Text2</label>
		          <div class="col-md-6 col-sm-6 col-xs-12">
		              <div class="txtarea-wysiwyg">
					  <?php
						$feature_text2 = stripslashes(html_entity_decode($pageRes['feature_text2']));
						$oFCKeditor = new FCKeditor('feature_text2') ;
						$oFCKeditor->BasePath	= 'fckeditor/';
						//$oFCKeditor->ToolbarSet = 'Basic';
						$oFCKeditor->Width 		= '660px';
						$oFCKeditor->Height 	= '500px';
						$oFCKeditor->Value		= htmlspecialchars_decode($feature_text2);
						$oFCKeditor->Create() ;
					  ?>
					</div>
			     </div>
			</div> -->
		
			
   			  	
                         
                <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
				           <input type="hidden" name="pgid" value="<?php echo $_REQUEST['pgid'];?>">
				           <input type="submit" name="page_content" value="Submit" onclick="return validate_form('edit_home_form');" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>
                </div>
             </div>