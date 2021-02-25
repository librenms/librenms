<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DOMPDF;
use LibreNMS\Config;
use Illuminate\Support\Facades\Route;
use LibreNMS\Util\DynamicConfig;

require '/opt/librenms/includes/init.php';
require_once '/opt/librenms/includes/html/functions.inc.php';

class PDFController extends Controller
{
	public function preview(DynamicConfig $config, $path, Request $request){
		$clean_path = \LibreNMS\Util\Clean::fileName($path);
		$view 	= 'pdf.' . $clean_path;
		
		// this is the URL used in the button "Create PDF" - probably can be done another way but this works.... 
		$route	= '/dompdf/generate/'. $clean_path;

		$header_logo = \LibreNMS\Config::get('dompdf.header_logo');
		$footer_logo = \LibreNMS\Config::get('dompdf.footer_logo');
		
		// Validate that the view exists 
		if (view()->exists($view)) {
			// Run report and return JSON object - saved to $json
			include_once "/opt/librenms/includes/html/reports/$clean_path.pdf.inc.php";
			
			$data = [
				'path'      => $clean_path,
				'route'     => $route,
				'date'      => date('d/m/Y'),
				'json'      => json_decode($json),
				'header_logo' => \LibreNMS\Config::get('dompdf.header_logo'),
				'header_text' => \LibreNMS\Config::get('dompdf.header_text'),
				'footer_logo' => \LibreNMS\Config::get('dompdf.footer_logo'),
				'owner'		  => \LibreNMS\Config::get('dompdf.doc_owner'),
				'level'		  => \LibreNMS\Config::get('dompdf.doc_level')
			];
			// display Preview of report using blade template
			return view($view, $data);
		} else {
			 abort(404);
		}
	}

	public function generate(DynamicConfig $config, $path){
		$clean_path = \LibreNMS\Util\Clean::fileName($path);
		$view =  'pdf.'. $clean_path;
		
		$footer_logo = \LibreNMS\Config::get('dompdf.footer_logo');
		
		// Validate that the view exists 
		if (view()->exists($view)) {
			// Run report and return JSON object - saved to $json
			include_once "/opt/librenms/includes/html/reports/$clean_path.pdf.inc.php";
			
			$data = [
				'path' 	=> $clean_path,
				'date'      => date('d/m/Y'),
				'json'      => json_decode($json),
				'header_logo' => \LibreNMS\Config::get('dompdf.header_logo'),
				'header_text' => \LibreNMS\Config::get('dompdf.header_text'),
				'footer_logo' => \LibreNMS\Config::get('dompdf.footer_logo'),
				'owner'		  => \LibreNMS\Config::get('dompdf.doc_owner'),
				'level'		  => \LibreNMS\Config::get('dompdf.doc_level')
			];

			// generate PDF document from blade template and download to browser
			$download_pdf = $view . "_download"; 
			$pdf = DOMPDF::loadView($download_pdf, $data)->setPaper('a4', 'landscape');
			$filename	= "bill-report_" .date('d-m-Y').".pdf";
			return $pdf->download($filename);
		} else {
			abort(404);
		}
	}

}
