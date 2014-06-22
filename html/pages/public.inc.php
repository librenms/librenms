
<script class="code" type="text/javascript">
$(document).ready(function() {
  $("#ToggleLogon").click( function()
  {
    document.getElementById('public-logon').style.display="block";
    document.getElementById('public-status').style.display="none";
  });
  $("#ToggleStatus").click( function()
  {
    document.getElementById('public-logon').style.display="none";
    document.getElementById('public-status').style.display="block";
  });
});
</script>

<?php

// Set Defaults here

$sql_param = array();
$pagetitle[] = "Public Devices";


$query = "SELECT * FROM `devices` ORDER BY hostname";

?>
<div class="well"><h3>System Status<button class="btn btn-default" type="submit" style="float:right;" id="ToggleLogon">Logon</button></h3></div>
  <div class="panel panel-default panel-condensed">
    <div class="table-responsive">
      <table class="table table-condensed">
        <tr>
          <th></th>
          <th></th>
          <th>Device</th>
          <th></th>
          <th>Platform</th>
          <th>Uptime/Location</th>
        </tr>
<?php
foreach (dbFetchRows($query, $sql_param) as $device)
{
  include("includes/hostbox-public.inc.php");
}
?>
      </table>
    </div>
  </div>
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-center">
<?php
echo(' <br /> <br /> ' . (isset($config['footer']) ? $config['footer'] : ''));
echo(' <br />Powered by <a href="' . $config['project_url'] . '" target="_blank">' . $config['project_name_version'].'</a>. ');
echo( $config['project_name'].' is <a href="http://www.gnu.org/philosophy/free-sw.html">Free Software</a>, released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPLv3</a>.<br/>');
echo(' Copyright &copy; 2006-2012 by Adam Armstrong. Copyright &copy; 2013-'.date("Y").' by the '.$config['project_name'].' Contributors.');
?>
      </div>
    </div>
  </div>
</footer>
