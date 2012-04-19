<?php 

## FIXME not used, do we still need this?

function mailerrorgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  

  $range = $to - $from;
  $points_per_sample = 3;
  $xpoints = '540';
  $step = $range*$points_per_sample/$xpoints;
  $database = $config['rrd_dir'] . "/" . $rrd . "-mailstats.rrd";

  $options .= " DEF:rejected=$database:reject:AVERAGE";
  $options .= " DEF:mrejected=$database:reject:MAX";
  $options .= " CDEF:rrejected=rejected,60,*";
  $options .= " CDEF:drejected=rejected,UN,0,rejected,IF,$step,*";
  $options .= " CDEF:srejected=PREV,UN,drejected,PREV,IF,drejected,+";
  $options .= " CDEF:rmrejected=mrejected,60,*";
  $options .= " DEF:bounced=$database:bounced:AVERAGE";
  $options .= " DEF:mbounced=$database:bounced:MAX";
  $options .= " CDEF:rbounced=bounced,60,*";
  $options .= " CDEF:dbounced=bounced,UN,0,bounced,IF,$step,*";
  $options .= " CDEF:sbounced=PREV,UN,dbounced,PREV,IF,dbounced,+";
  $options .= " CDEF:rmbounced=mbounced,60,*";
  $options .= " DEF:virus=$database:virus:AVERAGE";
  $options .= " DEF:mvirus=$database:virus:MAX";
  $options .= " CDEF:rvirus=virus,60,*";
  $options .= " CDEF:dvirus=virus,UN,0,virus,IF,$step,*";
  $options .= " CDEF:svirus=PREV,UN,dvirus,PREV,IF,dvirus,+";
  $options .= " CDEF:rmvirus=mvirus,60,*";
  $options .= " DEF:spam=$database:spam:AVERAGE";
  $options .= " DEF:mspam=$database:spam:MAX";
  $options .= " CDEF:rspam=spam,60,*";
  $options .= " CDEF:dspam=spam,UN,0,spam,IF,$step,*";
  $options .= " CDEF:sspam=PREV,UN,dspam,PREV,IF,dspam,+";
  $options .= " CDEF:rmspam=mspam,60,*";
  $options .= " COMMENT:Errors\ \ \ \ \ \ \ Total\ \ \ \ \ \ Average\ \ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:rrejected#cc0000:reject";
  $options .= " GPRINT:srejected:MAX:\ %6.0lf";
  $options .= " GPRINT:rrejected:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmrejected:MAX:\ %5.2lf/min\\\\l";
  $options .= " AREA:rbounced#0000cc:bounce";
  $options .= " GPRINT:sbounced:MAX:\ %6.0lf";
  $options .= " GPRINT:rbounced:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmbounced:MAX:\ %5.2lf/min\\\\l";
  $options .= " STACK:rvirus#000000:virus\ ";
  $options .= " GPRINT:svirus:MAX:\ %6.0lf";
  $options .= " GPRINT:rvirus:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmvirus:MAX:\ %5.2lf/min\\\\l";
  $options .= " STACK:rspam#00cc00:spam\ \ ";
  $options .= " GPRINT:sspam:MAX:\ %6.0lf";
  $options .= " GPRINT:rspam:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmspam:MAX:\ %5.2lf/min\\\\l";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function mailsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $range = $to - $from;
  $points_per_sample = 3;
  $xpoints = '540';
  $step = $range*$points_per_sample/$xpoints;
  $rrd = $config['rrd_dir'] . "/" . $rrd;
  $options .= " DEF:sent=$rrd:sent:AVERAGE";
  $options .= " DEF:msent=$rrd:sent:MAX";
  $options .= " CDEF:rsent=sent,60,*";
  $options .= " CDEF:rmsent=msent,60,*";
  $options .= " CDEF:dsent=sent,UN,0,sent,IF,$step,*";
  $options .= " CDEF:ssent=PREV,UN,dsent,PREV,IF,dsent,+";
  $options .= " DEF:recv=$rrd:rcvd:AVERAGE";
  $options .= " DEF:mrecv=$rrd:rcvd:MAX";
  $options .= " CDEF:rrecv=recv,60,*";
  $options .= " CDEF:rmrecv=mrecv,60,*";
  $options .= " CDEF:drecv=recv,UN,0,recv,IF,$step,*";
  $options .= " CDEF:srecv=PREV,UN,drecv,PREV,IF,drecv,+";
  $options .= " COMMENT:Mails\ \ \ \ \ \ Total\ \ \ \ \ \ \ Average\ \ \ \ \ Maximum\\\\n";
  $options .= " AREA:rsent#00c000:sent";
  $options .= " LINE1.25:rsent#005000:";
  $options .= " GPRINT:ssent:MAX:\ \ %6.0lf";
  $options .= " GPRINT:rsent:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmsent:MAX:\ %5.2lf/min\\\\l";
  $options .= " LINE1.25:rrecv#cc0000:rcvd";
  $options .= " GPRINT:srecv:MAX:\ \ %6.0lf";
  $options .= " GPRINT:rrecv:AVERAGE:\ \ %5.2lf/min";
  $options .= " GPRINT:rmrecv:MAX:\ %5.2lf/min\\\\l";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function memgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -b 1024";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $options .= " DEF:atotalswap=$database:totalswap:AVERAGE";
  $options .= " DEF:aavailswap=$database:availswap:AVERAGE";
  $options .= " DEF:atotalreal=$database:totalreal:AVERAGE";
  $options .= " DEF:aavailreal=$database:availreal:AVERAGE";
  $options .= " DEF:atotalfree=$database:totalfree:AVERAGE";
  $options .= " DEF:ashared=$database:shared:AVERAGE";
  $options .= " DEF:abuffered=$database:buffered:AVERAGE";
  $options .= " DEF:acached=$database:cached:AVERAGE";
  $options .= " CDEF:totalswap=atotalswap,1024,*";
  $options .= " CDEF:availswap=aavailswap,1024,*";
  $options .= " CDEF:totalreal=atotalreal,1024,*";
  $options .= " CDEF:availreal=aavailreal,1024,*";
  $options .= " CDEF:totalfree=atotalfree,1024,*";
  $options .= " CDEF:shared=ashared,1024,*";
  $options .= " CDEF:buffered=abuffered,1024,*";
  $options .= " CDEF:cached=acached,1024,*";
  $options .= " CDEF:usedreal=totalreal,availreal,-";
  $options .= " CDEF:usedswap=totalswap,availswap,-";
  $options .= " CDEF:cusedswap=usedswap,-1,*";
  $options .= " CDEF:cdeftot=availreal,shared,buffered,usedreal,cached,usedswap,+,+,+,+,+";
  $options .= " COMMENT:Bytes\ \ \ \ \ \ \ Current\ \ \ \ Average\ \ \ \ \ Maximum\\n";
  $options .= " LINE1:usedreal#d0b080:";
  $options .= " AREA:usedreal#f0e0a0:used";
  $options .= " GPRINT:usedreal:LAST:\ \ \ %7.2lf%sB";
  $options .= " GPRINT:usedreal:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:usedreal:MAX:%7.2lf%sB\\\\n";
  $options .= " STACK:availreal#e5e5e5:free";
  $options .= " GPRINT:availreal:LAST:\ \ \ %7.2lf%sB";
  $options .= " GPRINT:availreal:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:availreal:MAX:%7.2lf%sB\\\\n";
  $options .= " LINE1:usedreal#d0b080:";
  $options .= " AREA:shared#afeced::";
  $options .= " AREA:buffered#cc0000::STACK";
  $options .= " AREA:cached#ffaa66::STACK";
  $options .= " LINE1.25:shared#008fea:shared";
  $options .= " GPRINT:shared:LAST:\ %7.2lf%sB";
  $options .= " GPRINT:shared:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:shared:MAX:%7.2lf%sB\\\\n";
  $options .= " LINE1.25:buffered#ff1a00:buffers:STACK";
  $options .= " GPRINT:buffered:LAST:%7.2lf%sB";
  $options .= " GPRINT:buffered:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:buffered:MAX:%7.2lf%sB\\\\n";
  $options .= " LINE1.25:cached#ea8f00:cached:STACK";
  $options .= " GPRINT:cached:LAST:\ %7.2lf%sB";
  $options .= " GPRINT:cached:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:cached:MAX:%7.2lf%sB\\\\n";
  $options .= " LINE1:totalreal#050505:";
  $options .= " AREA:cusedswap#C3D9FF:swap";
  $options .= " LINE1.25:cusedswap#356AA0:";
  $options .= " GPRINT:usedswap:LAST:\ \ \ %7.2lf%sB";
  $options .= " GPRINT:usedswap:AVERAGE:%7.2lf%sB";
  $options .= " GPRINT:usedswap:MAX:%7.2lf%sB\\\\n";
  $options .= " LINE1:totalreal#050505:total";
  $options .= " GPRINT:totalreal:AVERAGE:\ \ %7.2lf%sB";

  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function loadgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $options .= " DEF:1min=$database:1min:AVERAGE";
  $options .= " DEF:5min=$database:5min:AVERAGE";
  $options .= " DEF:15min=$database:15min:AVERAGE";
  $options .= " CDEF:a=1min,100,/";
  $options .= " CDEF:b=5min,100,/";
  $options .= " CDEF:c=15min,100,/";
  $options .= " CDEF:cdefd=a,b,c,+,+";
  $options .= " COMMENT:Load\ Average\ \ Current\ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:a#ffeeaa:1\ Min:";
  $options .= " LINE1:a#c5aa00:";
  $options .= " GPRINT:a:LAST:\ \ \ \ %7.2lf";
  $options .= " GPRINT:a:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:a:MAX:\ \ %7.2lf\\\\n";
  $options .= " LINE1.25:b#ea8f00:5\ Min:";
  $options .= " GPRINT:b:LAST:\ \ \ \ %7.2lf";
  $options .= " GPRINT:b:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:b:MAX:\ \ %7.2lf\\\\n";
  $options .= " LINE1.25:c#cc0000:15\ Min";
  $options .= " GPRINT:c:LAST:\ \ \ %7.2lf";
  $options .= " GPRINT:c:AVERAGE:\ \ %7.2lf";
  $options .= " GPRINT:c:MAX:\ \ %7.2lf\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}


function usersgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -l 0";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $options .= " DEF:users=$database:users:AVERAGE";
  $options .= " COMMENT:Users\ \ \ \ \ \ \ Cur\ \ \ \ \ Ave\ \ \ \ \ \ Min\ \ \ \ \ Max\\\\n";
  $options .= " AREA:users#CDEB8B:";
  $options .= " LINE1.25:users#008C00:\ ";
  $options .= " GPRINT:users:LAST:\ \ \ \ %6.2lf";
  $options .= " GPRINT:users:AVERAGE:%6.2lf";
  $options .= " GPRINT:users:MIN:%6.2lf";
  $options .= " GPRINT:users:MAX:%6.2lf\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function procsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -l 0";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:procs=$database:procs:AVERAGE";
  $options .= " COMMENT:Processes\ \ \ \ Cur\ \ \ \ \ Ave\ \ \ \ \ \ Min\ \ \ \ \ Max\\\\n";
  $options .= " AREA:procs#CDEB8B:";
  $options .= " LINE1.25:procs#008C00:\ ";
  $options .= " GPRINT:procs:LAST:\ \ \ \ %6.2lf";
  $options .= " GPRINT:procs:AVERAGE:%6.2lf";
  $options .= " GPRINT:procs:MIN:%6.2lf";
  $options .= " GPRINT:procs:MAX:%6.2lf\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}



function cpugraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config;
  $database = $config['rrd_dir'] . "/" . $rrd;
  $period = $to - $from;
  $options = "-l 0 --alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $options .= " DEF:user=$database:user:AVERAGE";
  $options .= " DEF:nice=$database:nice:AVERAGE";
  $options .= " DEF:system=$database:system:AVERAGE";
  $options .= " DEF:idle=$database:idle:AVERAGE";
  $options .= " CDEF:total=user,nice,system,idle,+,+,+";
  $options .= " CDEF:user_perc=user,total,/,100,*";
  $options .= " CDEF:nice_perc=nice,total,/,100,*";
  $options .= " CDEF:system_perc=system,total,/,100,*";
  $options .= " CDEF:idle_perc=idle,total,/,100,*";
  $options .= " COMMENT:Usage\ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " AREA:user_perc#c02020:user";
  $options .= " GPRINT:user_perc:LAST:\ \ \ \ \ %5.2lf%%";
  $options .= " GPRINT:user_perc:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:user_perc:MAX:\ \ \ %5.2lf%%\\\\n";
  $options .= " AREA:nice_perc#008f00:nice:STACK";
  $options .= " GPRINT:nice_perc:LAST:\ \ \ \ \ %5.2lf%%";
  $options .= " GPRINT:nice_perc:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:nice_perc:MAX:\ \ \ %5.2lf%%\\\\n";
  $options .= " AREA:system_perc#ea8f00:system:STACK";
  $options .= " GPRINT:system_perc:LAST:\ \ \ %5.2lf%%";
  $options .= " GPRINT:system_perc:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:system_perc:MAX:\ \ \ %5.2lf%%\\\\n";
  $options .= " AREA:idle_perc#f5f5e5:idle:STACK";
  $options .= " GPRINT:idle_perc:LAST:\ \ \ \ \ %5.2lf%%";
  $options .= " GPRINT:idle_perc:AVERAGE:\ \ \ %5.2lf%%";
  $options .= " GPRINT:idle_perc:MAX:\ \ \ %5.2lf%%\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function couriergraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;  
  $period = $to - $from;
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height ";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $points_per_sample = 3;
  $range = $to - $from;
  $options .= " DEF:pop3d_login=$database:pop3:AVERAGE";
  $options .= " DEF:mpop3d_login=$database:pop3:MAX";
  $options .= " DEF:imapd_login=$database:imap:AVERAGE";
  $options .= " DEF:mimapd_login=$database:imap:MAX";
  $options .= " CDEF:rpop3d_login=pop3d_login,60,*";
  $options .= " CDEF:vpop3d_login=pop3d_login,UN,0,pop3d_login,IF,$range,*";
  $options .= " CDEF:rmpop3d_login=mpop3d_login,60,*";
  $options .= " CDEF:rimapd_login=imapd_login,60,*";
  $options .= " CDEF:vimapd_login=imapd_login,UN,0,imapd_login,IF,$range,*";
  $options .= " CDEF:rmimapd_login=mimapd_login,60,*";
  $options .= " DEF:pop3d_ssl_login=$database:pop3ssl:AVERAGE";
  $options .= " DEF:mpop3d_ssl_login=$database:pop3ssl:MAX";
  $options .= " DEF:imapd_ssl_login=$database:imapssl:AVERAGE";
  $options .= " DEF:mimapd_ssl_login=$database:imapssl:MAX";
  $options .= " CDEF:rpop3d_ssl_login=pop3d_ssl_login,60,*";
  $options .= " CDEF:vpop3d_ssl_login=pop3d_ssl_login,UN,0,pop3d_ssl_login,IF,$range,*";
  $options .= " CDEF:rmpop3d_ssl_login=mpop3d_ssl_login,60,*";
  $options .= " CDEF:rimapd_ssl_login=imapd_ssl_login,60,*";
  $options .= " CDEF:rmimapd_ssl_login=mimapd_ssl_login,60,*";
  $options .= " CDEF:vimapd_ssl_login=imapd_ssl_login,UN,0,imapd_ssl_login,IF,$range,*";
  $options .= " COMMENT:Logins\ \ \ \ \ \ \ \ \ Total\ \ \ \ Average\ \ \ \ Maximum\\\\n";
  $options .= " LINE1.25:rpop3d_login#BB0000:pop3";
  $options .= " GPRINT:vpop3d_login:AVERAGE:\ \ \ \ \ %6.0lf";
  $options .= " GPRINT:rpop3d_login:AVERAGE:%5.2lf/min";
  $options .= " GPRINT:rmpop3d_login:MAX:%5.2lf/min\\\\l";
  $options .= " LINE1.25:rimapd_login#009900:imap";
  $options .= " GPRINT:vimapd_login:AVERAGE:\ \ \ \ \ %6.0lf";
  $options .= " GPRINT:rimapd_login:AVERAGE:%5.2lf/min";
  $options .= " GPRINT:rmimapd_login:MAX:%5.2lf/min\\\\l";
  $options .= " LINE1.25:rpop3d_ssl_login#000000:pop3-ssl";
  $options .= " GPRINT:vpop3d_ssl_login:AVERAGE:\ %6.0lf";
  $options .= " GPRINT:rpop3d_ssl_login:AVERAGE:%5.2lf/min";
  $options .= " GPRINT:rmpop3d_ssl_login:MAX:%5.2lf/min\\\\l";
  $options .= " LINE1.25:rimapd_ssl_login#000099:imap-ssl";
  $options .= " GPRINT:vimapd_ssl_login:AVERAGE:\ %6.0lf";
  $options .= " GPRINT:rimapd_ssl_login:AVERAGE:%5.2lf/min";
  $options .= " GPRINT:rmimapd_ssl_login:MAX:%5.2lf/min\\\\l";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function apachehitsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;
  
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -l 0";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }  
  $options .= " DEF:hits=$database:hits:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ Maximum\\\\n";
  $options .= " AREA:hits#ff9933:";
  $options .= " LINE1.25:hits#FF6600:Hits";
  $options .= " GPRINT:hits:LAST:\ %6.2lf/sec";
  $options .= " GPRINT:hits:AVERAGE:%6.2lf/sec";
  $options .= " GPRINT:hits:MAX:%6.2lf/sec\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}

