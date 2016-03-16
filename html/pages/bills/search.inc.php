<?php

  print_optionbar_start('40');

?>

<form method='get' action='' class="form-inline" role="form">
  <fieldset class="form-group">
    Bills
    <input type="text" name="search" id="search" class="form-control input-sm" value="<?php echo $_GET['search']; ?>" />
    <select name='bill_type' id='bill_type' class="form-control input-sm">
      <option value=''>All Types</option>
      <option value='cdr' <?php if ($_GET['bill_type'] === 'cdr') { echo 'selected'; } ?>>CDR</option>
      <option value='quota' <?php if ($_GET['bill_type'] === 'quota') { echo 'selected'; } ?>>Quota</option>
    </select>
    <select name='state' id='state' class="form-control input-sm">
      <option value=''>All States</option>
      <option value='under' <?php if ($_GET['state'] === 'under') { echo 'selected'; } ?>>Under Quota</option>
      <option value='over' <?php if ($_GET['state'] === 'over') { echo 'selected'; } ?>>Over Quota</option>
    </select>
    <button type="submit" class="btn btn-default input-sm">Search</button>
  </fieldset>
  <div class="form-group pull-right">
<?php
if ($vars['view'] == 'history') {
    echo '<a class="btn btn-default btn-sm" href="bills/"><i class="fa fa-clock-o"></i> Current Billing Period</a>';
}
else {
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
