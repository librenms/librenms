<?php

use LibreNMS\Config;

// Push $_GET into $vars to be compatible with web interface naming
foreach ($_GET as $name => $value) {
    $vars[$name] = $value;
}

list($type, $subtype) = extract_graph_type($vars['type']);

if (is_numeric($vars['device'])) {
    $device = device_by_id_cache($vars['device']);
} elseif (!empty($vars['device'])) {
    $device = device_by_name($vars['device']);
}

// FIXME -- remove these
$width    = $vars['width'];
$height   = $vars['height'];
$title    = $vars['title'];
$vertical = $vars['vertical'];
$legend   = $vars['legend'];
$output   = (!empty($vars['output']) ? $vars['output'] : 'default');
$from = parse_at_time($_GET['from']) ?: Config::get('time.day');
$to   = parse_at_time($_GET['to']) ?: Config::get('time.now');
$graph_type = (isset($vars['graph_type']) ? $vars['graph_type'] : Config::get('webui.graph_type'));

$period = ($to - $from);
$base64_output = '';
$prev_from = ($from - $period);

$graphfile = Config::get('temp_dir') . '/' . strgen();

require Config::get('install_dir') . "/includes/html/graphs/$type/auth.inc.php";

if ($auth && is_custom_graph($type, $subtype, $device)) {
    include(Config::get('install_dir') . "/includes/html/graphs/custom.inc.php");
} elseif ($auth && is_mib_graph($type, $subtype)) {
    include Config::get('install_dir') . "/includes/html/graphs/$type/mib.inc.php";
} elseif ($auth && is_file(Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php")) {
    include Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php";
} else {
    graph_error("$type*$subtype ");
    // Graph Template Missing");
}

function graph_error($string)
{
    global $vars, $debug, $graphfile;

    $vars['bg'] = 'FFBBBB';

    include 'includes/html/graphs/common.inc.php';

    $rrd_options .= ' HRULE:0#555555';
    $rrd_options .= " --title='".$string."'";

    rrdtool_graph($graphfile, $rrd_options);

    if ($height > '99') {
        shell_exec($rrd_cmd);
        d_echo('<pre>'.$rrd_cmd.'</pre>');

        if (is_file($graphfile) && !$debug) {
            header('Content-type: image/png');
            $fd = fopen($graphfile, 'r');
            fpassthru($fd);
            fclose($fd);
            unlink($graphfile);
        }
    } else {
        if (!$debug) {
            header('Content-type: image/png');
        }

        $im = imagecreate($width, $height);
        $px = ((imagesx($im) - 7.5 * strlen($string)) / 2);
        imagestring($im, 3, $px, ($height / 2 - 8), $string, imagecolorallocate($im, 128, 0, 0));
        imagepng($im);
        imagedestroy($im);
    }
}

if ($error_msg) {
    // We have an error :(
    graph_error($graph_error);
} elseif ($auth === null) {
    // We are unauthenticated :(
    if ($width < 200) {
        graph_error('No Auth');
    } else {
        graph_error('No Authorisation');
    }
} else {
    // $rrd_options .= " HRULE:0#999999";
    if ($graph_type === 'svg') {
        $rrd_options .= " --imgformat=SVG";
        if ($width < 350) {
            $rrd_options .= " -m 0.75 -R light";
        }
    }

    if ($no_file) {
        if ($width < 200) {
            graph_error('No RRD');
        } else {
            graph_error('Missing RRD Datafile');
        }
    } elseif ($command_only) {
        echo "<div class='infobox'>";
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
        echo "<pre class='rrd-pre'>";
        echo "rrdtool ".rrdtool_build_command("graph", $graphfile, $rrd_options);
        echo "</pre>";
        $return = rrdtool_graph($graphfile, $rrd_options);
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
        echo "<pre class='rrd-pre'>";
        echo "$return";
        echo "</pre>";
        unlink($graphfile);
        echo '</div>';
    } else {
        if ($rrd_options) {
            rrdtool_graph($graphfile, $rrd_options);
            d_echo($rrd_cmd);
            if (is_file($graphfile)) {
                if (!$debug) {
                    set_image_type();
                    if (Config::get('trim_tobias') && $graph_type !== 'svg') {
                        list($w, $h, $type, $attr) = getimagesize($graphfile);
                        $src_im                    = imagecreatefrompng($graphfile);
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
                        $white        = imagecolorallocate($dst_im, 255, 255, 255);
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
                            $base64_output =  base64_encode($imagedata);
                        } else {
                            $fd = fopen($graphfile, 'r');
                            fpassthru($fd);
                            fclose($fd);
                        }
                    }
                } else {
                    echo `ls -l $graphfile`;
                    echo '<img src="'.data_uri($graphfile, 'image/svg+xml').'" alt="graph" />';
                }
                unlink($graphfile);
            } else {
                if ($width < 200) {
                    graph_error('Draw Error');
                } else {
                    graph_error('Error Drawing Graph');
                }
            }
        } else {
            if ($width < 200) {
                graph_error('Def Error');
            } else {
                graph_error('Graph Definition Error');
            }
        }
    }
}
