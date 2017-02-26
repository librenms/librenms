<html>
<body>

<?php
$_graphfilename = 'csim_in_html_graph_ex1.php';
// This is the filename of this HTML file
global $_wrapperfilename;
$_wrapperfilename = basename (__FILE__);

// Create a random mapname used to connect the image map with the image
$_mapname = '__mapname'.rand(0,1000000).'__';

// This is the first graph script
require_once ($_graphfilename);

// This line gets the image map and inserts it on the page
$imgmap = $graph->GetHTMLImageMap($_mapname);
echo $imgmap;

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
?>

<p>First we need to get hold of the image map and include it in the HTML
  page.</p>
<p>For this graph it is:</p>
<?php

// The we display the image map as well
echo '<pre><b>'.htmlentities($imgmap).'</b></pre>';?>

<?php
// Construct the <img> tag and rebuild the
$imgtag = $graph->GetCSIMImgHTML($_mapname,$_graphfilename);
?>
<p>The graph is then be displayed as shown in figure 1. With the following
  created &lt;img> tag:</p>
<pre><b>
<?php echo htmlentities($imgtag); ?>
</b></pre>


<p>
<?php
echo $imgtag;
?>
<br><b>Figure 1. </b>The included CSIM graph.
</p>

</body>
</html>
