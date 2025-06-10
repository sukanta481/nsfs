<?php
include('conn.php');
$output = '';  
 if(isset($_POST["brand_id"]))  
 {  
      if($_POST["brand_id"] != 'doc')  
      {  
           $sql = "SELECT  trip_no  FROM tbl_shipping"; 
           $result = mysqli_query($conn, $sql);  
      while($row = mysqli_fetch_array($result))  
      { 
          ?>
       
          
           <!--//$output .= $row["box"];  -->
           <option value="<?php echo $row["trip_no"]; ?>"><?php echo $row["trip_no"]; ?></option>
              <?php
      }  
      }  
      else  
      {  
           $sql = "SELECT doc FROM tbl_shipping_details";  
           $result = mysqli_query($conn, $sql);  
      while($row = mysqli_fetch_array($result))  
      { 
          ?>
          
           <!--$output .= $row["doc"];  -->
            <option value="<?php echo $row["doc"]; ?>"><?php echo $row["doc"]; ?></option>
           <?php
      }  
      }  
      
      //echo $output;  
 }  
 ?>  