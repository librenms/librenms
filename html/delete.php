<?php
#  ini_set('display_errors', 1);
#  ini_set('display_startup_errors', 1);
#  ini_set('log_errors', 1);
  ini_set('allow_url_fopen', 0);
#  ini_set('error_reporting', E_ALL);

  include("../config.php");
  include("../includes/functions.php");
  include("includes/authenticate.inc");
  $start = utime();
  $id = $_GET['device']
?>

  Really Delete?
  <a href="javascript:;" onclick="opener.location='/?page=list&delete=<?php echo("$id"); ?>';self.close()">Yes!</a> 
  <a href=\"javascript:;" onclick="self.close()">No!</a>
