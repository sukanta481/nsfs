<!-- footer content -->
<footer class="modern-footer">
  <div class="footer-content">
    <div class="footer-left">
      <p>&copy; <?php echo date('Y'); ?> <strong>North Super Fast Service</strong>. All rights reserved.</p>
    </div>
    <div class="footer-center">
      <p>Developed by <a href="#" target="_blank"><strong>Next Screen Infotech</strong></a></p>
    </div>
    <div class="footer-right">
      <p>Version 2.0</p>
    </div>
  </div>
</footer>

<style>
.modern-footer {
  background: linear-gradient(135deg, #2c4a61 0%, #1e3a52 100%);
  color: #fff;
  padding: 20px 30px;
  margin-top: 40px;
  box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
  font-family: 'Inter', sans-serif;
  position: relative;
  z-index: 10;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.footer-left, .footer-center, .footer-right {
  flex: 1;
  min-width: 200px;
}

.footer-left {
  text-align: left;
}

.footer-center {
  text-align: center;
}

.footer-right {
  text-align: right;
}

.modern-footer p {
  margin: 0;
  font-size: 0.95rem;
  color: rgba(255,255,255,0.9);
  font-weight: 500;
  letter-spacing: 0.3px;
}

.modern-footer strong {
  color: #ffc107;
  font-weight: 700;
}

.modern-footer a {
  color: #ffc107;
  text-decoration: none;
  transition: all 0.3s;
  font-weight: 700;
}

.modern-footer a:hover {
  color: #fff;
  text-decoration: none;
  text-shadow: 0 0 10px rgba(255,193,7,0.5);
}

/* Responsive footer */
@media (max-width: 992px) {
  .modern-footer {
    margin-left: 0;
  }
  
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-left, .footer-center, .footer-right {
    text-align: center;
  }
}

@media (max-width: 576px) {
  .modern-footer {
    padding: 15px 20px;
  }
  
  .modern-footer p {
    font-size: 0.85rem;
  }
}
</style>
<!-- /footer content -->
      </div>
      <!-- /page content -->

    </div>

  </div>

  <div id="custom_notifications" class="custom-notifications dsp_none">
    <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
    </ul>
    <div class="clearfix"></div>
    <div id="notif-group" class="tabbed_notifications"></div>
  </div>

  <script src="js/bootstrap.min.js"></script>

  <!-- bootstrap progress js -->
  <script src="js/progressbar/bootstrap-progressbar.min.js"></script>
  <!-- icheck -->
  <script src="js/icheck/icheck.min.js"></script>
  <!-- pace -->
  <script src="js/pace/pace.min.js"></script>
  <script src="js/custom.js"></script>
  <!-- form validation -->
  <script src="js/validator/validator.js"></script>
  <script>
    // initialize the validator function
    validator.message['date'] = 'not a real date';

    // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
    $('form')
      .on('blur', 'input[required], input.optional, select.required', validator.checkField)
      .on('change', 'select.required', validator.checkField)
      .on('keypress', 'input[required][pattern]', validator.keypress);

    $('.multi.required')
      .on('keyup blur', 'input', function() {
        validator.checkField.apply($(this).siblings().last()[0]);
      });

    // bind the validation to the form submit event
    //$('#send').click('submit');//.prop('disabled', true);
	
    function validate_form(f) {
     // e.preventDefault();
      var submit = true;
      
      
      var cat_name=document.getElementsByName("cat_id[]");
cat_len=cat_name.length;
var flag=0;
for(i=0;i<cat_len;i++)
{
	if(cat_name[i].checked)
	{
		submit=true;
		break;
	}else{
		
		if (!validator.checkField.apply($('.multi').siblings())){
		submit=false;
		}
	}
}
      
      
      
      // evaluate the form using generic validaing
      if (!validator.checkAll($('#'+f))) {
        submit = false;
      }





      if (submit==true){ 
      	  
        $('#'+f).submit();
      }else{
      	//validator.checkField.apply($('.multi').siblings());
      	//validator.message.checkbox;
      	return false;
      }
    }




  </script>
   <script src="js/datatables/jquery.dataTables.min.js"></script>
        <script src="js/datatables/dataTables.bootstrap.js"></script>
       <script src="js/datatables/dataTables.buttons.min.js"></script>
        <script src="js/datatables/buttons.bootstrap.min.js"></script>
        <script src="js/datatables/jszip.min.js"></script>
        <script src="js/datatables/pdfmake.min.js"></script>
        <script src="js/datatables/vfs_fonts.js"></script>
        <script src="js/datatables/buttons.html5.min.js"></script>
        <script src="js/datatables/buttons.print.min.js"></script>
        <script src="js/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="js/datatables/dataTables.keyTable.min.js"></script>
        <!--<script src="js/datatables/dataTables.responsive.min.js"></script>
        <script src="js/datatables/responsive.bootstrap.min.js"></script>
        <script src="js/datatables/dataTables.scroller.min.js"></script>-->
        <script src="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.css"></script>


        <!-- pace -->
        <script src="js/pace/pace.min.js"></script>
        <script>
          var handleDataTableButtons = function() {
              "use strict";
              0 !== $("#datatable-buttons").length && $("#datatable-buttons").DataTable({
                dom: "Bfrtip",
                buttons: [{
                  extend: "copy",
                  className: "btn-sm"
                }, {
                  extend: "csv",
                  className: "btn-sm"
                }, {
                  extend: "excel",
                  className: "btn-sm"
                }, {
                  extend: "pdf",
                  className: "btn-sm"
                }, {
                  extend: "print",
                  className: "btn-sm"
                }],
                responsive: true
              })
            },
            TableManageButtons = function() {
              "use strict";
              return {
                init: function() {
                  handleDataTableButtons()
                }
              }
            }();
            
        </script>
        <script type="text/javascript">
          $(document).ready(function() {
            $('#datatable').dataTable();
            $('#datatable-keytable').DataTable({
              keys: true,
              "order": [[ 0, "asc" ]]
            });
            $('#datatable-responsive').DataTable();
            
            var table = $('#datatable-fixed-header').DataTable({
              fixedHeader: true
            });
          });
          TableManageButtons.init();
        </script>
</body>

</html>