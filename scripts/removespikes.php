#!/usr/bin/php -q
<?php
/*
 ex: set tabstop=4 shiftwidth=4 autoindent:
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die("<br><strong>This script is only meant to run at the command line.</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;

$dir = dirname(__FILE__);
chdir($dir);

if (strpos($dir, 'spikekill') !== false) {
	chdir('../../');
}

$using_cacti = false;

/* setup defaults */
$debug     = FALSE;
$dryrun    = FALSE;
$avgnan    = 'avg';
$rrdfile   = "";
$std_kills = TRUE;
$var_kills = TRUE;
$html      = FALSE;

if ($using_cacti) {
	$method   = read_config_option("spikekill_method");
	$numspike = read_config_option("spikekill_number");
	$stddev   = read_config_option("spikekill_deviations");
	$percent  = read_config_option("spikekill_percent");
	$outliers = read_config_option("spikekill_outliers");
}else{
	$method   = 1; // Standard Deviation
	$numspike = 10;
	$stddev   = 10;
	$percent  = 500;
	$outliers = 5;
}

/* process calling arguments */
$parms = $_SERVER["argv"];
array_shift($parms);

foreach($parms as $parameter) {
	@list($arg, $value) = @explode("=", $parameter);

	switch ($arg) {
	case "--method":
	case "-M":
		if ($value == "variance") {
			$method = 2;
		}elseif ($value == "stddev") {
			$method = 1;
		}else{
			echo "FATAL: You must specify either 'stddev' or 'variance' as methods.\n\n";
			display_help();
			exit;
		}

		break;
	case "--avgnan":
	case "-A":
		if ($value == "avg") {
			$avgnan = "avg";
		}elseif ($value == "nan") {
			$avgnan = "nan";
		}else{
			echo "FATAL: You must specify either 'avg' or 'nan' as replacement methods.\n\n";
			display_help();
			exit;
		}

		break;
	case "--rrdfile":
	case "-R":
		$rrdfile = $value;

		if (!file_exists($rrdfile)) {
			echo "FATAL: File '$rrdfile' does not exist.\n";
			exit;
		}

		if (!is_writable($rrdfile)) {
			echo "FATAL: File '$rrdfile' is not writable by this account.\n";
			exit;
		}

		break;
	case "--stddev":
	case "-S":
		$stddev = $value;

		if (!is_numeric($stddev) || ($stddev < 1)) {
			echo "FATAL: Standard Deviation must be a positive integer.\n\n";
			display_help();
			exit;
		}

		break;
	case "--outliers":
	case "-O":
		$outliers = $value;

		if (!is_numeric($outliers) || ($outliers < 1)) {
			echo "FATAL: The number of outliers to exlude must be a positive integer.\n\n";
			display_help();
			exit;
		}

		break;
	case "--percent":
	case "-P":
		$percent = $value/100;

		if (!is_numeric($percent) || ($percent <= 0)) {
			echo "FATAL: Percent deviation must be a positive floating point number.\n\n";
			display_help();
			exit;
		}

		break;
	case "--html":
		$html = TRUE;

		break;
	case "-d":
	case "--debug":
		$debug = TRUE;

		break;
	case "-D":
	case "--dryrun":
		$dryrun = TRUE;

		break;
	case "--number":
	case "-n":
		$numspike = $value;

		if (!is_numeric($numspike) || ($numspike < 1)) {
			echo "FATAL: Number of spikes to remove must be a positive integer\n\n";
			display_help();
			exit;
		}

		break;
	case "-h":
	case "-v":
	case "-V":
	case "--version":
	case "--help":
		display_help();
		exit;
	default:
		print "ERROR: Invalid Parameter " . $parameter . "\n\n";
		display_help();
		exit;
	}
}

/* additional error check */
if ($rrdfile == "") {
	echo "FATAL: You must specify an RRDfile!\n\n";
	display_help();
	exit;
}

