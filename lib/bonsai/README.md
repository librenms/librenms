graphite-zoom-js
================

JS to zoom Graphite graphs

So you have installed graphite, got metrics and you're staring at a graph.
But what's that small dip there?  You need to zoom!

Graphite'll happily render graphs at different times and cacti has a bit of
Javascript to zoom graphs so I just took the cacti JS and made it parse
things like `from=-2hours` and graphite style dates like `from=00:00_20120617`

The only subtlety is that the cacti script only worked on one graph per
page.  So I hacked it to work with many graphs.  At <a
href="http://www.guardian.co.uk">the Guardian</a> we have an id of
the md5sum of the graph as the `img` tag id and then a button under every
graph that calls `initBonsai` with the right md5sum for the graph
above it.  This is horrible.  There must be a better way but I haven't spent
any time finding it yet.
<br>Pull requests welcome :)

# Usage

1. Load the bonsai.js however you choose.
1. Add this to your HTML:

```
<!-- Add the zoomBox for funky graph zooming -->
<div id='zoomBox' style='position:absolute; overflow:hidden; left:0px;
top:0px; width:0px; height:0px; visibility:visible; background:red;
filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity:0.5;
opacity:0.5'></div>
<div id='zoomSensitiveZone' style='position:absolute; overflow:hidden;
left:0px; top:0px; width:0px; height:0px; visibility:visible;
cursor:crosshair; background:blue; filter:alpha(opacity=0); -moz-opacity:0;
-khtml-opacity:0; opacity:0' oncontextmenu='return false'></div>
<STYLE MEDIA="print">
/*Turn off the zoomBox*/
div#zoomBox, div#zoomSensitiveZone {display: none}
/*This keeps IE from cutting things off*/
#why {position: static; width: auto}
</STYLE>
```

1. Add your graphs something like:

```
    <span class="graphiteGraph">
    <img
    id="317330dc6337227faa5df8dd149c344b" <-- I use md5sum of the graphite URL here
    src="http://yourgraphite.host/render?blahblah"><input type="submit" value="Zoom me" onClick="initBonsai('317330dc6337227faa5df8dd149c344b')"></span>
```

1. To use it as a user, click zoom me
1. Then when you wave your mouse over the graph it should turn into a
crosshair
1. Click and drag to zoom
1. Right click to zoom out

# Demo

There's a <a
href="http://the.earth.li/~huggie/graphite-zoom-template.html">demo that you
can use with your own graphs</a> too.

# Contact, Feedback, promises of beer

huggie@earth, shufgy on twitter, huggie on freenode in #graphite

# Todo

1. Do multiple graphs on one page without Zoom Me buttons.  (mouseover runs
initBonsai maybe?)
1. Clean the code up to be able to work with both cacti and graphite
1. Submit upstream to cacti perhaps if they want it
1. Submit upstream to graphite

