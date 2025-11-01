<?php 

$social_Sql = 'SELECT * from tbl_social where social_id=1';
$social_Query = mysqli_query($conn,$social_Sql);
$social_Res = mysqli_fetch_array($social_Query);

?>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Social</h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_social_form" action="" method="post" name="edit_social_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                   <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_facebook">Facebook<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_facebook" id="social_facebook" value="<?php echo $social_Res['social_facebook'];?>" class="form-control col-md-7 col-xs-12" placeholder="Facebook Link" required="required" />
                      </div>
                    </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">Twitter <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_twitter" id="social_twitter" value="<?php echo $social_Res['social_twitter'];?>" class="form-control col-md-7 col-xs-12" placeholder="Twitter Link" required="required" />
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">You Tube <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_youtube" id="social_youtube" value="<?php echo $social_Res['social_youtube'];?>" class="form-control col-md-7 col-xs-12" placeholder="Youtube Link" required="required" />
                      </div>
                    </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">Linkedin <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_linkedin" id="social_linkedin" value="<?php echo $social_Res['social_linkedin'];?>" class="form-control col-md-7 col-xs-12" placeholder="Linkedin Link" required="required" />
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_instagram">Instagram <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_instagram" id="social_instagram" value="<?php echo $social_Res['social_instagram'];?>" class="form-control col-md-7 col-xs-12" placeholder="Instagram Link" required="required" />
                      </div>
                    </div> 
                    
                   <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">Pinterest<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_pinterest" id="social_pinterest" value="<?php echo $social_Res['social_pinterest'];?>" class="form-control col-md-7 col-xs-12" placeholder="Pinterest Link" required="required" />
                      </div>
                    </div>  -->
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_email">Whatsapp<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_email" id="social_email" value="<?php echo $social_Res['social_email'];?>" class="form-control col-md-7 col-xs-12" placeholder="Whatsapp Link" required="required" />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="social_twitter">You Tube <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="social_youtube" id="social_youtube" value="<?php echo $social_Res['social_youtube'];?>" class="form-control col-md-7 col-xs-12" placeholder="Youtube Link" required="required" />
                      </div>
                    </div> -->
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
						<input name="social_id" type="hidden" value="<?php print $social_Res['social_id']; ?>" />
						<input name="save_social" type="submit" value="submit" onclick="return validate_form('edit_social_form');" class="btn btn-success btn-submit" />
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>