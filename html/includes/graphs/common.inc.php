<?php

  global $config, $installdir;
  $graphfile = $config['install_dir'] . "/graphs/" . $args['graphfile'];

  $options .= " --alt-autoscale-max -E --start ".$args['from']." --end " . ($args['to'] - 150) . " --width ".$args['width']." --height ".$args['height']." ";
  $options .= $config['rrdgraph_def_text'];

  if($args['height'] < "99") { $options .= " --only-graph"; }
  if($args['width'] <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal"; }

?>
