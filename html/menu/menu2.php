<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml2/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<style type="text/css">
 /* common styling */
.menu2 {
float:left; width:100%; font-family: verdana, arial, sans-serif; font-size:11px; border:1px solid #aaaaaa;
background:#ddd url(images/menu2.gif) repeat-x; 
}
.menu2 ul {
padding:0;margin:0;list-style-type:none;
}
.menu2 ul li {
float:left; position:relative;
}
.menu2 ul li.group {display:block; text-indent:10px; background:#666; color:#ff0; width:152px; padding:4px 0;}

.menu2 ul li a, .menu2 ul li a:visited {
float:left; display:block; text-decoration:none; color:#444; padding:0px 16px; line-height:30px; height:30px;
}

.menu2 ul li:hover {width:auto;}

.menu2 ul li a:hover {background:#aaa url(images/sub2a.gif); color:#356AA0;}

.menu2 ul li:hover a {background:#aaa url(images/sub2a.gif); color:#356AA0;}

.menu2 ul li ul {
display: none;
}
.menu2 table {
margin:0; border-collapse:collapse; font-size:11px; position:absolute; top:0; left:0;
}

/* specific to non IE browsers */
.menu2 ul li:hover ul {
display:block; position:absolute;top:29px; background:#aaa; margin-top:1px; left:0; width:140px;
}

.menu2 ul li:hover ul.scroller {
height:138px; width:172px; overflow:auto;}

.menu2 ul li:hover ul.endstop {
left:-90px;
}
.menu2 ul li:hover ul li ul {
display: none;
}
.menu2 ul li:hover ul li a {
display:block; background:#aaa; color:#fff;height:auto;line-height:15px;padding:4px 10px; width:120px;
}
.menu2 ul li:hover ul li a.drop {
background:#888;
}
.menu2 ul li:hover ul li a:hover {
color:#ff0; background: #888;
}
.menu2 ul li:hover ul li a:hover.drop {
background: #888; color:#ff0;
}
.menu2 ul li:hover ul li:hover ul {
display:block; position:absolute; left:153px; top:-70px; color:#000; left:152px; height:138px; width:172px; overflow:auto; background:#888;
}
.menu2 ul li:hover ul li:hover ul li a {background:#888;}
.menu2 ul li:hover ul li:hover ul li.group {width:152px; padding:5px 0;}


.menu2 ul li:hover ul li:hover ul.left {
left:-172px;
}
.menu2 ul li:hover ul li:hover ul li a:hover {background:#666; color:#0ff;}

/* specific to IE5.5 and IE6 browsers */
.menu2 ul li a:hover ul {
display:block;position:absolute;top:30px; t\op:29px; background:#aaa;left:0; marg\in-top:1px;
}
.menu2 ul li a:hover ul.scroller {
height:138px; overflow:auto;}

.menu2 ul li a:hover ul.endstop {
left: -90px;
}
.menu2 ul li a:hover ul li a {
display:block; color:#fff; height:1px; line-height:15px; padding:4px 16px; width:152px; w\idth:120px;
}
.menu2 ul li a:hover ul li a.drop {
background:#888; padding-bottom:5px;
}
.menu2 ul li a:hover ul li a ul {
visibility:hidden; position:absolute; height:0; width:0;
}
.menu2 ul li a:hover ul li a:hover {
color:#ff0; background: #888;
}
.menu2 ul li a:hover ul li a:hover.drop {
 background: #888; color:#ff0;
}
.menu2 ul li a:hover ul li a:hover ul {
visibility:visible; position:absolute; top:-69px; t\op:-70px; color:#000; left:152px; height:138px; width:172px; overflow:auto; background:#888;
}
.menu2 ul li a:hover ul li a:hover ul.left { left:-170px; }

.menu2 ul li a:hover ul li a:hover ul li a:hover {background:#666; color:#0ff;}

.left {clear:both;}

</style>

</head>
<body>

<div class="menu2">
<ul>
<li><a href="/"><img src='http://demo.project-observer.org/images/16/lightning.png' border=0 align=absmiddle> Status
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
        <li><a href="?page=eventlog"><img src='http://demo.project-observer.org/images/16/information.png' border=0 align=absmiddle> Eventlog</a></li>
        <li><a href="?page=alerts"><img src='http://demo.project-observer.org/images/16/exclamation.png' border=0 align=absmiddle> Alerts</a></li>
        </ul>
        </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->

</li>
</ul>
<ul>
<li><a href="?page=devices"><img src='http://demo.project-observer.org/images/16/server.png' border=0 align=absmiddle> Devices
<!--[if IE 7]><!--></a><!--<![endif]-->
	<table><tr><td>
	<ul>
	<li><a href="?page=devices&type=server"><img src='http://demo.project-observer.org/images/16/server.png' border=0 align=absmiddle> Servers</a></li>
	<li><a href="?page=devices&type=network"><img src='http://demo.project-observer.org/images/16/arrow_switch.png' border=0 align=absmiddle> Network</a></li>
	<li><a href="?page=devices&type=firewall"><img src='http://demo.project-observer.org/images/16/shield.png' border=0 align=absmiddle> Firewalls</a></li>
        <li><hr width=140 /></li>
	<li><a href="?page=devices&status=0"><img src='http://demo.project-observer.org/images/16/server_error.png' border=0 align=absmiddle> Alerted Devices</a></li>
        <li><hr width=140 /></li>
        <li><a href="?page=addhost"><img src='http://demo.project-observer.org/images/16/server_add.png' border=0 align=absmiddle> Add Device</a></li>
        <li><a href="?page=delhost"><img src='http://demo.project-observer.org/images/16/server_delete.png' border=0 align=absmiddle> Delete Device</a></li>
	</ul>
	</td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>
<li><a href="?page=services"><img src='http://demo.project-observer.org/images/16/cog.png' border=0 align=absmiddle> Services
<!--[if IE 7]><!--></a><!--<![endif]-->
	<table><tr><td>
	<ul>
	<li><a href="?page=services&status=0"><img src='http://demo.project-observer.org/images/16/cog_error.png' border=0 align=absmiddle> Down Services</a></li>
	</ul>
	</td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>
<li><a class="menu2four" href="?page=locations"><img src='http://demo.project-observer.org/images/16/building.png' border=0 align=absmiddle> Locations</a></li>
<li style="float: right;"><a href="?page=configuration"><img src='http://demo.project-observer.org/images/16/wrench.png' border=0 align=absmiddle> Configuration</a></li>
</ul>
</div>
</body>
