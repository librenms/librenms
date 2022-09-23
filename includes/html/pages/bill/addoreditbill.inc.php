<h4>Bill Information</h4>
<div class="form-group">
  <label for="bill_name" class="col-sm-4 control-label">Description</label>
  <div class="col-sm-8">
    <input class="form-control input-sm" type="text" id="bill_name" name="bill_name" value="<?php echo $bill_data['bill_name']; ?>">
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label" for="bill_type">Billing Type</label>
  <div class="col-sm-8">
    <label class="radio-inline">
      <input type="radio" name="bill_type" id="bill_type_cdr" value="cdr"
            <?php
            if ($bill_data['bill_type'] == 'cdr') {
                echo 'checked';
            }
            ?> onchange="javascript: billType();" /> CDR 95th
    </label>
    <label class="radio-inline">
      <input type="radio" name="bill_type" id="bill_type_quota" value="quota"
            <?php
            if ($bill_data['bill_type'] == 'quota') {
                echo 'checked';
            }
            ?> onchange="javascript: billType();" /> Quota
    </label>
  </div>
</div>
<div class="form-group">
  <div id="cdrDiv">
    <label class="col-sm-4 control-label" for="bill_cdr">CDR</label>
    <div class="col-sm-3">
      <input class="form-control input-sm" type="text" name="bill_cdr" value="<?php echo $cdr['data'] ?>">
    </div>
    <div class="col-sm-5">
      <select name="bill_cdr_type" class="form-control input-sm">
        <option <?php echo $cdr['select_kbps'] ?> value="Kbps">Kilobits per second (Kbps)</option>
        <option <?php echo $cdr['select_mbps'] ?> value="Mbps">Megabits per second (Mbps)</option>
        <option <?php echo $cdr['select_gbps'] ?> value="Gbps">Gigabits per second (Gbps)</option>
      </select>
    </div>
    <label class="col-sm-4 control-label" for="dir_95th">95th Calculation</label>
    <div class="col-sm-8">
      <label class="radio-inline">
       <input type="radio" name="dir_95th" id="dir_95th_inout" value="in"
            <?php
            if ($bill_data['dir_95th'] == 'in' || $bill_data['dir_95th'] == 'out') {
                echo 'checked';
            }
            ?> /> Max In/Out
       </label>
      <label class="radio-inline">
       <input type="radio" name="dir_95th" id="dir_95th_agg" value="agg"
            <?php
            if ($bill_data['dir_95th'] == 'agg') {
                echo 'checked';
            }
            ?> /> Aggregate
       </label>
    </div>
  </div>
  <div id="quotaDiv">
    <label class="col-sm-4 control-label" for="bill_quota">Quota</label>
    <div class="col-sm-3">
      <input class="form-control input-sm" type="text" name="bill_quota" value="<?php echo $quota['data'] ?>">
    </div>
    <div class="col-sm-5">
      <select name="bill_quota_type" class="form-control input-sm">
        <option <?php echo $quota['select_mb'] ?> value="MB">Megabytes (MB)</option>
        <option <?php echo $quota['select_gb'] ?> value="GB">Gigabytes (GB)</option>
        <option <?php echo $quota['select_tb'] ?> value="TB">Terabytes (TB)</option>
      </select>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label" for="bill_day">Billing Day</label>
  <div class="col-sm-2">
    <select name="bill_day" class="form-control input-sm">
    <?php
    for ($x = 1; $x < 32; $x++) {
        $sel = $bill_data['bill_day'] == $x ? 'selected ' : '';
        echo "<option $sel value='$x'>$x</option>\n";
    }
    ?>
    </select>
  </div>
</div>
<fieldset>
    <h4>Optional Information</h4>
    <div class="form-group">
      <label class="col-sm-4 control-label" for="bill_custid">Customer Reference</label>
      <div class="col-sm-8">
        <input class="form-control input-sm" type="text" name="bill_custid" value="<?php echo $bill_data['bill_custid'] ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label" for="bill_ref">Billing Reference</label>
      <div class="col-sm-8">
        <input class="form-control input-sm" type="text" name="bill_ref" value="<?php echo $bill_data['bill_ref']; ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label" for="bill_notes">Notes</label>
      <div class="col-sm-8">
        <input class="form-control input-sm" type="textarea" name="bill_notes" value="<?php echo $bill_data['bill_notes']; ?>">
      </div>
    </div>
</fieldset>

<script type="text/javascript">
function billType() {
    var selected = $('input[name=bill_type]:checked').val();

    $('#cdrDiv').toggle(selected === 'cdr');
    $('#quotaDiv').toggle(selected === 'quota');
}
billType();
</script>
