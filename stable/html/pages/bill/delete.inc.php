
<form name="form1" action="" method="post" class="form-horizontal">
  <link rel="stylesheet" href="<?php echo $config['base_url']; ?>/css/bootstrap.min.css">
  <script type="text/javascript">
    function showWarning(checked) {
      $('#warning').toggle();
      if (checked) {
        $('#deleteBtn').removeAttr('disabled');
      } else {
        $('#deleteBtn').attr('disabled', 'disabled');
      }
    }
  </script>
  <input type="hidden" name="action" value="delete_bill">
  <fieldset>
    <legend>Delete Bill</legend>
    <div class="control-group">
      <label class="control-label" for="confirm"><strong>Confirm</strong></label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning(this.checked);">
          Yes, please delete this bill!
        </label>
      </div>
    </div>
    <div class="alert alert-message" id="warning" style="display: none;">
      <h4 class="alert-heading"><i class="icon-warning-sign"></i> Warning!</h4>
      Are you sure you want to delete his bill?
    </div>
  </fieldset>
  <div class="form-actions">
    <button id="deleteBtn" type="submit" class="btn btn-danger" disabled="disabled"><i class="icon-trash icon-white"></i> <strong>Delete Bill</strong></button>
  </div>
</form>
