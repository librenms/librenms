<?php $target = urldecode($_GET['target']); ?>
<!doctype html public "-//W3C//DTD HTML 4.0 Frameset//EN">
<html>
<head>
<title> Test suite for JpGraph - <?php echo $target; ?></title>
<script type="text/javascript" language="javascript">
<!--
function resize()
{
	return true;
}
//-->
</script>
</head>
<frameset rows="*,*" onLoad="resize()">
	<?php 
	if( !strstr($target,"csim") )
		echo "<frame src=\"show-image.php?target=".basename($target)."\" name=\"image\">";
	else
		echo	"<frame src=\"".basename($target)."\" name=\"image\">";
	?>
	<frame src="show-source.php?target=<?php echo basename($target); ?>" name="source">
</frameset>
</html>
