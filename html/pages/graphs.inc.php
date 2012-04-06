<?php

unset($vars['page']);

### Setup here

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

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', mres($vars['type']), $graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];
$id = $vars['id'];

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

  $graph_array = $vars;
  $graph_array['height'] = "60";
  $graph_array['width']  = $thumb_width;
  $graph_array['legend'] = "no";
  $graph_array['to']     = $now;

  print_optionbar_start();
  echo($title);
  print_optionbar_end();

  // css and js for datetimepicker
  echo("
    <link type='text/css' href='css/ui-lightness/jquery-ui-1.8.18.custom.css' rel='stylesheet' />
    <script type='text/javascript' src='js/jquery-ui.min.js'></script>
    <script type='text/javascript' src='js/jquery-ui-timepicker-addon.js'></script>
    <script type='text/javascript' src='js/jquery-ui-sliderAccess.js'></script>
    <script type='text/javascript'>
      $(function()
      {
        $('#dtpickerfrom').datetimepicker({
          showOn: 'button',
          buttonImage: 'images/16/date.png',
          buttonImageOnly: true,
          dateFormat: 'yy-mm-dd',
          hourGrid: 4,
          minuteGrid: 10,
          onClose: function(dateText, inst) {
            var toDateTextBox = $('#dtpickerto');
            if (toDateTextBox.val() != '') {
              var testStartDate = new Date(dateText);
              var testEndDate = new Date(toDateTextBox.val());
              if (testStartDate > testEndDate)
                toDateTextBox.val(dateText);
            }
            else {
              toDateTextBox.val(dateText);
            }
          },
          onSelect: function (selectedDateTime) {
            var toDateTextBox = $('#dtpickerto');
            var toValue = toDateTextBox.val();
            var start = $(this).datetimepicker('getDate');
            toDateTextBox.datetimepicker('option', 'minDate', new Date(start.getTime()));
            // we do this so the above datetimepicker call doesn't strip the time from the pre-set value in the text box.
            toDateTextBox.val(toValue);
          }
        });
        $('#dtpickerto').datetimepicker({
          showOn: 'button',
          buttonImage: 'images/16/date.png',
          buttonImageOnly: true,
          dateFormat: 'yy-mm-dd',
          hourGrid: 4,
          minuteGrid: 10,
          maxDate: 0,
          onClose: function(dateText, inst) {
            var startDateTextBox = $('#dtpickerfrom');
            if (startDateTextBox.val() != '') {
              var testStartDate = new Date(startDateTextBox.val());
              var testEndDate = new Date(dateText);
                if (testStartDate > testEndDate)
                  startDateTextBox.val(dateText);
            }
            else {
              startDateTextBox.val(dateText);
            }
          },
          onSelect: function (selectedDateTime) {
            var fromDateTextBox = $('#dtpickerfrom');
            var fromValue = fromDateTextBox.val();
            var end = $(this).datetimepicker('getDate');
            fromDateTextBox.datetimepicker('option', 'maxDate', new Date(end.getTime()) );
            // we do this so the above datetimepicker call doesn't strip the time from the pre-set value in the text box.
            fromDateTextBox.val(fromValue);
          }
        });
      });

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
    <style type='text/css'>
      /* css for timepicker */
      .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
      .ui-timepicker-div dl { text-align: left; }
      .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
      .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
      .ui-timepicker-div td { font-size: 90%; }
      .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
    </style>
  ");

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
  echo("
    <form id='customrange' action=\"test\">
    <p>
  ");
  echo("<input type=hidden id='selfaction' value='" . $_SERVER['PHP_SELF'] . "'>");
  echo("
    <strong>From:</strong> <input type='text' id='dtpickerfrom' maxlength=16 value='" . date('Y-m-d H:i', $graph_array['from']) . "'>
    <strong>To:</strong> <input type='text' id='dtpickerto' maxlength=16 value='" . date('Y-m-d H:i', $graph_array['to']) . "'>
    <input type='submit' id='submit' value='Update' onclick='javascript:submitCustomRange(this.form);'>
    </p>
    </form>
  ");

  echo("<hr />");

  if ($vars['legend'] == "no")
  {
    echo(generate_link("Show Legend",$vars, array('page' => "graphs", 'legend' => NULL)));
  } else {
    echo(generate_link("Hide Legend",$vars, array('page' => "graphs", 'legend' => "no")));
  }

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
