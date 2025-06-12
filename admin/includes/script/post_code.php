<?php
$message = '';

// Fix for undefined array key "type"
$type = isset($_GET['type']) ? $_GET['type'] : '';

ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

if(isset($_REQUEST['save_post']) && $_REQUEST['save_post'] == "Save") {

    $ser_alias = alias($_REQUEST['post_title']);
    $ser_sql = "select * from tbl_post where alise='" . $ser_alias . "'";
    $ser_res = mysqli_query($conn, $ser_sql);
    $ser_row = mysqli_fetch_array($ser_res);
    $ser_num = mysqli_num_rows($ser_res);
    if($ser_num < 1) {
        $date = date("Y-m-d");
        if(!empty($_FILES['author_image']['name'])) {
            $author_image = time() . $_FILES['author_image']['name'];
            $image_type = $_FILES['author_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['author_image']['size'];
            $temp_name = $_FILES['author_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $author_image;
            $upload = move_uploaded_file($temp_name, $uploadimage);
        } else {
            $author_image = 'noimage.jpg';
        }
            
        if(!empty($_FILES['post_image']['name'])) {
            $image_name = time() . $_FILES['post_image']['name'];
            $image_type = $_FILES['post_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['post_image']['size'];
            $temp_name = $_FILES['post_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            $upload = move_uploaded_file($temp_name, $uploadimage);
        } else {
            $image_name = 'noimage.jpg';
        }
        
        if(!empty($_FILES['review_image']['name'])) {
            $review_image = time() . $_FILES['review_image']['name'];
            $image_type = $_FILES['review_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['review_image']['size'];
            $temp_name = $_FILES['review_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $review_image;
            $upload = move_uploaded_file($temp_name, $uploadimage);
        } else {
            $review_image = 'noimage.jpg';
        }

        $add_post_sql = "insert into tbl_post set  
            meta_title='" . mysqli_real_escape_string($conn, $_REQUEST['meta_title']) . "', 
            meta_keyword='" . mysqli_real_escape_string($conn, $_REQUEST['meta_keyword']) . "', 
            meta_desc='" . mysqli_real_escape_string($conn, $_REQUEST['meta_desc']) . "',      
            post_title='" . mysqli_real_escape_string($conn, $_REQUEST['post_title']) . "',
            alise='" . $ser_alias . "',  
            post_srt_desc='" . mysqli_real_escape_string($conn, $_REQUEST['post_srt_desc']) . "', 
            post_desc='" . mysqli_real_escape_string($conn, $_REQUEST['post_desc']) . "',
            author_title='" . mysqli_real_escape_string($conn, $_REQUEST['author_title']) . "',
            review_name='" . mysqli_real_escape_string($conn, $_REQUEST['review_name']) . "',
            review_content='" . mysqli_real_escape_string($conn, $_REQUEST['review_content']) . "',
            post_overview='" . mysqli_real_escape_string($conn, $_REQUEST['post_overview']) . "',     
            post_date='" . mysqli_real_escape_string($conn, $_REQUEST['post_date']) . "',
            post_image='" . $image_name . "',
            author_image='" . $author_image . "',
            review_image='" . $review_image . "'";
        $add_post_exe = mysqli_query($conn, $add_post_sql) or die(mysqli_error($conn));
        if($add_post_exe) {
            $last_id = mysqli_insert_id($conn);
            if(isset($_REQUEST['post_category']) && $_REQUEST['post_category'] != '') {
                $count = count($_REQUEST['post_category']);
                for($i = 0; $i < $count; $i++) {
                    $add_post_category_sql = "insert into tbl_post_multiple_category set       
                        post_id='" . $last_id . "',
                        post_category_id='" . $_REQUEST['post_category'][$i] . "'";
                    $add_post_category_exe = mysqli_query($conn, $add_post_category_sql) or die(mysqli_error($conn));
                }
            }
            $postmsg .= showMessage("post has been added successfully", 'success');       
        }
    } else {
        $postmsg .= showMessage('There is an item with same name.', 'error');
    }   
}

if(isset($_REQUEST['edit_post']) && $_REQUEST['edit_post'] == "Update") {
    $ser_alias = alias($_REQUEST['post_title']);
    $ser_sql = "select * from tbl_post where alise='" . $ser_alias . "' and post_id!='" . $_REQUEST['post_id'] . "'";
    $ser_res = mysqli_query($conn, $ser_sql);
    $ser_row = mysqli_fetch_array($ser_res);
    $ser_num = mysqli_num_rows($ser_res);
    if($ser_num < 1) {   
        $edit_post_sql1 = "update tbl_post set
            meta_title='" . mysqli_real_escape_string($conn, $_REQUEST['meta_title']) . "', 
            meta_keyword='" . mysqli_real_escape_string($conn, $_REQUEST['meta_keyword']) . "', 
            meta_desc='" . mysqli_real_escape_string($conn, $_REQUEST['meta_desc']) . "', 
            post_title='" . mysqli_real_escape_string($conn, $_REQUEST['post_title']) . "',
            alise='" . $ser_alias . "',
            post_srt_desc='" . mysqli_real_escape_string($conn, $_REQUEST['post_srt_desc']) . "',     
            post_desc='" . mysqli_real_escape_string($conn, $_REQUEST['post_desc']) . "',
            author_title='" . mysqli_real_escape_string($conn, $_REQUEST['author_title']) . "',
            review_name='" . mysqli_real_escape_string($conn, $_REQUEST['review_name']) . "',
            review_content='" . mysqli_real_escape_string($conn, $_REQUEST['review_content']) . "',
            post_overview='" . mysqli_real_escape_string($conn, $_REQUEST['post_overview']) . "',
            post_date='" . mysqli_real_escape_string($conn, $_REQUEST['post_date']) . "'
            where post_id ='" . $_REQUEST['post_id'] . "'";
        $edit_post_exe1 = mysqli_query($conn, $edit_post_sql1) or die(mysqli_error($conn));
        if(!empty($_FILES['post_image']['name'])) {
            $image_name = time() . $_FILES['post_image']['name'];
            $image_type = $_FILES['post_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['post_image']['size'];
            $temp_name = $_FILES['post_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            $upload = move_uploaded_file($temp_name, $uploadimage);
            $edit_post_sql = "update tbl_post set 
            post_image ='" . $image_name . "'
            where post_id='" . $_REQUEST['post_id'] . "'";
            mysqli_query($conn, $edit_post_sql) or die(mysqli_error($conn));
        }
        if(!empty($_FILES['author_image']['name'])) {
            $image_name = time() . $_FILES['author_image']['name'];
            $image_type = $_FILES['author_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['author_image']['size'];
            $temp_name = $_FILES['author_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            $upload = move_uploaded_file($temp_name, $uploadimage);
            $edit_post_sql = "update tbl_post set 
            author_image ='" . $image_name . "'
            where post_id='" . $_REQUEST['post_id'] . "'";
            mysqli_query($conn, $edit_post_sql) or die(mysqli_error($conn));
        }
        
        if(!empty($_FILES['review_image']['name'])) {
            $image_name = time() . $_FILES['review_image']['name'];
            $image_type = $_FILES['review_image']['type'];
            $type = explode("/", "$image_type");
            $image_size = $_FILES['review_image']['size'];
            $temp_name = $_FILES['review_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            $upload = move_uploaded_file($temp_name, $uploadimage);
            $edit_post_sql = "update tbl_post set 
            review_image ='" . $image_name . "'
            where post_id='" . $_REQUEST['post_id'] . "'";
            mysqli_query($conn, $edit_post_sql) or die(mysqli_error($conn));
        }
        if($edit_post_exe1) {
            if(isset($_REQUEST['post_category']) && $_REQUEST['post_category'] != '') {
                $del_post_cat_sql = 'DELETE FROM tbl_post_multiple_category WHERE post_id  = "' . $_REQUEST['post_id'] . '"';
                $del_post_cat_qry = mysqli_query($conn, $del_post_cat_sql);
                $count = count($_REQUEST['post_category']);
                for($i = 0; $i < $count; $i++) {
                    $add_post_category_sql = "insert into tbl_post_multiple_category set       
                        post_id='" . $_REQUEST['post_id'] . "',
                        post_category_id='" . $_REQUEST['post_category'][$i] . "'";
                    $add_post_category_exe = mysqli_query($conn, $add_post_category_sql) or die(mysqli_error($conn));
                }
            }
            $postmsg .= showMessage("Post has been updated successfully", 'success');      
        }
    } else {
        $postmsg .= showMessage('There is an item with same name.', 'error');
    }
}
if(isset($_REQUEST['actnpost']) && isset($_REQUEST['post_id'])) {
    $action = $_REQUEST['actnpost'];
    $post_id = $_REQUEST['post_id'];
    if($action == 'dellpost' && !empty($post_id)) {
        $DelpostSql = 'DELETE FROM tbl_post WHERE post_id  = "' . $post_id . '"';
        $DelpostQuery = g_db_query($DelpostSql);
        if($DelpostQuery) {
            $postmsg .= showMessage('The post Has Been Deleted', 'success');
        }
    }
}
?>
