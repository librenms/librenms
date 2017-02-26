<?php $target =  urldecode($_GET['target']); ?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Image <?php echo basename($target); ?></title>
</head>
<body>
<img src="<?php echo basename($target); ?>" border=0 alt="<?php echo basename($target); ?>" align="left">
</body>
</html>
