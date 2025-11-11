<style>
.cms-page-container {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 100px);
}

.page-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
}

.page-card-header {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    padding: 20px 25px;
    color: #fff;
}

.page-card-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-card-header h2 i {
    font-size: 1.4rem;
}

.page-card-body {
    padding: 30px;
}

.form-group-modern {
    margin-bottom: 25px;
}

.form-group-modern label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 14px;
}

.form-group-modern label .required {
    color: #dc3545;
    margin-left: 3px;
}

.form-control-cms {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    background: #fff;
}

.form-control-cms:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
}

textarea.form-control-cms {
    resize: vertical;
    min-height: 80px;
}

.btn-submit-modern {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: #fff;
    border: none;
    padding: 14px 40px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-submit-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39,174,96,0.3);
}

.btn-submit-modern i {
    font-size: 18px;
}

.image-preview {
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.image-preview img {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-divider {
    height: 1px;
    background: #e9ecef;
    margin: 30px 0;
}

.editor-container {
    margin-bottom: 25px;
}

.editor-container label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 14px;
}

/* Hide old FCKeditor toolbars styling issues */
.txtarea-wysiwyg {
    width: 100%;
}

.txtarea-wysiwyg iframe {
    border: 2px solid #e1e8ed;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .page-card-body {
        padding: 20px;
    }

    .form-control-cms {
        padding: 10px 12px;
    }

    .txtarea-wysiwyg {
        overflow-x: auto;
    }
}
</style>

