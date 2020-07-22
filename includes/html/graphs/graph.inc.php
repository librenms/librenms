<?php

use LibreNMS\Config;

// Push $_GET into $vars to be compatible with web interface naming
foreach ($_GET as $name => $value) {
    $vars[$name] = $value;
}

[$type, $subtype] = extract_graph_type($vars['type']);

if (is_numeric($vars['device'])) {
    $device = device_by_id_cache($vars['device']);
} elseif (! empty($vars['device'])) {
    $device = device_by_name($vars['device']);
}

// FIXME -- remove these
$width = $vars['width'];
$height = $vars['height'];
$title = $vars['title'];
$vertical = $vars['vertical'];
$legend = $vars['legend'];
$output = (! empty($vars['output']) ? $vars['output'] : 'default');
$from = parse_at_time($_GET['from']) ?: Config::get('time.day');
$to = parse_at_time($_GET['to']) ?: Config::get('time.now');
$graph_type = (isset($vars['graph_type']) ? $vars['graph_type'] : Config::get('webui.graph_type'));

$period = ($to - $from);
$base64_output = '';
$prev_from = ($from - $period);

$graphfile = Config::get('temp_dir') . '/' . strgen();

require Config::get('install_dir') . "/includes/html/graphs/$type/auth.inc.php";

//set default graph title
$graph_title = format_hostname($device);

if ($auth && is_customoid_graph($type, $subtype)) {
    $unit = $vars['unit'];
    include Config::get('install_dir') . '/includes/html/graphs/customoid/customoid.inc.php';
} elseif ($auth && is_file(Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php")) {
    include Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php";
} else {
    graph_error("$type*$subtype ");
    // Graph Template Missing");
}

function graph_error($string)
{
    global $vars, $debug;

    if (!$debug) {
        header('Content-type: image/png');
    }
    $width = $vars['width'] ?? 150;
    $height = $vars['height'] ?? 60;

    $im = imagecreate($width, $height);
    imagecolorallocate($im, 255, 255, 255); // background
    $px = ((imagesx($im) - 7.5 * strlen($string)) / 2);
    imagestring($im, 3, $px, ($height / 2 - 8), $string, imagecolorallocate($im, 128, 0, 0));
    imagepng($im);
    imagedestroy($im);
}

if ($error_msg) {
    // We have an error :(
    graph_error($graph_error);
} elseif ($auth === null) {
    // We are unauthenticated :(
    graph_error($width < 200 ? 'No Auth' : 'No Authorisation');
} else {
    // $rrd_options .= " HRULE:0#999999";
    if ($graph_type === 'svg') {
        $rrd_options .= ' --imgformat=SVG';
        if ($width < 350) {
            $rrd_options .= ' -m 0.75 -R light';
        }
    }

    if ($command_only) {
        echo "<div class='infobox'>";
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
        echo "<pre class='rrd-pre'>";
        echo 'rrdtool ' . Rrd::buildCommand('graph', $graphfile, $rrd_options);
        echo '</pre>';
        $return = rrdtool_graph($graphfile, $rrd_options);
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
        echo "<pre class='rrd-pre'>";
        echo "$return";
        echo '</pre>';
        unlink($graphfile);
        echo '</div>';
    } elseif ($no_file || (isset($rrd_filename) && !Rrd::checkRrdExists($rrd_filename))) {
        graph_error($width < 200 ? 'No RRD' : 'Missing RRD Datafile');
    }  else {
        if ($rrd_options) {
            rrdtool_graph($graphfile, $rrd_options);
            d_echo($rrd_cmd);
            if (is_file($graphfile)) {
                if (! $debug) {
                    set_image_type();
                    if (Config::get('trim_tobias') && $graph_type !== 'svg') {
                        [$w, $h, $type, $attr] = getimagesize($graphfile);
                        $src_im = imagecreatefrompng($graphfile);
                        $src_x = '0';
                        // begin x
                        $src_y = '0';
                        // begin y
                        $src_w = ($w - 12);
                        // width
                        $src_h = $h;
                        // height
                        $dst_x = '0';
                        // destination x
                        $dst_y = '0';
                        // destination y
                        $dst_im = imagecreatetruecolor($src_w, $src_h);
                        imagesavealpha($dst_im, true);
                        $white = imagecolorallocate($dst_im, 255, 255, 255);
                        $trans_colour = imagecolorallocatealpha($dst_im, 0, 0, 0, 127);
                        imagefill($dst_im, 0, 0, $trans_colour);
                        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
                        if ($output === 'base64') {
                            ob_start();
                            imagepng($png);
                            $imagedata = ob_get_contents();
                            imagedestroy($png);
                            ob_end_clean();

                            $base64_output = base64_encode($imagedata);
                        } else {
                            imagepng($dst_im);
                            imagedestroy($dst_im);
                        }
                    } else {
                        if ($output === 'base64') {
                            $imagedata = file_get_contents($graphfile);
                            $base64_output = base64_encode($imagedata);
                        } else {
                            $fd = fopen($graphfile, 'r');
                            fpassthru($fd);
                            fclose($fd);
                        }
                    }
                } else {
                    echo `ls -l $graphfile`;
                    echo '<img src="' . data_uri($graphfile, 'image/svg+xml') . '" alt="graph" />';
                }
                unlink($graphfile);
            } else {
                graph_error($width < 200 ? 'Draw Error' : 'Error Drawing Graph');
            }
        } else {
            graph_error($width < 200 ? 'Def Error' : 'Graph Definition Error');
        }
    }
}
