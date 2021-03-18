<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use LibreNMS\Config;
use Illuminate\Support\Facades\Route;
use LibreNMS\Util\DynamicConfig;
use Auth;
use App\Models\AuthLog;
use App\Models\AlertLog;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertLogs;
use LibreNMS\Reporting\Bills;

use Illuminate\Support\Facades\App;

class PDFController extends Controller
{
	public function preview(DynamicConfig $config, $path, Request $request){
		$clean_path = \LibreNMS\Util\Clean::fileName($path);
		$view 	= 'pdf.' . $clean_path;
		
		// this is the URL used in the button "Create PDF" - probably can be done another way but this works.... 
		$route	= '/pdf/generate/'. $clean_path;

		$header_logo = \LibreNMS\Config::get('dompdf.header_logo');
		$footer_logo = \LibreNMS\Config::get('dompdf.footer_logo');

		$params = array(
			"period" 		=> 'prev', 
			"bill_type" 	=> '', 
			"state" 		=> '',
			"searchPhrase"	=> '',
			"rowCount"		=> 50,
			"current"		=> 1
		);

		// Define Class based on route 
		$className = 'LibreNMS\\Reporting\\' . ucfirst($clean_path);
		

		// Validate that the view exists 
		if (view()->exists($view)) {
			$Report = new $className;
			// Build list - list is returned in response as json - $response['json']
			$repsonse	= $Report->get_list($params);
			$params['total'] = $repsonse['total'];
			
			// iterate through json list and expand links, add formating etc.
			$json	= $Report->expand_list($repsonse['json'], $params);
			$baseURL = \LibreNMS\Config::get('base_url');
			
			$data = [
				'path'			=> $clean_path,
				'route'			=> $route,
				'date'			=> date('d/m/Y'),
				'pagetitle'		=> \LibreNMS\Config::get('pdf.header_text'),
				'json'			=> json_decode($json),
				'baseURL'		=> $baseURL,
				'header_logo'	=> \LibreNMS\Config::get('pdf.header_logo'),
				'header_text'	=> \LibreNMS\Config::get('pdf.header_text'),
				'footer_logo'	=> \LibreNMS\Config::get('pdf.footer_logo'),
				'owner'			=> \LibreNMS\Config::get('pdf.doc_owner'),
				'level'			=> \LibreNMS\Config::get('pdf.doc_level')
			];
			//display Preview of report using blade template
			 return view($view, $data);
		} else {
			 abort(404);
		}
	}

	public function generate(DynamicConfig $config, $path){
		$clean_path = \LibreNMS\Util\Clean::fileName($path);
		$view =  'pdf.'. $clean_path;
		
		$header_logo = \LibreNMS\Config::get('dompdf.header_logo');
		$footer_logo = \LibreNMS\Config::get('dompdf.footer_logo');

		$params = array(
			"period" 		=> 'prev', 
			"bill_type" 	=> '', 
			"state" 		=> '',
			"searchPhrase"	=> '',
			"rowCount"		=> 50,
			"current"		=> 1
		);

		// Define Class based on route 
		$className = 'LibreNMS\\Reporting\\' . ucfirst($clean_path);
		
		
		// Validate that the view exists 
		if (view()->exists($view)) {
			$Report = new $className;
			// Build list - list is returned in response as json - $response['json']
			$repsonse	= $Report->get_list($params);
			$params['total'] = $repsonse['total'];
			
			// iterate through json list and expand links, add formating etc.
			$json	= $Report->expand_list($repsonse['json'], $params);
			$data = [
				'path' 	=> $clean_path,
				'date'      => date('d/m/Y'),
				'json'      => json_decode($json),
				'pagetitle' => \LibreNMS\Config::get('pdf.header_text'),
				'baseURL'		=> \LibreNMS\Config::get('base_url'),
				'header_logo' => \LibreNMS\Config::get('pdf.header_logo'),
				'header_text' => \LibreNMS\Config::get('pdf.header_text'),
				'footer_logo' => \LibreNMS\Config::get('pdf.footer_logo'),
				'owner'		  => \LibreNMS\Config::get('pdf.doc_owner'),
				'level'		  => \LibreNMS\Config::get('pdf.doc_level')
			];
			
			// generate PDF document from blade template and download to browser
			$download_pdf = $view . "_download"; 
			$pdf = PDF::loadView($download_pdf, $data)->setPaper('a4', 'landscape');
			$filename	= "bill-report_" .date('d-m-Y').".pdf";
			return $pdf->download($filename);
		} else {
			abort(404);
		}
	}

