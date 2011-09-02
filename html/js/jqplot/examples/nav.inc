<div class="nav">
  <?php
    $dh  = opendir('./');
    while (false !== ($filename = readdir($dh))) {
        if (preg_match('/^[a-z0-9]+\.html$/i', $filename)) {
          $files[] = $filename;
        }
    }
    $fcount = count($files);
    $parts = explode("/", $_SERVER['SCRIPT_NAME']);
    $curfile = end($parts);
    $prevfile = '';
    $nextfile = '';
    // echo $fcount, $curfile;
    // print_r($files);
    
    for ($i=0; $i<$fcount; $i++) {
      if ($curfile == $files[$i]) {
        $prevfile = $files[$i-1];
        $nextfile = $files[$i+1];
        if ($i == 0) {
          $prevfile = $files[$fcount-1];
          $nextfile = $files[1];
        }
        elseif ($i == $fcount-1) {
          $prevfile = $files[$i-1];
          $nextfile = $files[0];
        }
      }  
    }
    
    echo '<a href="'.$prevfile.'">Previous</a> <a href="./">Examples</a> <a href="'.$nextfile.'">Next</a>';
    
  ?>
</div>
