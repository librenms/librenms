<?php

if ($_POST['action'] == "delete_bill" && $_POST['confirm'] == "confirm")
{
  foreach (dbFetchRows("SELECT * FROM `bill_ports` WHERE `bill_id` = ?", array($bill_id)) as $port_data)
  {
    dbDelete('port_in_measurements', '`port_id` = ?', array($port_data['bill_id']));
    dbDelete('port_out_measurements', '`port_id` = ?', array($port_data['bill_id']));
  }

  dbDelete('bill_ports', '`bill_id` = ?', array($bill_id));
  dbDelete('bill_data', '`bill_id` = ?', array($bill_id));
  dbDelete('bill_perms', '`bill_id` = ?', array($bill_id));
  dbDelete('bills', '`bill_id` = ?', array($bill_id));

  echo("<div class=infobox>Bill Deleted. Redirecting to Bills list.</div>");

  echo("<meta http-equiv='Refresh' content=\"2; url='bills/'\">");
}

if ($_POST['action'] == "add_bill_port")
{
  dbInsert(array('bill_id' => $_POST['bill_id'], 'port_id' => $_POST['interface_id']), 'bill_ports');
}
if ($_POST['action'] == "delete_bill_port")
{
  dbDelete('bill_ports', "`bill_id` =  ? AND `port_id` = ?", array($bill_id, $_POST['interface_id']));
}
if ($_POST['action'] == "update_bill")
{

  if (dbUpdate(array('bill_name' => $_POST['bill_name'], 'bill_day' => $_POST['bill_day'], 'bill_gb' => $_POST['bill_gb'],
                 'bill_cdr' => $_POST['bill_cdr'], 'bill_type' => $_POST['bill_type']), 'bills', '`bill_id` = ?', array($bill_id)))
  {
    print_message("Bill Properties Updated");
  }
}

?>
