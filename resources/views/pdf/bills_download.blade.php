<!DOCTYPE html>
<html>
<head>
	<title>{{$title}}</title>
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('css/jquery.bootgrid.min.css') }}" rel="stylesheet" type="text/css">

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
				<table class="table table-hover bootgrid-table">
					<tr>
						<td id="header_text" class="text-center" width="100%">{{ $header_text }}</td>
					</tr>
				</table>
			</div>
		</div>
	</header>

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
<br>
<div class="container-fluid">
	<div class="row">
		<div class="panel panel-default panel-condensed">
			<div class="table-responsive">
				<table class="table table-hover bootgrid-table" id="bills-list" aria-busy="false" >
					<thead>
						<tr>
							<th data-column-id="bill_name" class="text-left" style="">Billing name</th>
							<th data-column-id="notes" class="text-left" style=""></th>
							<th data-column-id="bill_type" class="text-left" style="">Type</th>
							<th data-column-id="bill_allowed" class="text-left" style="">Allowed</th>
							<th data-column-id="total_data_in" class="text-left" style="">Inbound</th>
							<th data-column-id="total_data_out" class="text-left" style="">Outbound</th>
							<th data-column-id="total_data" class="text-left" style="">Total</th>
							<th data-column-id="rate_95th" class="text-left" style="">95th Percentile</th>
							<th data-column-id="overusage" class="text-left" style="">Overusage</th>
							<th data-column-id="predicted" class="text-left" style="">Predicted</th>
						</tr>
					</thead>
					<tbody>

					@foreach($json as  $row)
						<tr data-row-id="0">
							<td class="text-left" style="">{!!$row->bill_name!!}</td>
							<td class="text-left" style="">{!!$row->notes!!}</td>
							<td class="text-left" style="">{!!$row->bill_type!!}</td>
							<td class="text-right" style="">{!!$row->bill_allowed!!}</td>
							<td class="text-right" style="">{!!$row->total_data_in!!}</td>
							<td class="text-right" style="">{!!$row->total_data_out!!}</td>
							<td class="text-right" style="">{!!$row->total_data!!}</td>
							<td class="text-right" style="">{!!$row->rate_95th!!}</td>
							<td class="text-center" style="">{!!$row->overusage!!}</td>
							<td class="text-center" style="">{!!$row->predicted!!}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
</body>
</html>

