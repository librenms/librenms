<?php

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
            } elseif (is_file($configs.strtok($device['hostname'], '.'))) { // Strip domain
                $file = $configs.strtok($device['hostname'], '.');
            } else {
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
        } else {
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
            } else {
                $text = '';
                while (!feof($diff)) {
                    $text .= fread($diff, 8192);
                }

                fclose($diff);
                fclose($errors);
            }
        } else {
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
    } elseif ($config['oxidized']['enabled'] === true && isset($config['oxidized']['url'])) {
        // Try with hostname as set in librenms first
        $oxidized_hostname = $device['hostname'];
        // fetch info about the node and then a list of versions for that node
        $node_info = json_decode(file_get_contents($config['oxidized']['url'].'/node/show/'.$oxidized_hostname.'?format=json'), true);
        
        // Try other hostname format if Oxidized request failed
        if (! $node_info) {
            // Adjust hostname based on whether domain was already in it or not
            if (strpos($oxidized_hostname, '.') !== false) {
                // Use short name
                $oxidized_hostname = strtok($device['hostname'], '.');
            } elseif ($config['mydomain']) {
                $oxidized_hostname = $device['hostname'].'.'.$config['mydomain'];
            }

            // Try Oxidized again with new hostname, if it has changed
            if ($oxidized_hostname != $device['hostname']) {
                $node_info = json_decode(file_get_contents($config['oxidized']['url'].'/node/show/'.$oxidized_hostname.'?format=json'), true);
            }
        }

        if ($config['oxidized']['features']['versioning'] === true) { // fetch a list of versions
            $config_versions = json_decode(file_get_contents($config['oxidized']['url'].'/node/version?node_full='.(isset($node_info['full_name']) ? $node_info['full_name'] : $oxidized_hostname).'&format=json'), true);
        }

        $config_total = 1;
        if (is_array($config_versions)) {
            $config_total = count($config_versions);
        }

        if ($config_total > 1) {
            // populate current_version
            if (isset($_POST['config'])) {
                list($oid,$date,$version) = explode('|', mres($_POST['config']));
                $current_config = array('oid'=>$oid, 'date'=>$date, 'version'=>$version);
            } else { // no version selected
                $current_config = array('oid'=>$config_versions[0]['oid'], 'date'=>$current_config[0]['date'], 'version'=>$config_total);
            }

            // populate previous_version
            if (isset($_POST['diff'])) { // diff requested
                list($oid,$date,$version) = explode('|', mres($_POST['prevconfig']));
                if (isset($oid) && $oid != $current_config['oid']) {
                    $previous_config = array('oid'=>$oid, 'date'=>$date, 'version'=>$version);
                } elseif ($current_config['version'] != 1) {  // assume previous, unless current is first config
                    foreach ($config_versions as $key => $version) {
                        if ($version['oid'] == $current_config['oid']) {
                            $prev_key = $key + 1;
                            $previous_config['oid']     = $config_versions[$prev_key]['oid'];
                            $previous_config['date']    = $config_versions[$prev_key]['date'];
                            $previous_config['version'] = $config_total - $prev_key;
                            break;
                        }
                    }
                } else {
                    print_error('No previous version, please select a different version.');
                }
            }

            if (isset($previous_config)) {
                $url = $config['oxidized']['url'].'/node/version/diffs?node='.$oxidized_hostname.'&group=';
                if (!empty($node_info['group'])) {
                    $url .= $node_info['group'];
                }
                $url .= '&oid='.$current_config['oid'].'&date='.urlencode($current_config['date']).'&num='.$current_config['version'].'&oid2='.$previous_config['oid'].'&format=text';

                $text = file_get_contents($url); // fetch diff
            } else {
                // fetch current_version
                $text = file_get_contents($config['oxidized']['url'].'/node/version/view?node='.$oxidized_hostname.'&group='.(!empty($node_info['group']) ? $node_info['group'] : '').'&oid='.$current_config['oid'].'&date='.urlencode($current_config['date']).'&num='.$current_config['version'].'&format=text');
            }
        } else {  // just fetch the only version
            $text = file_get_contents($config['oxidized']['url'].'/node/fetch/'.(!empty($node_info['group']) ? $node_info['group'].'/' : '').$oxidized_hostname);
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
                    if ($current_config['oid'] == $version['oid']) {
                        if (isset($previous_config)) {
                            echo 'selected>+';
                        } else {
                            echo 'selected>*';
                        }
                    } elseif ($previous_config['oid'] == $version['oid']) {
                        echo '>&nbsp;-';
                    } else {
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
                echo implode('|', $current_config);
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
        } else {
            echo '<br />';
            print_error("We couldn't retrieve the device information from Oxidized");
            $text = '';
        }
    }//end if

    if (!empty($text)) {
        if (isset($previous_config)) {
            $language = 'diff';
        } else {
            $language = 'ios';
        }
        $geshi = new GeSHi($text, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        $geshi->set_overall_style('color: black;');
        // $geshi->set_line_style('color: #999999');
        echo '<div class="config">';
        echo '<input id="linenumbers" class="btn btn-primary" type="submit" value="Hide line numbers"/>';
        echo $geshi->parse_code();
        echo '</div>';
    }
}//end if

$pagetitle[] = 'Config';
