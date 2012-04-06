<form name="form1" action="" method="post" class="form-horizontal">
  <link rel="stylesheet" href="<?php echo $config['base_url']; ?>/css/bootstrap.min.css">
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
  <fieldset>
    <legend>Reset Bill</legend>
    <div class="control-group">
      <label class="control-label" for="confirm"><strong>Confirm</strong></label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="confirm" value="mysql" onchange="javascript: showWarning();">
          Yes, please reset MySQL data for all interfaces on this bill!
        </label>
        <label class="checkbox">
          <input disabled type="checkbox" name="confirm" value="rrd" onchange="javascript: showWarning();">
          Yes, please reset RRD data for all interfaces on this bill!
        </label>
      </div>
    </div>
    <div class="alert alert-message" id="warning" style="display: none;">
      <h4 class="alert-heading"><i class="icon-warning-sign"></i> Warning!</h4>
      Are you sure you want to reset all <strong>MySQL</strong> and/or <strong>RRD</strong> data for all interface on this bill?
    </div>
  </fieldset>
  <div class="form-actions">
    <button id="resetBtn" type="submit" class="btn btn-danger" disabled="disabled"><i class="icon-refresh icon-white"></i> <strong>Reset Bill</strong></button>
  </div>
</form>