/* determine the temporary file name */
$seed = mt_rand();
if ($config["cacti_server_os"] == "win32") {
	$tempdir  = getenv("TEMP");
	$xmlfile = $tempdir . "/" . str_replace(".rrd", "", basename($rrdfile)) . ".dump." . $seed;
}else{
	$tempdir = "/tmp";
	$xmlfile = "/tmp/" . str_replace(".rrd", "", basename($rrdfile)) . ".dump." . $seed;
}

if ($html) {
	echo "<table cellpadding='3' cellspacing='0' class='spikekill_data' id='spikekill_data'>";
}

if ($using_cacti) {
	cacti_log("NOTE: Removing Spikes for '$rrdfile', Method:'$method'", false, "WEBUI");
}

/* execute the dump command */
echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: Creating XML file '$xmlfile' from '$rrdfile'" . ($html ? "</td></tr>\n":"\n");

if ($using_cacti) {
	shell_exec(read_config_option("path_rrdtool") . " dump $rrdfile > $xmlfile");
}else{
	shell_exec("rrdtool dump $rrdfile > $xmlfile");
}

/* read the xml file into an array*/
if (file_exists($xmlfile)) {
	$output = file($xmlfile);

	/* remove the temp file */
	unlink($xmlfile);
}else{
	if ($using_cacti) {
		echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "FATAL: RRDtool Command Failed.  Please verify that the RRDtool path is valid in Settings->Paths!" . ($html ? "</td></tr>\n":"\n");
	}else{
		echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "FATAL: RRDtool Command Failed.  Please insure RRDtool is in your path!" . ($html ? "</td></tr>\n":"\n");
	}
	exit;
}

/* process the xml file and remove all comments */
$output = removeComments($output);

/* Read all the rra's ds values and obtain the following pieces of information from each
   rra archive.

   * numsamples - The number of 'valid' non-nan samples
   * sumofsamples - The sum of all 'valid' samples.
   * average - The average of all samples
   * standard_deviation - The standard deviation of all samples
   * max_value - The maximum value of all samples
   * min_value - The minimum value of all samples
   * max_cutoff - Any value above this value will be set to the average.
   * min_cutoff - Any value lower than this value will be set to the average.

   This will end up being a n-dimensional array as follows:
   rra[x][ds#]['totalsamples'];
   rra[x][ds#]['numsamples'];
   rra[x][ds#]['sumofsamples'];
   rra[x][ds#]['average'];
   rra[x][ds#]['stddev'];
   rra[x][ds#]['max_value'];
   rra[x][ds#]['min_value'];
   rra[x][ds#]['max_cutoff'];
   rra[x][ds#]['min_cutoff'];

   There will also be a secondary array created with the actual samples.  This
   array will be used to calculate the standard deviation of the sample set.
   samples[rra_num][ds_num][];

   Also track the min and max value for each ds and store it into the two
   arrays: ds_min[ds#], ds_max[ds#].

   The we don't need to know the type of rra, only it's number for this analysis
   the same applies for the ds' as well.
*/
$rra     = array();
$rra_cf  = array();
$rra_pdp = array();
$rra_num = 0;
$ds_num  = 0;
$total_kills = 0;
$in_rra  = false;
$in_db   = false;
$ds_min  = array();
$ds_max  = array();
$ds_name = array();