<!-- Include TinyMCE 6 from CDN (No API Key needed for self-hosted domains) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
// Wait for page to load before initializing TinyMCE
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE for all textareas with class 'tinymce-editor'
    tinymce.init({
        selector: '.tinymce-editor',
        height: 450,
        menubar: 'file edit view insert format tools table help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount', 'emoticons'
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | ' +
                 'alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist outdent indent | removeformat | ' +
                 'link image media table emoticons | code fullscreen preview | help',
        toolbar_mode: 'sliding',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; padding: 15px; }',
        promotion: false,
        branding: false,
        resize: true,
        statusbar: true,
        elementpath: false,
        // Image upload settings
        images_upload_url: 'upload_image.php',
        automatic_uploads: true,
        images_reuse_filename: true,
        file_picker_types: 'image',
        // Content formatting
        paste_data_images: true,
        paste_as_text: false,
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',
        // Modern UI options
        skin: 'oxide',
        content_css: 'default'
    });
});
</script>

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
<div class="cms-page-container">
<div class="page-card">
    <div class="page-card-header">
      <h2><i class="fa fa-edit"></i> Edit Content</h2>
    </div>
    <div class="page-card-body">
       <form id="edit_home_form" action="" method="post" name="edit_home_form" novalidate enctype="multipart/form-data">
          	<div class="form-group-modern">
              <label>Select Page <span class="required">*</span></label>
				 <select name="category" id="category" onChange="reload(this.form);" required class="form-control-cms">
					  <option value="">Select Page</option>
					  <?php
						$category = 'SELECT * FROM '.TABLE_PREFIX.'page where id in(1,2,3,5,6,7,8) order by page asc';
						$category_query = g_db_query($category);
						while ($result = g_db_fetch_array($category_query)){
						?>
					  <option value="<?php print $result['id']; ?>" <?php print isset($_REQUEST['pgid']) && $result['id'] == $_REQUEST['pgid'] ? 'selected' : '' ; ?>><?php print $result['page']; ?></option>
					  <?php } ?>
				 </select>
            </div>
		  	<?php
		  		if(isset($_REQUEST['pgid']) && !empty($_REQUEST['pgid'])) {
					$pageSql = "select * from tbl_pagecontent where id='".$_REQUEST['pgid']."'";
					$pageSqlExe = mysqli_query($conn,$pageSql);
					$pageRes = mysqli_fetch_array($pageSqlExe);
					$page_id = $pageRes['id'];
				} else {
					$pageRes = array();
				}
			?>
			<?php if(isset($_REQUEST['pgid']) && !empty($_REQUEST['pgid'])) { ?>

			<div class="form-divider"></div>

      	    <div class="form-group-modern">
          		<label>Meta Title <span class="required">*</span></label>
		 		<input type="text" name="meta_title" id="meta_title" class="form-control-cms" value="<?php echo isset($pageRes['meta_title']) ? stripslashes(html_entity_decode($pageRes['meta_title'])) : '';?>" required="required" />
        	</div>
            <div class="form-group-modern">
                <label>Meta Keyword <span class="required">*</span></label>
 				<input type="text" name="meta_keyword" id="meta_keyword" class="form-control-cms" value="<?php echo isset($pageRes['meta_keyword']) ? stripslashes(html_entity_decode($pageRes['meta_keyword'])) : '';?>" required="required" />
            </div>
            <div class="form-group-modern">
                <label>Meta Description</label>
 				<textarea name="meta_desc" class="form-control-cms"><?php echo isset($pageRes['meta_desc']) ? stripslashes(html_entity_decode($pageRes['meta_desc'])) : '';?></textarea>
            </div>

            <div class="form-group-modern">
              	<label>Page Heading <span class="required">*</span></label>
 				<input type="text" name="heading" id="heading" class="form-control-cms" value="<?php echo isset($pageRes['heading']) ? stripslashes(html_entity_decode($pageRes['heading'])) : '';?>" required="required" />
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
            <?php if(isset($pageRes['page_image']) && $pageRes['page_image']!=''){ ?>
            <div class="image-preview">
 				<img src="post_img/<?php print $pageRes['page_image'];?>" height="120" width="120" />
            </div>
            <?php }?>
            <div class="form-group-modern">
               <label>Page Banner Image</label>
				<input type="file" name="page_image" id="page_image" class="form-control-cms" value="" />
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
            <div class="form-group-modern">
                <label>Banner Text</label>
				<textarea name="banner_heading" class="tinymce-editor"><?php echo isset($pageRes['banner_heading']) ? stripslashes(html_entity_decode($pageRes['banner_heading'])) : ''; ?></textarea>
            </div>
            <?php
            }
            ?>
             <?php if($_REQUEST['pgid']==1){ ?>
            <div class="form-group-modern">
          		<label>Banner Link</label>
 				<input type="text" name="banner_link" id="banner_link" class="form-control-cms" value="<?= isset($pageRes['banner_link']) ? stripslashes(html_entity_decode($pageRes['banner_link'])) : ''; ?>" />
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
              <?php if(isset($pageRes['page_details_image']) && $pageRes['page_details_image']!=''){ ?>
            <div class="image-preview">
 				<img src="post_img/<?php print $pageRes['page_details_image'];?>" height="120" width="120" />
            </div>
            <?php }?>
            <div class="form-group-modern">
               <label>Page Details Image</label>
					<input type="file" name="page_details_image" id="page_details_image" class="form-control-cms" value="" />
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
            
            
            
            <div class="form-group-modern">
                 <label>Page Details</label>
				 <textarea name="page_desc" class="tinymce-editor"><?php echo isset($pageRes['content']) ? stripslashes(html_entity_decode($pageRes['content'])) : ''; ?></textarea>
            </div>
 <?php if($_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=3 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
             <div class="form-group-modern">
          		<label>Page Link</label>
 				<input type="text" name="page_link" id="page_link" class="form-control-cms" value="<?= isset($pageRes['page_link']) ? stripslashes(html_entity_decode($pageRes['page_link'])) : ''; ?>" />
            </div>
<?php }?>           
			<?php if($_REQUEST['pgid']==1 || $_REQUEST['pgid']==3 || $_REQUEST['pgid']==5 || $_REQUEST['pgid']==8){ ?>
   			<div class="form-group-modern">
		          <label>Additional Content 1</label>
				  <textarea name="add_cont1" class="tinymce-editor"><?php echo isset($pageRes['add_cont1']) ? stripslashes(html_entity_decode($pageRes['add_cont1'])) : ''; ?></textarea>
			</div>
			<?php
			}
			?>
  			<?php if($_REQUEST['pgid']!=2  && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
    		<div class="form-group-modern">
                  <label>Additional Content 2</label>
				  <textarea name="add_cont2" class="tinymce-editor"><?php echo isset($pageRes['add_cont2']) ? stripslashes(html_entity_decode($pageRes['add_cont2'])) : ''; ?></textarea>
			</div>
			<?php
			}
			?>
			<?php if($_REQUEST['pgid']!=2  && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
  			<div class="form-group-modern">
					<label>Additional Content 3</label>
					<textarea name="add_cont3" class="tinymce-editor"><?php echo isset($pageRes['add_cont3']) ? stripslashes(html_entity_decode($pageRes['add_cont3'])) : ''; ?></textarea>
  			</div>
  			<?php
			}
			?>
  			<?php if($_REQUEST['pgid']!=1 && $_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12){ ?>
  			<div class="form-group-modern">
                  <label>Additional Content 4</label>
				  <textarea name="add_cont4" class="tinymce-editor"><?php echo isset($pageRes['add_cont4']) ? stripslashes(html_entity_decode($pageRes['add_cont4'])) : ''; ?></textarea>
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
            <?php if(isset($pageRes['feature_image']) && $pageRes['feature_image']!=''){ ?>
            <div class="image-preview">
 				<img src="post_img/<?php print $pageRes['feature_image'];?>" height="120" width="120" />
            </div>
            <?php }?>

          	<div class="form-group-modern">
          		<label>Feature Image</label>
 				<input type="file" name="feature_image" id="feature_image" class="form-control-cms" value="" />
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

            <div class="form-group-modern">
		          <label>Feature Text</label>
				  <textarea name="feature_text" class="tinymce-editor"><?php echo isset($pageRes['feature_text']) ? stripslashes(html_entity_decode($pageRes['feature_text'])) : ''; ?></textarea>
			</div>
			<?php if($_REQUEST['pgid']==1 || $_REQUEST['pgid']==8){ ?>
			 <div class="form-group-modern">
          		<label>Feature Link</label>
 				<input type="text" name="feature_link" id="feature_link" class="form-control-cms" value="<?= isset($pageRes['feature_link']) ? stripslashes(html_entity_decode($pageRes['feature_link'])) : ''; ?>" />
            </div>
            <?php } ?>
			<?php } ?>
			
         
      <?php if($_REQUEST['pgid']!=1 && $_REQUEST['pgid']!=2 && $_REQUEST['pgid']!=4 && $_REQUEST['pgid']!=5 && $_REQUEST['pgid']!=6 && $_REQUEST['pgid']!=7 && $_REQUEST['pgid']!=8 && $_REQUEST['pgid']!=9 && $_REQUEST['pgid']!=10 && $_REQUEST['pgid']!=11 && $_REQUEST['pgid']!=12)
          {
          ?>

            <?php if(isset($pageRes['feature_image1']) && $pageRes['feature_image1']!=''){ ?>
            <div class="image-preview">
 				<img src="post_img/<?php print $pageRes['feature_image1'];?>" height="120" width="120" />
            </div>
           <?php }?>

          	<div class="form-group-modern">
          		<label>Feature Image1</label>
 				<input type="file" name="feature_image1" id="feature_image1" class="form-control-cms" value="" />
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
            <div class="form-group-modern">
		          <label>Feature Text 1</label>
				  <textarea name="feature_text1" class="tinymce-editor"><?php echo isset($pageRes['feature_text1']) ? stripslashes(html_entity_decode($pageRes['feature_text1'])) : ''; ?></textarea>
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
		
			
   			  	
                         
			<div class="form-divider"></div>
                    <div class="form-group-modern" style="text-align: center;">
				           <input type="hidden" name="pgid" value="<?php echo isset($_REQUEST['pgid']) ? $_REQUEST['pgid'] : '';?>">
				           <button type="submit" name="page_content" onclick="return validate_form('edit_home_form');" class="btn-submit-modern">
							   <i class="fa fa-check"></i> Update Content
						   </button>
                    </div>
			<?php } ?>
                  </form>
                </div>
             </div>
</div>