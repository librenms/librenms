<!DOCTYPE html>
<html>
<head>
        <title>{{$title}}</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="/css/styles.css?ver=20191124" rel="stylesheet" type="text/css">
        <link href="/css/jquery.bootgrid.min.css" rel="stylesheet" type="text/css">

	<style>
           @page {
                margin: 0cm 0cm;
            }

            /** Define now the real margins of every page in the PDF **/
            body {
                margin-top: 2cm;
                margin-left: 2cm;
                margin-right: 2cm;
                margin-bottom: 2cm;
            }

            /** Define the header rules **/
            header {
                position: fixed;
                top: 0cm;
                left: 0cm;
                right: 0cm;
                height: 2cm;

                /** Extra personal styles **/
                text-align: left;
            }

            /** Define the footer rules **/
            footer {
                position: fixed; 
                bottom: 0cm; 
                left: 0cm; 
                right: 0cm;
                height: 2cm;

                /** Extra personal styles **/
                background-color: #03a9f4;
                color: white;
                text-align: center;
                line-height: 1.5cm;
            }
	</style>
</head>
<body>
	<header>
		<img src="{{ '/images/custom/mt_logo_blue.svg' }}" alt="header" style="width: 100%;">
            Our Code World
        </header>
	<main>
<br>
<div class="container-fluid">
        <div class="row">
                        <div class="panel panel-default panel-condensed">
                                <div class="table-responsive">
                                        <table class="table table-hover bootgrid-table" id="bills-list" aria-busy="false">
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
</div>
	</main>
</body>
</html>

