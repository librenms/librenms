<?php
  ini_set('allow_url_fopen', 0);

  include("../config.php");
  include("../includes/functions.php");
  include("includes/authenticate.inc");
  $start = utime();
  $id = $_GET['device']
?>

  Really Delete?
  <a href="javascript:;" onclick="opener.location='/?page=list&delete=<?php echo("$id"); ?>';self.close()">Yes!</a> 
  <a href=\"javascript:;" onclick="self.close()">No!</a>