function unixfsgraph ($id, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -b 1024 -l 0";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $hostname = gethostbyid($device);
  $iter = "1";
  $sql = mysql_query("SELECT * FROM storage where storage_id = '$id'");
  $options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Size\ \ \ \ \ \ Used\ \ \ \ %age\l";
  while($fs = mysql_fetch_array($sql)) {
    $hostname = gethostbyid($fs['device_id']);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }
    $descr = str_pad($fs[storage_descr], 14);
    $descr = substr($descr,0,14);
    $text = str_replace("/", "_", $fs['storage_descr']);
    $rrd = $config['rrd_dir'] . "/$hostname/storage-$text.rrd";
    $options .= " DEF:$fs[storage_id]=$rrd:used:AVERAGE";
    $options .= " DEF:$fs[storage_id]s=$rrd:size:AVERAGE";
    $options .= " DEF:$fs[storage_id]p=$rrd:perc:AVERAGE";
    $options .= " LINE1.25:$fs[storage_id]p#" . $colour . ":'$descr'";
    $options .= " GPRINT:$fs[storage_id]s:LAST:%6.2lf%SB";
    $options .= " GPRINT:$fs[storage_id]:LAST:%6.2lf%SB";
    $options .= " GPRINT:$fs[storage_id]p:LAST:%5.2lf%%\\\\l";
    $iter++;
  }
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}


function apachebitsgraphUnix ($rrd, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  $database = $config['rrd_dir'] . "/" . $rrd;  
  $options = "--alt-autoscale-max -E --start $from --end $to --width $width --height $height -l 0";
  if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $options .= " DEF:bits=$database:bits:AVERAGE";
  $options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ Maximum\\\\n";
  $options .= " AREA:bits#cccc00:";
  $options .= " LINE1.25:bits#9900:Bits";
  $options .= " GPRINT:bits:LAST:\ %6.2lf/sec";
  $options .= " GPRINT:bits:AVERAGE:%6.2lf/sec";
  $options .= " GPRINT:bits:MAX:%6.2lf/sec\\\\n";
  shell_exec($config['rrdtool'] . " graph $graph $options");
  return $graph;
}