/* perform a first pass on the array and do the following:
   1) Get the number of good samples per ds
   2) Get the sum of the samples per ds
   3) Get the max and min values for all samples
   4) Build both the rra and sample arrays
   5) Get each ds' min and max values
*/
if (sizeof($output)) {
foreach($output as $line) {
	if (substr_count($line, "<v>")) {
		$linearray = explode("<v>", $line);
		/* discard the row */
		array_shift($linearray);
		$ds_num = 0;
		foreach($linearray as $dsvalue) {
			/* peel off garbage */
			$dsvalue = trim(str_replace("</row>", "", str_replace("</v>", "", $dsvalue)));
			if (strtolower($dsvalue) != "nan") {
				if (!isset($rra[$rra_num][$ds_num]["numsamples"])) {
					$rra[$rra_num][$ds_num]["numsamples"] = 1;
				}else{
					$rra[$rra_num][$ds_num]["numsamples"]++;
				}

				if (!isset($rra[$rra_num][$ds_num]["sumofsamples"])) {
					$rra[$rra_num][$ds_num]["sumofsamples"] = $dsvalue;
				}else{
					$rra[$rra_num][$ds_num]["sumofsamples"] += $dsvalue;
				}

				if (!isset($rra[$rra_num][$ds_num]["max_value"])) {
					$rra[$rra_num][$ds_num]["max_value"] = $dsvalue;
				}else if ($dsvalue > $rra[$rra_num][$ds_num]["max_value"]) {
					$rra[$rra_num][$ds_num]["max_value"] = $dsvalue;
				}

				if (!isset($rra[$rra_num][$ds_num]["min_value"])) {
					$rra[$rra_num][$ds_num]["min_value"] = $dsvalue;
				}else if ($dsvalue < $rra[$rra_num][$ds_num]["min_value"]) {
					$rra[$rra_num][$ds_num]["min_value"] = $dsvalue;
				}

				/* store the sample for standard deviation calculation */
				$samples[$rra_num][$ds_num][] = $dsvalue;
			}

			if (!isset($rra[$rra_num][$ds_num]["totalsamples"])) {
				$rra[$rra_num][$ds_num]["totalsamples"] = 1;
			}else{
				$rra[$rra_num][$ds_num]["totalsamples"]++;
			}

			$ds_num++;
		}
	} elseif (substr_count($line, "<rra>")) {
		$in_rra = true;
	} elseif (substr_count($line, "<min>")) {
		$ds_min[] = trim(str_replace("<min>", "", str_replace("</min>", "", trim($line))));
	} elseif (substr_count($line, "<max>")) {
		$ds_max[] = trim(str_replace("<max>", "", str_replace("</max>", "", trim($line))));
	} elseif (substr_count($line, "<name>")) {
		$ds_name[] = trim(str_replace("<name>", "", str_replace("</name>", "", trim($line))));
	} elseif (substr_count($line, "<cf>")) {
		$rra_cf[] = trim(str_replace("<cf>", "", str_replace("</cf>", "", trim($line))));
	} elseif (substr_count($line, "<pdp_per_row>")) {
		$rra_pdp[] = trim(str_replace("<pdp_per_row>", "", str_replace("</pdp_per_row>", "", trim($line))));
	} elseif (substr_count($line, "</rra>")) {
		$in_rra = false;
		$rra_num++;
	} elseif (substr_count($line, "<step>")) {
		$step = trim(str_replace("<step>", "", str_replace("</step>", "", trim($line))));
	}
}
}

/* For all the samples determine the average with the outliers removed */
calculateVarianceAverages($rra, $samples);

/* Now scan the rra array and the samples array and calculate the following
   1) The standard deviation of all samples
   2) The average of all samples per ds
   3) The max and min cutoffs of all samples
   4) The number of kills in each ds based upon the thresholds
*/
echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: Searching for Spikes in XML file '$xmlfile'" . ($html ? "</td></tr>\n":"\n");
calculateOverallStatistics($rra, $samples);

/* debugging and/or status report */
if ($debug || $dryrun) {
	outputStatistics($rra);
}

/* create an output array */
if ($method == 1) {
	/* standard deviation subroutine */
	if ($std_kills) {
		if (!$dryrun) {
			$new_output = updateXML($output, $rra);
		}
	}else{
		echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: NO Standard Deviation Spikes found in '$rrdfile'" . ($html ? "</td></tr>\n":"\n");
	}
}else{
	/* variance subroutine */
	if ($var_kills) {
		if (!$dryrun) {
			$new_output = updateXML($output, $rra);
		}
	}else{
		echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: NO Variance Spikes found in '$rrdfile'" . ($html ? "</td></tr>\n":"\n");
	}
}

