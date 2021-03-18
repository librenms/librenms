<!DOCTYPE html>
<html>
<head>
	<title>{{$pagetitle}}</title>
	<base href="{!! $baseURL !!}" />
	<link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="/css/styles.css" rel="stylesheet" type="text/css">
	<link href="/css/jquery.bootgrid.min.css" rel="stylesheet" type="text/css">


	<style>
	   @page {
			margin: 0px 20px;
		}

		/** Define now the real margins of every page in the PDF **/
		body {
			margin-top: 10px;
			margin-left: -5px;
			margin-right: -5px;
			margin-bottom: 2cm;
		}
	
		/** Define the header rules **/
		header {
			position: fixed;
			top: 0cm;
			left: 0cm;
			right: 0cm;
			height: 3cm;
			background-repeat:no-repeat;
			background-image:url("{{url('/') . $header_logo}}");
		}

		/** Define the footer rules **/
		footer {
			position: fixed; 
			bottom: 12px; 
			left: -25px; 
			right: 0cm;
			height: 2.5cm;
		}
		#footer {
			bottom: 0;
			left: -25px; 
			color:white;
			padding:4px;
			height: 2.5cm;
			background-repeat:no-repeat;
			background-image:url("{{url('/') . $footer_logo}}");
		}
		.page-number:before {
			content: "Page " counter(page);
		}
		.borderless td, .borderless th {
			border: none;
			padding-top:5px;
		}
		.page-break {
			page-break-after: always;
			padding-top:5px;
		}


		/* Styling an indeterminate progress bar */

		progress:not(value) {
		  /* Add your styles here. As part of this walkthrough we will focus only on determinate progress bars. */
		}

		/* Styling the determinate progress element */

		progress[value] {
			/* Get rid of the default appearance */
			appearance: none;

			/* This unfortunately leaves a trail of border behind in Firefox and Opera. We can remove that by setting the border to none. */
			border: none;

			/* Add dimensions */
			width: 100%; height: 20px;

			/* Although firefox doesn't provide any additional pseudo class to style the progress element container, any style applied here works on the container. */
			background-color: whiteSmoke;
			border-radius: 3px;
			box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;

			/* Of all IE, only IE10 supports progress element that too partially. It only allows to change the background-color of the progress value using the 'color' attribute. */
			color: royalblue;

			position: relative;
			margin: 0 0 1.5em; 
		}

		/*
		Webkit browsers provide two pseudo classes that can be use to style HTML5 progress element.
		-webkit-progress-bar -> To style the progress element container
		-webkit-progress-value -> To style the progress element value.
		*/

		progress[value]::-webkit-progress-bar {
			background-color: whiteSmoke;
			border-radius: 3px;
			box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;
		}

		progress[value]::-webkit-progress-value {
			position: relative;

			background-size: 35px 20px, 100% 100%, 100% 100%;
			border-radius:3px;

			/* Let's animate this */
			animation: animate-stripes 5s linear infinite;
		}

		@keyframes animate-stripes { 100% { background-position: -100px 0; } }

		/* Let's spice up things little bit by using pseudo elements. */

		progress[value]::-webkit-progress-value:after {
			/* Only webkit/blink browsers understand pseudo elements on pseudo classes. A rare phenomenon! */
			content: '';
			position: absolute;

			width:5px; height:5px;
			top:7px; right:7px;

			background-color: white;
			border-radius: 100%;
		}

		/* Firefox provides a single pseudo class to style the progress element value and not for container. -moz-progress-bar */

		progress[value]::-moz-progress-bar {
			/* Gradient background with Stripes */
			background-image:
			-moz-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-moz-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -moz-linear-gradient( left, #09c, #f44);
		
			background-size: 35px 20px, 100% 100%, 100% 100%;
			border-radius:3px;
		
			/* Firefox doesn't support CSS3 keyframe animations on progress element. Hence, we did not include animate-stripes in this code block */
		}

		/* Fallback technique styles */
		.progress-bar {
			background-color: whiteSmoke;
			border-radius: 3px;
			box-shadow: 0 2px 3px rgba(0,0,0,.5) inset;

			/* Dimensions should be similar to the parent progress element. */
			width: 100%; height:20px;
		}

		.progress-bar span {
			background-color: royalblue;
			border-radius: 3px;
			display: block;
			text-indent: -9999px;
		}

		p[data-value] { 
			position: relative; 
		}

		/* The percentage will automatically fall in place as soon as we make the width fluid. Now making widths fluid. */

		p[data-value]:after {
			content: attr(data-value) '%';
			position: absolute; left:50px;
		}





		.html5::-webkit-progress-value,
		.python::-webkit-progress-value  {
			/* Gradient background with Stripes */
			background-image:
			-webkit-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-webkit-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -webkit-linear-gradient( left, #09c, #f44);
		}

		.css3::-webkit-progress-value,
		.php::-webkit-progress-value 
		{
			/* Gradient background with Stripes */
			background-image:
			-webkit-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-webkit-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -webkit-linear-gradient( left, #09c, #ff0);
		}

		.jquery::-webkit-progress-value,
		.node-js::-webkit-progress-value 
		{
			/* Gradient background with Stripes */
			background-image:
			-webkit-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-webkit-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -webkit-linear-gradient( left, #09c, #690);
		}

		/* Similarly, for Mozillaa. Unfortunately combining the styles for different browsers will break every other browser. Hence, we need a separate block. */

		.html5::-moz-progress-bar,
		.php::-moz-progress-bar {
			/* Gradient background with Stripes */
			background-image:
			-moz-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-moz-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -moz-linear-gradient( left, #09c, #f44);
		}

		.css3::-moz-progress-bar,
		.php::-moz-progress-bar {
		{
			/* Gradient background with Stripes */
			background-image:
			-moz-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-moz-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -moz-linear-gradient( left, #09c, #ff0);
		}

		.jquery::-moz-progress-bar,
		.node-js::-moz-progress-bar {
			/* Gradient background with Stripes */
			background-image:
			-moz-linear-gradient( 135deg,
				transparent,
				transparent 33%,
				rgba(0,0,0,.1) 33%,
				rgba(0,0,0,.1) 66%,
				transparent 66%),
			-moz-linear-gradient( top,
				rgba(255, 255, 255, .25),
				rgba(0,0,0,.2)),
			 -moz-linear-gradient( left, #09c, #690);
		}

	</style>
</head>
<body>
	<header>
		<div id="header">
			<div class="container-fluid">
				<table class="table bootgrid-table">
					<tr>
						<td id="header_text" class="text-center" width="100%">{{ $header_text }}</td>
					</tr>
				</table>
			</div>
		</div>
	</header>

<br>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default panel-condensed">

				<div class="table-responsive">
					@include('pdf.tableHeader')
						@foreach($json as $index => $row)
							<tr data-row-id="$index" >
							@foreach ($row as $key => $value)
								<?php if(property_exists($row, $key)) : ?>
									<td class="text-left" style="">{!! $value !!}</td>
								<?php else : ?>
									<td class="text-left" style=""></td>
								<?php endif; ?>
  							@endforeach
							@if( $index > 0 && $index % 5 == 0)
							</tr>
						</tbody>
					</table>
					<div class="page-break"></div>
					@include('pdf.tableHeader')
							@else
							</tr>
							@endif
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
	</div>
</div>
	<footer>
		<div id="footer">
			<div class="container-fluid">
				<table class="borderless">
					<tr>
						<td id="Footer_date" class="text-left" width="100">{{ date('d-m-Y') }}</td>
						<td id="Footer_owner" class="text-left" width="150">{{ $owner }}</td>
						<td id="Footer_security_Level" class="text-left" width="150">{{ $level }}</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="4"><div class="page-number"></div></td>
					</tr>
				</table>
			</div>
		</div>
	</footer>	
</body>
</html>

