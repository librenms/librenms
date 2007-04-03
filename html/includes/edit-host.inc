<?

  $updated = '1';

  $descr = $_POST['descr'];
  $apache = $_POST['apache'];
  $temp = $_POST['temp'];
  $ignore = $_POST['ignore'];
  $type = $_POST['type'];

  $sql = "UPDATE `devices` SET `purpose` = '$descr', `apache` = '$apache', `temp` = '$temp', `type` = '$type', `ignore` = '$ignore' WHERE `id` = '$_GET[id]'";
  $query = mysql_query($sql);

  $affected = mysql_affected_rows() . "records affected";

?>
