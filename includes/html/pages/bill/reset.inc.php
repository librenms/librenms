<form name="form1" action="" method="post" class="form-horizontal">
  <?php echo csrf_field() ?>
  <script type="text/javascript">
    function showWarning() {
      var checked = $('input:checked').length;
      if (checked == '0') {
        $('#resetBtn').attr('disabled', 'disabled');
        $('#warning').hide();
      } else {
        $('#resetBtn').removeAttr('disabled');
        $('#warning').show();
      }
    }
  </script>
  <input type="hidden" name="action" value="reset_bill">
  <div class="row">
  <div class="col-md-8 col-md-push-2">
  <div class="alert alert-danger alert-block">
  <h4>Reset Bill</h4>
  <div class="control-group">
    <label class="control-label" for="confirm"><strong>Confirm</strong></label>
    <div class="controls">
      <div class="checkbox">
        <label>
        <input type="checkbox" name="confirm" value="mysql" onchange="javascript: showWarning();">
        Yes, please reset MySQL data for all interfaces on this bill!
        </label>
      </div>
      <div class="checkbox">
        <label>
        <input disabled type="checkbox" name="confirm" value="rrd" onchange="javascript: showWarning();">
        Yes, please reset RRD data for all interfaces on this bill!
        </label>
      </div>
    </div>
  </div>
  <br>
  <div class="alert alert-danger" id="warning" style="display: none;">
    <h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Warning</h4>
    Are you sure you want to reset all <strong>MySQL</strong> and/or <strong>RRD</strong> data for all interface on this bill?
  </div>
  <div class="form-actions">
    <button id="resetBtn" type="submit" class="btn btn-danger" disabled="disabled"><i class="fa fa-refresh"></i> <strong>Reset Bill</strong></button>
  </div>
  </div>
  </div>
  </div>
</form>