/* finally update the file XML file and Reprocess the RRDfile */
if (!$dryrun) {
	if ($total_kills) {
		if (writeXMLFile($new_output, $xmlfile)) {
			if (backupRRDFile($rrdfile)) {
				createRRDFileFromXML($xmlfile, $rrdfile);
			}else{
				echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "FATAL: Unable to backup '$rrdfile'" . ($html ? "</td></tr>\n":"\n");
			}
		}else{
			echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "FATAL: Unable to write XML file '$xmlfile'" . ($html ? "</td></tr>\n":"\n");
		}
	}
}else{
	echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: Dryrun requested.  No updates performed" . ($html ? "</td></tr>\n":"\n");
}

if ($html) {
	echo "</table>";
}

/* All Functions */
function createRRDFileFromXML($xmlfile, $rrdfile) {
	global $using_cacti, $html;

	/* execute the dump command */
	echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: Re-Importing '$xmlfile' to '$rrdfile'" . ($html ? "</td></tr>\n":"\n");
	if ($using_cacti) {
		$response = shell_exec(read_config_option("path_rrdtool") . " restore -f -r $xmlfile $rrdfile");
	}else{
		$response = shell_exec("rrdtool restore -f -r $xmlfile $rrdfile");
	}
	if (strlen($response)) echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . $response . ($html ? "</td></tr>\n":"\n");
}

function writeXMLFile($output, $xmlfile) {
	return file_put_contents($xmlfile, $output);
}

function backupRRDFile($rrdfile) {
	global $using_cacti, $tempdir, $seed, $html;

	if ($using_cacti) {
		$backupdir = read_config_option("spikekill_backupdir");

		if ($backupdir == "") {
			$backupdir = $tempdir;
		}
	}else{
		$backupdir = $tempdir;
	}

	if (file_exists($backupdir . "/" . basename($rrdfile))) {
		$newfile = basename($rrdfile) . "." . $seed;
	}else{
		$newfile = basename($rrdfile);
	}

	echo ($html ? "<tr><td colspan='20' class='spikekill_note'>":"") . "NOTE: Backing Up '$rrdfile' to '" . $backupdir . "/" .  $newfile . "'" . ($html ? "</td></tr>\n":"\n");

	return copy($rrdfile, $backupdir . "/" . $newfile);
}

function calculateVarianceAverages(&$rra, &$samples) {
	global $outliers;

	if (sizeof($samples)) {
	foreach($samples as $rra_num => $dses) {
		if (sizeof($dses)) {
		foreach($dses as $ds_num => $ds) {
			if (sizeof($ds) < $outliers * 3) {
				$rra[$rra_num][$ds_num]["variance_avg"] = "NAN";
			}else{
				rsort($ds, SORT_NUMERIC);
				$ds = array_slice($ds, $outliers);

				sort($ds, SORT_NUMERIC);
				$ds = array_slice($ds, $outliers);

				$rra[$rra_num][$ds_num]["variance_avg"] = array_sum($ds) / sizeof($ds);
			}
		}
		}
	}
	}
}

