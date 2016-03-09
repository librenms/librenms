<?php

  print_optionbar_start('40');

?>

<form method='post' action='' class="form-inline" role="form">
  <fieldset class="form-group" disabled title="Search is currently broken">
    Bills
    <input type="text" name="hostname" id="hostname" class="form-control input-sm" value="<?php echo $_POST['hostname']; ?>" />
    <select name='os' id='os' class="form-control input-sm">
      <option value=''>All Types</option>
      <option value=''>CDR</option>
      <option value=''>95th</option>
      <option value=''>Quota</option>
    </select>
    <select name='hardware' id='hardware' class="form-control input-sm">
      <option value=''>All States</option>
      <option value=''>Under Quota</option>
      <option value=''>Over Quota</option>
    </select>
    <select name='location' id='location' class="form-control input-sm">
      <option value=''>All Customers</option>
    </select>
    <button type="submit" class="btn btn-default input-sm">Search</button>
  </fieldset>
  <div class="form-group pull-right">
<?php
if ($vars['view'] == 'history') {
    echo '<a class="btn btn-default btn-sm" href="bills/"><i class="fa fa-clock-o"></i> Current Billing Period</a>';
}
else {
    // FIXME - generate_url
    echo '<a class="btn btn-default btn-sm" href="bills/view=history/"><i class="fa fa-history"></i> Previous Billing Period</a>';
}

?>
<?php if ($_SESSION['userlevel'] >= 10) {  ?>
    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#create-bill"><i class="fa fa-plus"></i> Create Bill</button>
<?php } ?>
  </div>
</form>

<?php
print_optionbar_end();
