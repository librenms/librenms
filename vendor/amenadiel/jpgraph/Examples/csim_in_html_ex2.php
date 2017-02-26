<html>
<body>

<?php
// The names of the graph scripts
$_graphfilename1 = 'csim_in_html_graph_ex1.php';
$_graphfilename2 = 'csim_in_html_graph_ex2.php';

// This is the filename of this HTML file
global $_wrapperfilename;
$_wrapperfilename = basename (__FILE__);

// Create a random mapname used to connect the image map with the image
$_mapname1 = '__mapname'.rand(0,1000000).'__';
$_mapname2 = '__mapname'.rand(0,1000000).'__';

// Get the graph scripts
require_once ($_graphfilename1);
require_once ($_graphfilename2);

// This line gets the image map and inserts it on the page
$imgmap1 = $graph->GetHTMLImageMap($_mapname1);
$imgmap2 = $piegraph->GetHTMLImageMap($_mapname2);
echo $imgmap1;
echo $imgmap2;

?>

<h2>This is an example page with CSIM graphs with arbitrary HTML text</h2>

<?php
if( empty($_GET['clickedon']) ) {
   echo '<b style="color:darkred;">Clicked on bar: &lt;none></b>';
}
else {
   echo '<b style="color:darkred;">Clicked on bar: '.$_GET['clickedon'].'</b>';
}
echo '<p />';
if( empty($_GET['pie_clickedon']) ) {
   echo '<b style="color:darkred;">Clicked on pie slice: &lt;none></b>';
}
else {
   echo '<b style="color:darkred;">Clicked on pie slice: '.$_GET['pie_clickedon'].'</b>';
}
echo '<p />';
?>

<p>First we need to get hold of the image maps and include them in the HTML
  page.</p>
<p>For these graphs the maps are:</p>
<?php
// The we display the image map as well
echo '<small><pre>'.htmlentities($imgmap1).'</pre></small>';
?>
<p>
and
</p>
<?php
// The we display the image map as well
echo '<small><pre>'.htmlentities($imgmap2).'</pre></small>';
?>

<?php
// Construct the <img> tags for Figure 1 &amp; 2 and rebuild the URL arguments
$imgtag1 = $graph->GetCSIMImgHTML($_mapname1,$_graphfilename1);
$imgtag2 = $piegraph->GetCSIMImgHTML($_mapname2,$_graphfilename2);
?>
<p>The graphs are then displayed as shown in figure 1 &amp; 2. With the following
  created &lt;img> tags:</p>
<small><pre>
<?php 
echo htmlentities($imgtag1); 
echo htmlentities($imgtag2); 
?>
</pre></small>

<p>
Note: For the Pie the center is counted as the first slice.
</p>

<p>
<table border=0>
<tr><td valign="bottom">
<?php
echo $imgtag1;
?>
<br><b>Figure 1. </b>The included Bar CSIM graph.
</p>
</td>
<td valign="bottom">
<?php
echo $imgtag2;
?>
<br><b>Figure 2. </b>The included Pie CSIM graph.
</p>
</td>
</tr>
</table>
</body>
</html>
