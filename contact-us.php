<?php include("include/header.php"); ?>
<?php
$get_page_sql = "select * from tbl_pagecontent where id='2'";
$get_page_qry = mysqli_query($conn,$get_page_sql);
$get_page_res = mysqli_fetch_assoc($get_page_qry);
?>




<div class="banIn">
	<img src="<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['page_image'];?>" alt="" />
	<div class="banCon">
		<div class="container">
			<div class="banLeft">
<?= $get_page_res['banner_heading'];?>
			</div>
		</div>
	</div>
</div>

<section class="contaSec tophead">
	<div class="container">
		<ul class="brdCm">
			<li><a href="<?= SITE_URL; ?>">Home</a></li>
			<li><span><?= $get_page_res['heading'];?></span></li>
		</ul>
		<div class="row">
			<div class="col-md-7">
<?= $get_page_res['content'];?>		
				<form action="" id="frmcontact" method="post" onsubmit="submitform();">
					<div class="row">
						<div class="col-md-6 contaFrm">
							<label>Your Name</label>
							<input type="text" placeholder="Jhon Doe" name="name" id="name" pattern="[A-Za-z ]+" required title="Charecter and space only"/>
						</div>
						<div class="col-md-6 contaFrm">
							<label>Your Email</label>
							<input type="email" placeholder="example@example.com" name="email"  id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"/>
						</div>
						<div class="col-md-6 contaFrm">
							<label>Your Phone Number</label>
							<input type="tel" placeholder="0123456789"  name="phone" required  pattern="[0-9]{10}" title="10 digit number only"/>
						</div>
						<div class="col-md-6 contaFrm">
							<label>Your City</label>
							<input type="text" placeholder="your City" name="city"/>
						</div>
						<div class="col-md-12 contaFrm">
							<label>I want to talk About</label>
							<select name="talk_about">
								<option>Please Choose an option</option>
								<?php
        	$get_service_sql="select * from tbl_service order by service_id asc";
			$get_service_rs=mysqli_query($conn,$get_service_sql);
			$i=1;
			while($get_service_row=mysqli_fetch_array($get_service_rs))
			{
        	?>
								<option><?= $get_service_row['service_title'];?></option>
				<?php
			$i++;
			}
            ?>				
								
							</select>
						</div>
						<div class="col-md-12 contaFrm">
							<label>Your Message</label>
							<textarea placeholder="Your Message"  name="msg"></textarea>
						</div>
						<div class="col-md-12 contaFrm">
							<div class="g-recaptcha" id="RecaptchaField3" data-sitekey="6LcxlwUqAAAAAKiWmZ9KUMkbh15-WenTmpvTaqqf"></div>
							<div id="capmsg" ></div>
							
						</div>
						<div class="col-md-12 contaFrm">
							<span class="privcy">
								<input type="checkbox" id="chkb" required="required" />
								<label for="chkb">I agree to the <a href="<?= SITE_URL; ?>page.php?alise=privacy-policy" target="_blank"> Privacy Policy</a> and <a href="<?= SITE_URL; ?>page.php?alise=terms-and-condition" target="_blank">Terms & Condition </a> of this website.</label>
							</span>
						</div>
						<div class="col-md-12 contaFrm">
							<span class="cnctBtn">
								
								<input type="hidden" value="con_submit" name="action"/>		
								<input type="submit" value="" />
								<i><img src="assets/images/sbmt.svg" alt="vwSer"></i>Submit now
							</span>
						</div>
					</div>
				</form>
				<span id="contactmsg"></span>
			</div>
			<div class="col-md-5">
				<img class="w-100" src="<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['page_details_image'];?>" alt="conrgt" />
			</div>
		</div>
	</div>
</section>

<script>
    	function submitform()
		{
		event.preventDefault();		
		var form=$("#frmcontact");
		var response = grecaptcha.getResponse();
		if(response.length == 0)
		{
			$("#capmsg").html("reCaptcha not verified");
		}	
		else
		{
			$.ajax({
			type: "POST",
			url: "contact_feedback.php",
			dataType: 'html',
			data:form.serialize(),
			success: function(html){
			$("#capmsg").html("");
			$("#contactmsg").html(html);
			$('#frmcontact')[0].reset();
			},error: function(){
			},complete: function(){
			}
			});
		}	
		
		}
    </script> 





<?php include("include/footer.php"); ?>