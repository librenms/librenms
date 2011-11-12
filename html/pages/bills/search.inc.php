<?php

  print_optionbar_start('40');

?>

        <table cellpadding=7 cellspacing=0 class=devicetable width=100%>
<form method='post' action=''>
<tr>
             <td width='40' align=center valign=middle><div style='font-weight: bold; font-size: 16px;'>Bills</div></td>
             <td width='240'><span style='font-weight: bold; font-size: 14px;'></span>
             <input type="text" name="hostname" id="hostname" size=40 value="<?php echo($_POST['hostname']); ?>" />
             </td>
             <td width='100'>
      <select name='os' id='os'>
      <option value=''>All Types</option>
      <option value=''>CDR</option>
      <option value=''>95th</option>
      <option value=''>Quota</option>
       </select>
             </td>
             <td width='100'>
      <select name='hardware' id='hardware'>
      <option value=''>All States</option>
      <option value=''>Under Quota</option>
      <option value=''>Over Quota</option>
       </select>
             </td>
             <td width='100'>
      <select name='location' id='location'>
      <option value=''>All Customers</option>
       </select>
     </td>
                 <td>
         <input type=submit class=submit value=Search>
             </td>
             <td width='170' style='font-weight:bold; font-size: 12px;'>
<?php

  if ($_GET['opta'] == "history")
  {
    echo('<a href="bills/"><img src="images/16/clock.png" align=absmiddle alt="Current Billing Period"> Current Billing Period</a>');
  } else
  {
    echo('<a href="bills/history/"><img src="images/16/clock_red.png" align=absmiddle alt="Previous Billing Period"> Previous Billing Period</a>');
  }

?>
             </td>
             <td width='80' style='font-weight:bold; font-size: 12px;'>
                                                   <a href='bills/add/'><img src="images/16/add.png" align=absmiddle alt="Add"> Add Bill</a>
             </td>
           </tr>
  </form>
</table>

<?php

  print_optionbar_end();

?>
