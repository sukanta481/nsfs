<?php
require 'includes/top.php';
if(isset($_SESSION['admin']))
{
	
	delete_session('admin');
	
	redirect(href_link('index.php'));
}else {
	redirect(href_link('index.php'));
}