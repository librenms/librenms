<?

if ($_SESSION['userlevel'] >= "5") 
{
  $id = mres($_GET['id']);
  $title = generatedevicelink($device);
  $auth = TRUE;
}

?>