	public function Alerts(Request $request){

		$device_id 	= $request->input('device_id');
		$string		= $request->input('string'); 
		$results	= $request->input('results');
		$start		= $request->input('start');
		$report		= $request->input('report');
		$rule_id	= $request->input('rule_id');

		if (isset($results) && is_numeric($results)) {
			$numresults = $results;
		} else {
			$numresults = 250;
		}
		
		// Build Base SQL Query
		$date_format = \LibreNMS\Config::get('dateformat.mysql.compact');
		$query = AlertLog::query();
		$query = $query->select('R.severity', 'D.device_id','D.sysName', 'name AS alert','rule_id','state','time_logged', DB::raw('DATE_FORMAT(time_logged, "'.$date_format.'") as humandate'))
			->from('alert_log as E')
			->leftJoin('devices as D', 'E.device_id', '=', 'D.device_id')
			->rightJoin('alert_rules as R', 'E.rule_id', '=', 'R.id');

		//json_decode(gzuncompress($id['details']), true);
		// adding by device
		if (is_numeric($device_id)) {
			$query = $query->where('E.device_id', '=', $device_id);
		}
		// adding by rule
		if ($string) {
			$query = $query->where('R.rule','LIKE', $string);
		}
		// adding based on auth
		if (! Auth::user()->hasGlobalRead()) {
			$query = $query->rightJoin('devices_perms AS P', 'E.device_id', '=', 'P.device_id');
		}

		// adding orderby
		if (! isset($sort) || empty($sort)) {
			$query = $query->orderBy('time_logged', 'DESC');
		}else {
			$query = $query->orderBy($sort);
		}

		//adding Limits and finally get results
		$query = $query->offset($start)
			->limit($numresults)
			->get();

		// probably a better solution to this but it works.....
		$json = json_decode($query->toJson());

		$params = array(
			"rule_id" 		=> $rule_id, 
			"device_id" 	=> $device_id, 
			"show_links"	=> false
		);
		$className = 'LibreNMS\\Alert\\AlertLogs';
		$AlertLog = new $className;
		
		foreach($json as $key => $alert) {
			$params = array(
				"rule_id" 		=> $alert->rule_id, 
				"device_id" 	=> $alert->device_id, 
				"show_links"	=> false
			);
			$fault_detail = $AlertLog->alert_details($params);
			$json[$key]->faultDetails = $fault_detail;
		}

		$data = [
			'date'			=> date('d/m/Y'),
			'json'			=> $json,
			'pagetitle'		=> 'Alert Logs',
			'header_logo'	=> \LibreNMS\Config::get('pdf.header_logo'),
			'baseURL'		=> \LibreNMS\Config::get('base_url'),
			'header_text'	=> \LibreNMS\Config::get('pdf.header_text'),
			'footer_logo'	=> \LibreNMS\Config::get('pdf.footer_logo'),
			'owner'			=> \LibreNMS\Config::get('pdf.doc_owner'),
			'level'			=> \LibreNMS\Config::get('pdf.doc_level')
		];
		
		$returnHTML = view('pdf.alertlog')->with($data)->render();
		$pdf = PDF::loadView('pdf.alertlog', $data)->setPaper('a4', 'landscape');
		return $pdf->stream();
	}

	public function Getalerts(Request $request){
		$device_id 	= $request->input('device_id');
		$string		= $request->input('string'); 
		$results	= $request->input('results');
		$start		= $request->input('start');
		$report		= $request->input('report');

		if (isset($results) && is_numeric($results)) {
			$numresults = $results;
		} else {
			$numresults = 250;
		}

		// Build Base SQL Query
		$date_format = \LibreNMS\Config::get('dateformat.mysql.compact');
		$query = AlertLog::query();
		$query = $query->select('R.severity', 'D.device_id','D.sysName', 'name AS alert','rule_id','state','time_logged', DB::raw('DATE_FORMAT(time_logged, "'.$date_format.'") as humandate'))
			->from('alert_log as E')
			->leftJoin('devices as D', 'E.device_id', '=', 'D.device_id')
			->rightJoin('alert_rules as R', 'E.rule_id', '=', 'R.id');

		//json_decode(gzuncompress($id['details']), true);
		// adding by device
		if (is_numeric($device_id)) {
			$query = $query->where('E.device_id', '=', $device_id);
		}
		// adding by rule
		if ($string) {
			$query = $query->where('R.rule','LIKE', $string);
		}
		// adding based on auth
		if (! Auth::user()->hasGlobalRead()) {
			$query = $query->rightJoin('devices_perms AS P', 'E.device_id', '=', 'P.device_id');
		}

		// adding orderby
		if (! isset($sort) || empty($sort)) {
			$query = $query->orderBy('time_logged', 'DESC');
		}else {
			$query = $query->orderBy($sort);
		}

		//adding Limits and finally get results
		$query = $query->offset($start)
			->limit($numresults)
			->get();

		// probably a better solution to this but it works.....
		$json = json_decode($query->toJson());
		$show_links = false;

		$className = 'LibreNMS\\Alert\\AlertLogs';
		$AlertLog = new $className;
		
		foreach($json as $key => $alert) {
			$params = array(
				"rule_id" 		=> $alert->rule_id, 
				"device_id" 	=> $alert->device_id, 
				"show_links"	=> false
			);
			$fault_detail = $AlertLog->alert_details($params);
			$json[$key]->faultDetails = $fault_detail;
		}

		$data = [
			'date'			=> date('d/m/Y'),
			'json'			=> $json,
			'pagetitle'			=> 'Alert Logs',
			'baseURL'		=> \LibreNMS\Config::get('base_url'),
			'header_logo'	=> \LibreNMS\Config::get('pdf.header_logo'),
			'header_text'	=> \LibreNMS\Config::get('pdf.header_text'),
			'footer_logo'	=> \LibreNMS\Config::get('pdf.footer_logo'),
			'owner'			=> \LibreNMS\Config::get('pdf.doc_owner'),
			'level'			=> \LibreNMS\Config::get('pdf.doc_level')
		];
		$returnHTML = view('pdf.alertlog')->with($data)->render();
		$pdf = PDF::loadView('pdf.alertlog', $data)->setPaper('a4', 'landscape');
		return $pdf->stream();
	}

	
}
