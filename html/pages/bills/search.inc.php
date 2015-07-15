<?php

  print_optionbar_start('40');

?>

<form method='post' action='' class="form-inline" role="form">
  <div class="form-group">
    Bills
  </div>
  <div class="form-group">
    <input type="text" name="hostname" id="hostname" class="form-control input-sm" value="<?php echo($_POST['hostname']); ?>" />
  </div>
  <div class="form-group">
    <select name='os' id='os' class="form-control input-sm">
      <option value=''>All Types</option>
      <option value=''>CDR</option>
      <option value=''>95th</option>
      <option value=''>Quota</option>
    </select>
  </div>
  <div class="form-group">
    <select name='hardware' id='hardware' class="form-control input-sm">
      <option value=''>All States</option>
      <option value=''>Under Quota</option>
      <option value=''>Over Quota</option>
    </select>
  </div>
  <div class="form-group">
    <select name='location' id='location' class="form-control input-sm">
      <option value=''>All Customers</option>
    </select>
  </div>
  <button type="submit" class="btn btn-default input-sm">Search</button>
  <div class="form-group">
<?php

  if ($vars['view'] == "history")
  {
    echo('<a href="bills/"><img src="images/16/clock.png" align=absmiddle alt="Current Billing Period"> Current Billing Period</a>');
  } else
  {
    // FIXME - generate_url
    echo('<a href="bills/view=history/"><img src="images/16/clock_red.png" align=absmiddle alt="Previous Billing Period"> Previous Billing Period</a>');
  }

?>
  </div>
  <div class="form-group">
    <a href='bills/view=add/'><img src="images/16/add.png" align=absmiddle alt="Add"> Add Bill</a>
  </div>
</form>

<?php

  print_optionbar_end();

?>
