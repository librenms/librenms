<?php

require 'includes/geshi/geshi.php';

// FIXME svn stuff still using optc etc, won't work, needs updating!
if (is_admin()) {
    if (!is_array($config['rancid_configs'])) {
        $config['rancid_configs'] = array($config['rancid_configs']);
    }

    if (isset($config['rancid_configs'][0])) {
        foreach ($config['rancid_configs'] as $configs) {
            if ($configs[(strlen($configs) - 1)] != '/') {
                $configs .= '/';
            }

            if (is_file($configs.$device['hostname'])) {
                $file = $configs.$device['hostname'];
            } 
            elseif (is_file($configs.strtok($device['hostname'], '.'))) { // Strip domain
                $file = $configs.strtok($device['hostname'], '.');
            } 
            else {
                if (!empty($config['mydomain'])) { // Try with domain name if set
                    if (is_file($configs.$device['hostname'].'.'.$config['mydomain'])) {
                        $file = $configs.$device['hostname'].'.'.$config['mydomain'];
                    }
                }
            } // end if
        }

        echo '<div style="clear: both;">';

        print_optionbar_start('', '');

        echo "<span style='font-weight: bold;'>Config</span> &#187; ";

        if (!$vars['rev']) {
            echo '<span class="pagemenu-selected">';
            echo generate_link('Latest', array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig'));
            echo '</span>';
        }
        else {
            echo generate_link('Latest', array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig'));
        }

        if (function_exists('svn_log')) {
            $sep     = ' | ';
            $svnlogs = svn_log($file, SVN_REVISION_HEAD, null, 8);
            $revlist = array();

            foreach ($svnlogs as $svnlog) {
                echo $sep;
                $revlist[] = $svnlog['rev'];

                if ($vars['rev'] == $svnlog['rev']) {
                    echo '<span class="pagemenu-selected">';
                }

                $linktext = 'r'.$svnlog['rev'].' <small>'.date($config['dateformat']['byminute'], strtotime($svnlog['date'])).'</small>';
                echo generate_link($linktext, array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig', 'rev' => $svnlog['rev']));

                if ($vars['rev'] == $svnlog['rev']) {
                    echo '</span>';
                }

                $sep = ' | ';
            }
        }//end if

        print_optionbar_end();

        if (function_exists('svn_log') && in_array($vars['rev'], $revlist)) {
            list($diff, $errors) = svn_diff($file, ($vars['rev'] - 1), $file, $vars['rev']);
            if (!$diff) {
                $text = 'No Difference';
            }
            else {
                $text = '';
                while (!feof($diff)) {
                    $text .= fread($diff, 8192);
                }

                fclose($diff);
                fclose($errors);
            }
        }
        else {
            $fh   = fopen($file, 'r') or die("Can't open file");
            $text = fread($fh, filesize($file));
            fclose($fh);
        }

        if ($config['rancid_ignorecomments']) {
            $lines = explode("\n", $text);
            for ($i = 0; $i < count($lines); $i++) {
                if ($lines[$i][0] == '#') {
                    unset($lines[$i]);
                }
            }

            $text = join("\n", $lines);
        }
    }
    else if ($config['oxidized']['enabled'] === true && isset($config['oxidized']['url'])) {
        // fetch info about the node and then a list of versions for that node
        $node_info = json_decode(file_get_contents($config['oxidized']['url'].'/node/show/'.$device['hostname'].'?format=json'), true);
        $config_versions = json_decode(file_get_contents($config['oxidized']['url'].'/node/version?node_full='.(!empty($node_info['group']) ? $node_info['group'].'/' : '').$device['hostname'].'&format=json'), true);

        if (is_array($config_versions)) {
            $config_total = count($config_versions);
        }

        if ($config_total > 1) {
            if (isset($_POST['config'])) {  // versioning with selected version
                list($oid,$date,$version) = explode('|',mres($_POST['config']));
                $current_version = array('oid'=>$oid, 'date'=>$date, 'version'=>$version);
            }
            else { // no version selected
                $current_version = array('oid'=>$config_versions[0]['oid'], 'date'=>$current_version[0]['date'], 'version'=>$config_total);
            }

            // fetch current_version
            $text = file_get_contents($config['oxidized']['url'].'/node/version/view?node='.$device['hostname'].'&group='.(!empty($node_info['group']) ? $node_info['group'] : '').'&oid='.$current_version['oid'].'&date='.urlencode($current_version['date']).'&num='.$current_version['version'].'&format=text');

            if (isset($_POST['diff']) && isset($_POST['prevconfig'])) { // diff requested
                list($oid,$date,$version) = explode('|',mres($_POST['prevconfig']));
                if ($current_version['oid'] == $oid) { // the same version is selected, assume the previous revision
                    foreach ($config_versions as $key => $version) {
                        if ($version['oid'] == $current_version['oid']) {
                            $prev_key = $key + 1;
                            $oid = $config_versions[$prev_key]['oid'];
                            $date = $config_versions[$prev_key]['date'];
                            $version = $config_total - $prev_key;
                            break;
                        }
                    }
                }

                if ($version > 0) { // if we know the version doesn't exist, don't even try to fetch it
                    $previous_text = file_get_contents($config['oxidized']['url'].'/node/version/view?node='.$device['hostname'].'&group='.(!empty($node_info['group']) ? $node_info['group'] : '').'&oid='.$oid.'&date='.urlencode($date).'&num='.$version.'&format=text');
                    if (!empty($previous_text)) {
                        $previous_version = array('oid'=>$oid, 'date'=>$date, 'version'=>$version);
                        $text = xdiff_string_diff($text, $previous_text); // requires pecl xdiff
                    }
                } else {
                    print_error('No previous version, please select a different version.');
                }
            }
        }
        else {  // just fetch the only version
            $text = file_get_contents($config['oxidized']['url'].'/node/fetch/'.(!empty($node_info['group']) ? $node_info['group'].'/' : '').$device['hostname']);
        }

        if (is_array($node_info) || $config_total > 1) {
            echo '<br />
                <div class="row">
            ';

            if (is_array($node_info)) {
                echo '
                      <div class="col-sm-4">
                          <div class="panel panel-primary">
                              <div class="panel-heading">Sync status: <strong>'.$node_info['last']['status'].'</strong></div>
                              <ul class="list-group">
                                  <li class="list-group-item"><strong>Node:</strong> '.$node_info['name'].'</strong></li>
                                  <li class="list-group-item"><strong>IP:</strong> '.$node_info['ip'].'</strong></li>
                                  <li class="list-group-item"><strong>Model:</strong> '.$node_info['model'].'</strong></li>
                                  <li class="list-group-item"><strong>Last Sync:</strong> '.$node_info['last']['end'].'</strong></li>
                              </ul>
                          </div>
                      </div>
                ';
            }

            if ($config_total > 1) {
                echo '
                    <div class="col-sm-8">
                        <form class="form-horizontal" action="" method="post">
                            <div class="form-group">
                                <label for="config" class="col-sm-2 control-label">Config version</label>
                                <div class="col-sm-6">
                                    <select id="config" name="config" class="form-control">
                ';

                $i = $config_total;
                foreach ($config_versions as $version) {
                    echo '<option value="'.$version['oid'].'|'.$version['date'].'|'.$config_total.'" ';
                    if ($current_version['oid'] == $version['oid']) {
                        if (is_array($previous_version)) {
                            echo 'selected>+';
                        }
                        else {
                            echo 'selected>*';
                        }
                    }
                    else if ($previous_version['oid'] == $version['oid']) {
                        echo '>&nbsp;-';
                    }
                    else {
                        echo '>&nbsp;&nbsp;';
                    }
                    echo $i.' :: '.$version['date'].'</option>';
                    $i--;
                }

                echo '
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-6">
                                      <input type="hidden" name="prevconfig" value="';
                echo implode('|',$current_version);
                echo '">
                                      <button type="submit" class="btn btn-primary btn-sm" name="show">Show version</button>
                                      <button type="submit" class="btn btn-primary btn-sm" name="diff">Show diff</button>
                                </div>
                            </div>
                        </form>
                    </div>
                ';
            }

            echo '</div>';
        }
        else {
            echo '<br />';
            print_error("We couldn't retrieve the device information from Oxidized");
            $text = '';
        }
    }//end if

    if (!empty($text)) {
        if (is_array($previous_version)) {
            $language = 'diff';
        } else {
            $language = 'ios';
        }
        $geshi = new GeSHi($text, $language);
//        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        $geshi->set_overall_style('color: black;');
        // $geshi->set_line_style('color: #999999');
        echo $geshi->parse_code();
    }
}//end if

$pagetitle[] = 'Config';