function calculateOverallStatistics(&$rra, &$samples) {
	global $percent, $stddev, $ds_min, $ds_max, $var_kills, $std_kills;

	$rra_num = 0;
	if (sizeof($rra)) {
	foreach($rra as $dses) {
		$ds_num = 0;

		if (sizeof($dses)) {
		foreach($dses as $ds) {
			if (isset($samples[$rra_num][$ds_num])) {
				$rra[$rra_num][$ds_num]["standard_deviation"] = standard_deviation($samples[$rra_num][$ds_num]);
				if ($rra[$rra_num][$ds_num]["standard_deviation"] == "NAN") {
					$rra[$rra_num][$ds_num]["standard_deviation"] = 0;
				}
				$rra[$rra_num][$ds_num]["average"]    = $rra[$rra_num][$ds_num]["sumofsamples"] / $rra[$rra_num][$ds_num]["numsamples"];

				$rra[$rra_num][$ds_num]["min_cutoff"] = $rra[$rra_num][$ds_num]["average"] - ($stddev * $rra[$rra_num][$ds_num]["standard_deviation"]);
				if ($rra[$rra_num][$ds_num]["min_cutoff"] < $ds_min[$ds_num]) {
					$rra[$rra_num][$ds_num]["min_cutoff"] = $ds_min[$ds_num];
				}

				$rra[$rra_num][$ds_num]["max_cutoff"] = $rra[$rra_num][$ds_num]["average"] + ($stddev * $rra[$rra_num][$ds_num]["standard_deviation"]);
				if ($rra[$rra_num][$ds_num]["max_cutoff"] > $ds_max[$ds_num]) {
					$rra[$rra_num][$ds_num]["max_cutoff"] = $ds_max[$ds_num];
				}

				$rra[$rra_num][$ds_num]["numnksamples"] = 0;
				$rra[$rra_num][$ds_num]["sumnksamples"] = 0;
				$rra[$rra_num][$ds_num]["avgnksamples"] = 0;

				/* go through values and find cutoffs */
				$rra[$rra_num][$ds_num]["stddev_killed"]    = 0;
				$rra[$rra_num][$ds_num]["variance_killed"]  = 0;

				if (sizeof($samples[$rra_num][$ds_num])) {
				foreach($samples[$rra_num][$ds_num] as $sample) {
					if (($sample > $rra[$rra_num][$ds_num]["max_cutoff"]) ||
						($sample < $rra[$rra_num][$ds_num]["min_cutoff"])) {
						debug(sprintf("Std Kill: Value '%.4e', StandardDev '%.4e', StdDevLimit '%.4e'", $sample, $rra[$rra_num][$ds_num]["standard_deviation"], ($rra[$rra_num][$ds_num]["max_cutoff"] * (1+$percent))));
						$rra[$rra_num][$ds_num]["stddev_killed"]++;
						$std_kills = true;
					}else{
						$rra[$rra_num][$ds_num]["numnksamples"]++;
						$rra[$rra_num][$ds_num]["sumnksamples"] += $sample;
					}

					if ($rra[$rra_num][$ds_num]["variance_avg"] == "NAN") {
						/* not enought samples to calculate */
					}else if ($sample > ($rra[$rra_num][$ds_num]["variance_avg"] * (1+$percent))) {
						/* kill based upon variance */
						debug(sprintf("Var Kill: Value '%.4e', VarianceDev '%.4e', VarianceLimit '%.4e'", $sample, $rra[$rra_num][$ds_num]["variance_avg"], ($rra[$rra_num][$ds_num]["variance_avg"] * (1+$percent))));
						$rra[$rra_num][$ds_num]["variance_killed"]++;
						$var_kills = true;
					}
				}
				}

				if ($rra[$rra_num][$ds_num]["numnksamples"] > 0) {
					$rra[$rra_num][$ds_num]["avgnksamples"] = $rra[$rra_num][$ds_num]["sumnksamples"] / $rra[$rra_num][$ds_num]["numnksamples"];
				}
			}else{
				$rra[$rra_num][$ds_num]["standard_deviation"] = "N/A";
				$rra[$rra_num][$ds_num]["average"]            = "N/A";
				$rra[$rra_num][$ds_num]["min_cutoff"]         = "N/A";
				$rra[$rra_num][$ds_num]["max_cutoff"]         = "N/A";
				$rra[$rra_num][$ds_num]["numnksamples"]       = "N/A";
				$rra[$rra_num][$ds_num]["sumnksamples"]       = "N/A";
				$rra[$rra_num][$ds_num]["avgnksamples"]       = "N/A";
				$rra[$rra_num][$ds_num]["stddev_killed"]      = "N/A";
				$rra[$rra_num][$ds_num]["variance_killed"]    = "N/A";
				$rra[$rra_num][$ds_num]["stddev_killed"]      = "N/A";
				$rra[$rra_num][$ds_num]["numnksamples"]       = "N/A";
				$rra[$rra_num][$ds_num]["sumnksamples"]       = "N/A";
				$rra[$rra_num][$ds_num]["variance_killed"]    = "N/A";
				$rra[$rra_num][$ds_num]["avgnksamples"]       = "N/A";
			}

			$ds_num++;
		}
		}

		$rra_num++;
	}
	}
}

