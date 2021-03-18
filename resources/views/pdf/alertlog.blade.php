<!DOCTYPE HTML>
<html >
<head>
	<title>{{ $pagetitle }}</title>
	<base href="{{ LibreNMS\Config::get('base_url') }}" />
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('css/jquery.bootgrid.min.css') }}" rel="stylesheet" type="text/css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
<div class="container-fluid">

	<div class="table-responsive">
		<table id="alertlog" class="table table-hover bootgrid-table table-striped">
			<thead>
				<tr>
					<th width="30px"  data-column-id="status" class="text-left" style=""><span class="text">@lang('State')</span></th>
					<th width="150px" data-column-id="time_logged" class="text-left" style=""><span class="text">@lang('Timestamp')</span></th>
					<th width="350px" data-column-id="hostname" class="text-left" style=""><span class="text">@lang('Device')</span></th>
					<th width="200px" data-column-id="alert" class="text-left" style=""><span class="text">@lang('Alert')</span></th>
					<th width="50px" data-column-id="severity" class="text-left" style=""><span class="text">@lang('Severity')</span></th>
				</tr>
			</thead>

			<tbody id="authlog_rows">
@foreach($json as $alert)
				<tr data-row-id="0">
					<td class="text-left col-lg-1" style="">
					@if ($alert->state === 1)
					<i class="alert-status label-danger" title="active"></i>
					@else
					<i class="alert-status label-success" title="recovered"></i>						
					@endif
					</td>
					<td class="text-left" style="">{{ $alert->time_logged }}</td>
					<td class="text-left col-lg-4 col-md-4 col-sm-4 col-xs-4" style="">
						<div class="incident">{{ $alert->sysName }}
							<div id="incident1" >{!! $alert->faultDetails !!}</div>
						</div>
					</td>
					<td class="text-left" style="">{{ $alert->alert }}</td>
					<td class="text-left" style="">{{ $alert->severity }}</td>
				</tr>

@endforeach
			</tbody>
		</table>
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
				</table>
			</div>
		</div>
	</footer>





<style>
	#manage-authlog-panel .panel-title { font-size: 18px; }
</style>

</body>
</html>