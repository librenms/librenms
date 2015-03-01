<?php

unset($vars['page']);

// Setup here

if($_SESSION['widescreen'])
{
  $graph_width=1700;
  $thumb_width=180;
} else {
  $graph_width=1075;
  $thumb_width=113;
}

if (!is_numeric($vars['from'])) { $vars['from'] = $config['time']['day']; }
if (!is_numeric($vars['to']))   { $vars['to']   = $config['time']['now']; }

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $vars['type'], $graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];
$id = $vars['id'];

if(is_numeric($vars['device']))
{
  $device = device_by_id_cache($vars['device']);
} elseif(!empty($vars['device'])) {
  $device = device_by_name($vars['device']);
}

if (is_file("includes/graphs/".$type."/auth.inc.php"))
{
  include("includes/graphs/".$type."/auth.inc.php");
}

if (!$auth)
{
  include("includes/error-no-perm.inc.php");
} else {
  if (isset($config['graph_types'][$type][$subtype]['descr']))
  {
    $title .= " :: ".$config['graph_types'][$type][$subtype]['descr'];
  } else {
    $title .= " :: ".ucfirst($subtype);
  }

  # Load our list of available graphtypes for this object
  // FIXME not all of these are going to be valid
  if ($handle = opendir($config['install_dir'] . "/html/includes/graphs/".$type."/"))
  {
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && $file != "auth.inc.php" &&strstr($file, ".inc.php"))
      {
        $types[] = str_replace(".inc.php", "", $file);
      }
    }
    closedir($handle);
  }

  $graph_array = $vars;
  $graph_array['height'] = "60";
  $graph_array['width']  = $thumb_width;
  $graph_array['legend'] = "no";
  $graph_array['to']     = $config['time']['now'];

  print_optionbar_start();
  echo($title);

  echo('<div style="float: right;">');
  ?>
  <form action="">
  <select name='type' id='type'
    onchange="window.open(this.options[this.selectedIndex].value,'_top')" >
          <?php

  foreach ($types as $avail_type)
  {
    echo("<option value='".generate_url($vars, array('type' => $type."_".$avail_type, 'page' => "graphs"))."'");
    if ($avail_type == $subtype) { echo(" selected"); }
    echo(">".nicecase($avail_type)."</option>");
  }
          ?>
    </select>
  </form>
  <?php
  echo('</div>');

  print_optionbar_end();

  print_optionbar_start();

  $thumb_array = array('sixhour' => '6 Hours', 'day' => '24 Hours', 'twoday' => '48 Hours', 'week' => 'One Week', 'twoweek' => 'Two Weeks',
                       'month' => 'One Month', 'twomonth' => 'Two Months','year' => 'One Year', 'twoyear' => 'Two Years');

  echo('<table width=100%><tr>');

  foreach ($thumb_array as $period => $text)
  {
    $graph_array['from']   = $config['time'][$period];

    $link_array = $vars;
    $link_array['from'] = $graph_array['from'];
    $link_array['to'] = $graph_array['to'];
    $link_array['page'] = "graphs";
    $link = generate_url($link_array);

    echo('<td align=center>');
    echo('<span class="device-head">'.$text.'</span><br />');
    echo('<a href="'.$link.'">');
    echo(generate_graph_tag($graph_array));
    echo('</a>');
    echo('</td>');

  }

  echo('</tr></table>');

  $graph_array = $vars;
  $graph_array['height'] = "300";
  $graph_array['width']  = $graph_width;

  echo("<hr />");

  // datetime range picker
?>
    <script type="text/javascript">
        function submitCustomRange(frmdata) {
            var reto = /to=([0-9])+/g;
            var refrom = /from=([0-9])+/g;
            var tsto = new Date(frmdata.dtpickerto.value.replace(' ','T'));
            var tsfrom = new Date(frmdata.dtpickerfrom.value.replace(' ','T'));
            tsto = tsto.getTime() / 1000;
            tsfrom = tsfrom.getTime() / 1000;
            frmdata.selfaction.value = frmdata.selfaction.value.replace(reto, 'to=' + tsto);
            frmdata.selfaction.value = frmdata.selfaction.value.replace(refrom, 'from=' + tsfrom);
            frmdata.action = frmdata.selfaction.value
            return true;
        }
    </script>
<?php
  echo("
    <form class='form-inline' id='customrange' action='test'>
    <input type=hidden id='selfaction' value='" . $_SERVER['REQUEST_URI'] . "'>");
  echo('
        <div class="form-group">
            <label for="dtpickerfrom">From</label>
            <input type="text" class="form-control" id="dtpickerfrom" maxlength="16" value="' . date('Y-m-d H:i', $graph_array['from']) . '" data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <div class="form-group">
            <label for="dtpickerto">To</label>
            <input type="text" class="form-control" id="dtpickerto" maxlength=16 value="' . date('Y-m-d H:i', $graph_array['to']) . '" data-date-format="YYYY-MM-DD HH:mm">
        </div>
        <input type="submit" class="btn btn-default" id="submit" value="Update" onclick="javascript:submitCustomRange(this.form);">
    </form>
    <script type="text/javascript">
        $(function () {
            $("#dtpickerfrom").datetimepicker({useSeconds: false, useCurrent: true, sideBySide: true, useStrict: false, showToday: true});
            $("#dtpickerto").datetimepicker({useSeconds: false, useCurrent: true, sideBySide: true, useStrict: false, showToday: true});
        });
    </script>

  ');

  echo("<hr />");

  if ($vars['legend'] == "no")
  {
    echo(generate_link("Show Legend",$vars, array('page' => "graphs", 'legend' => NULL)));
  } else {
    echo(generate_link("Hide Legend",$vars, array('page' => "graphs", 'legend' => "no")));
  }

  // FIXME : do this properly
#  if ($type == "port" && $subtype == "bits")
#  {
    echo(' | ');
    if ($vars['previous'] == "yes")
    {
      echo(generate_link("Hide Previous",$vars, array('page' => "graphs", 'previous' => NULL)));
    } else {
      echo(generate_link("Show Previous",$vars, array('page' => "graphs", 'previous' => "yes")));
    }
#  }

  echo('<div style="float: right;">');

  if ($vars['showcommand'] == "yes")
  {
    echo(generate_link("Hide RRD Command",$vars, array('page' => "graphs", 'showcommand' => NULL)));
  } else {
    echo(generate_link("Show RRD Command",$vars, array('page' => "graphs", 'showcommand' => "yes")));
  }

  echo('</div>');

  print_optionbar_end();

  echo generate_graph_js_state($graph_array);

  echo('<div style="width: '.$graph_array['width'].'; margin: auto;">');
  echo(generate_graph_tag($graph_array));
  echo("</div>");

  if (isset($config['graph_descr'][$vars['type']]))
  {

    print_optionbar_start();
    echo('<div style="float: left; width: 30px;">
          <div style="margin: auto auto;">
            <img valign=absmiddle src="images/16/information.png" />
          </div>
          </div>');
    echo($config['graph_descr'][$vars['type']]);
    print_optionbar_end();
  }

  if ($vars['showcommand'])
  {
    $_GET = $graph_array;
    $command_only = 1;

    include("includes/graphs/graph.inc.php");
  }
}

?>