function outputStatistics($rra) {
	global $rra_cf, $rra_name, $ds_name, $rra_pdp, $html;

	if (sizeof($rra)) {
		if (!$html) {
			echo "\n";
			printf("%10s %16s %10s %7s %7s %10s %10s %10s %10s %10s %10s %10s %10s %10s %10s\n",
				"Size", "DataSource", "CF", "Samples", "NonNan", "Avg", "StdDev",
				"MaxValue", "MinValue", "MaxStdDev", "MinStdDev", "StdKilled", "VarKilled", "StdDevAvg", "VarAvg");
			printf("%10s %16s %10s %7s %7s %10s %10s %10s %10s %10s %10s %10s %10s %10s %10s\n",
				"----------", "---------------", "----------", "-------", "-------", "----------", "----------", "----------",
				"----------", "----------", "----------", "----------", "----------", "----------",
				"----------");
			foreach($rra as $rra_key => $dses) {
				if (sizeof($dses)) {
				foreach($dses as $dskey => $ds) {
					printf("%10s %16s %10s %7s %7s " .
						($ds["average"] < 1E6 ? "%10s ":"%10.4e ") .
						($ds["standard_deviation"] < 1E6 ? "%10s ":"%10.4e ") .
						(isset($ds["max_value"]) ? ($ds["max_value"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") .
						(isset($ds["min_value"]) ? ($ds["min_value"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") .
						(isset($ds["max_cutoff"]) ? ($ds["max_cutoff"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") .
						(isset($ds["min_cutoff"]) ? ($ds["min_cutoff"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") .
						"%10s %10s " .
						(isset($ds["avgnksampled"]) ? ($ds["avgnksamples"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") .
						(isset($ds["variance_avg"]) ? ($ds["variance_avg"] < 1E6 ? "%10s ":"%10.4e ") : "%10s ") . "\n",
						displayTime($rra_pdp[$rra_key]),
						$ds_name[$dskey],
						$rra_cf[$rra_key],
						$ds["totalsamples"],
						(isset($ds["numsamples"]) ? $ds["numsamples"] : "0"),
						($ds["average"] != "N/A" ? round($ds["average"],2) : $ds["average"]),
						($ds["standard_deviation"] != "N/A" ? round($ds["standard_deviation"],2) : $ds["standard_deviation"]),
						(isset($ds["max_value"]) ? round($ds["max_value"],2) : "N/A"),
						(isset($ds["min_value"]) ? round($ds["min_value"],2) : "N/A"),
						($ds["max_cutoff"] != "N/A" ? round($ds["max_cutoff"],2) : $ds["max_cutoff"]),
						($ds["min_cutoff"] != "N/A" ? round($ds["min_cutoff"],2) : $ds["min_cutoff"]),
						$ds["stddev_killed"],
						$ds["variance_killed"],
						($ds["avgnksamples"] != "N/A" ? round($ds["avgnksamples"],2) : $ds["avgnksamples"]),
						(isset($ds["variance_avg"]) ? round($ds["variance_avg"],2) : "N/A"));
				}
				}
			}

			echo "\n";
		}else{
			printf("<tr><th style='width:10%%;'>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>\n",
				"Size", "DataSource", "CF", "Samples", "NonNan", "Avg", "StdDev",
				"MaxValue", "MinValue", "MaxStdDev", "MinStdDev", "StdKilled", "VarKilled", "StdDevAvg", "VarAvg");
			foreach($rra as $rra_key => $dses) {
				if (sizeof($dses)) {
				foreach($dses as $dskey => $ds) {
					printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>" .
						($ds["average"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") .
						($ds["standard_deviation"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") .
						(isset($ds["max_value"]) ? ($ds["max_value"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") : "%s</td><td>") .
						(isset($ds["min_value"]) ? ($ds["min_value"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") : "%s</td><td>") .
						(isset($ds["max_cutoff"]) ? ($ds["max_cutoff"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") : "%s</td><td>") .
						(isset($ds["min_cutoff"]) ? ($ds["min_cutoff"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") : "%s</td><td>") .
						"%s</td><td>%s</td><td>" .
						(isset($ds["avgnksampled"]) ? ($ds["avgnksamples"] < 1E6 ? "%s</td><td>":"%.4e</td><td>") : "%s</td><td>") .
						(isset($ds["variance_avg"]) ? ($ds["variance_avg"] < 1E6 ? "%s</td></tr>\n":"%.4e</td></tr>\n") : "%s</td></tr>\n") . "\n",
						displayTime($rra_pdp[$rra_key]),
						$ds_name[$dskey],
						$rra_cf[$rra_key],
						$ds["totalsamples"],
						(isset($ds["numsamples"]) ? $ds["numsamples"] : "0"),
						($ds["average"] != "N/A" ? round($ds["average"],2) : $ds["average"]),
						($ds["standard_deviation"] != "N/A" ? round($ds["standard_deviation"],2) : $ds["standard_deviation"]),
						(isset($ds["max_value"]) ? round($ds["max_value"],2) : "N/A"),
						(isset($ds["min_value"]) ? round($ds["min_value"],2) : "N/A"),
						($ds["max_cutoff"] != "N/A" ? round($ds["max_cutoff"],2) : $ds["max_cutoff"]),
						($ds["min_cutoff"] != "N/A" ? round($ds["min_cutoff"],2) : $ds["min_cutoff"]),
						$ds["stddev_killed"],
						$ds["variance_killed"],
						($ds["avgnksamples"] != "N/A" ? round($ds["avgnksamples"],2) : $ds["avgnksamples"]),
						(isset($ds["variance_avg"]) ? round($ds["variance_avg"],2) : "N/A"));
				}
				}
			}
		}
	}
}

function updateXML(&$output, &$rra) {
	global $numspike, $percent, $avgnan, $method, $total_kills;
        $new_array = array();

	/* variance subroutine */
	$rra_num = 0;
	$ds_num  = 0;
	$kills   = 0;

	if (sizeof($output)) {
	foreach($output as $line) {
		if (substr_count($line, "<v>")) {
			$linearray = explode("<v>", $line);
			/* discard the row */
			array_shift($linearray);

			/* initialize variables */
			$ds_num  = 0;
			$out_row = "<row>";
			foreach($linearray as $dsvalue) {
				/* peel off garbage */
				$dsvalue = trim(str_replace("</row>", "", str_replace("</v>", "", $dsvalue)));
				if (strtolower($dsvalue) == "nan") {
					/* do nothing, it's a NaN */
				}else{
					if ($method == 2) {
						if ($dsvalue > (1+$percent)*$rra[$rra_num][$ds_num]["variance_avg"]) {
							if ($kills < $numspike) {
								if ($avgnan == "avg") {
									$dsvalue = $rra[$rra_num][$ds_num]["variance_avg"];
								}else{
									$dsvalue = "NaN";
								}
								$kills++;
								$total_kills++;
							}
						}
					}else{
						if (($dsvalue > $rra[$rra_num][$ds_num]["max_cutoff"]) ||
							($dsvalue < $rra[$rra_num][$ds_num]["min_cutoff"])) {
							if ($kills < $numspike) {
								if ($avgnan == "avg") {
									$dsvalue = $rra[$rra_num][$ds_num]["average"];
								}else{
									$dsvalue = "NaN";
								}
								$kills++;
								$total_kills++;
							}
						}
					}
				}

				$out_row .= "<v> " . $dsvalue . "</v>";
				$ds_num++;
			}

			$out_row .= "</row>";

			$new_array[] = $out_row;
		}else{
			if (substr_count($line, "</rra>")) {
				$ds_minmax = array();
				$rra_num++;
				$kills = 0;
			}else if (substr_count($line, "</database>")) {
				$ds_num++;
				$kills = 0;
			}

			$new_array[] = $line;
		}
	}
	}

	return $new_array;
}

function removeComments(&$output) {
	if (sizeof($output)) {
		foreach($output as $line) {
			$line = trim($line);
			if ($line == "") {
				continue;
			}else{
				/* is there a comment, remove it */
				$comment_start = strpos($line, "<!--");
				if ($comment_start === false) {
					/* do nothing no line */
				}else{
					$comment_end = strpos($line, "-->");
					if ($comment_start == 0) {
						$line = trim(substr($line, $comment_end+3));
					}else{
						$line = trim(substr($line,0,$comment_start-1) . substr($line,$comment_end+3));
					}
				}

				if ($line != "") {
					$new_array[] = $line;
				}
			}
		}
		/* transfer the new array back to the original array */
		return $new_array;
	}
}

function displayTime($pdp) {
	global $step;

	$total_time = $pdp * $step; // seconds

	if ($total_time < 60) {
		return $total_time . " secs";
	}else{
		$total_time = $total_time / 60;

		if ($total_time < 60) {
			return $total_time . " mins";
		}else{
			$total_time = $total_time / 60;

			if ($total_time < 24) {
				return $total_time . " hours";
			}else{
				$total_time = $total_time / 24;

				return $total_time . " days";
			}
		}
	}
}

function debug($string) {
	global $debug;

	if ($debug) {
		echo "DEBUG: " . $string . "\n";
	}
}

function standard_deviation($samples) {
	$sample_count = count($samples);
        $sample_square = array();

	for ($current_sample = 0; $sample_count > $current_sample; ++$current_sample) {
		$sample_square[$current_sample] = pow($samples[$current_sample], 2);
	}

	return sqrt(array_sum($sample_square) / $sample_count - pow((array_sum($samples) / $sample_count), 2));
}

/* display_help - displays the usage of the function */
function display_help () {
	global $using_cacti;

	if ($using_cacti) {
		$version = spikekill_version();
	}else{
		$version = "v1.0";
	}

	echo "Cacti Spike Remover " . ($using_cacti ? "v" . $version["version"] : $version) . ", Copyright 2009, The Cacti Group, Inc.\n\n";
	echo "Usage:\n";
	echo "removespikes.php -R|--rrdfile=rrdfile [-M|--method=stddev] [-A|--avgnan] [-S|--stddev=N]\n";
	echo "                 [-P|--percent=N] [-N|--number=N] [-D|--dryrun] [-d|--debug] [-h|--help|-v|-V|--version]\n\n";

	echo "The RRDfile input parameter is mandatory.  If no other input parameters are specified the defaults\n";
	echo "are taken from the Spikekill Plugin settings.\n\n";

	echo "-M|--method      - The spike removal method to use.  Options are 'stddev'|'variance'\n";
	echo "-A|--avgnan      - The spike replacement method to use.  Options are 'avg'|'nan'\n";
	echo "-S|--stddev      - The number of standard deviations +/- allowed\n";
	echo "-P|--percent     - The sample to sample percentage variation allowed\n";
	echo "-N|--number      - The maximum number of spikes to remove from the RRDfile\n";
	echo "-D|--dryrun      - If specified, the RRDfile will not be changed.  Instead a summary of\n";
	echo "                   changes that would have been performed will be issued.\n\n";

	echo "The remainder of arguments are informational\n";
	echo "-d|--debug       - Display verbose output during execution\n";
	echo "-v -V --version  - Display this help message\n";
	echo "-h --help        - display this help message\n";
}
