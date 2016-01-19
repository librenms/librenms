<?php

require 'includes/geshi/geshi.php';

// FIXME svn stuff still using optc etc, won't work, needs updating!
if ($_SESSION['userlevel'] >= '7') {
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
        $node_info = json_decode(file_get_contents($config['oxidized']['url'].'/node/show/'.$device['hostname'].'?format=json'), true);
        if ($config['oxidized']['features']['versioning'] === true && isset($_POST['config'])) {
            list($oid,$date,$version) = explode('|',mres($_POST['config']));
            $text = file_get_contents($config['oxidized']['url'].'/node/version/view?node='.$device['hostname'].'&group=&oid='.$oid.'&date='.urlencode($date).'&num='.$version.'&format=text');
            if ($text == 'node not found') {
                $text = file_get_contents($config['oxidized']['url'].'/node/version/view?node='.$device['hostname'].'&group='.(is_array($node_info) ? $node_info['group'] : $device['os']).'&oid='.$oid.'&date='.urlencode($date).'&num='.$version.'&format=text');
            }
        }
        else {
            $text      = file_get_contents($config['oxidized']['url'].'/node/fetch/'.$device['hostname']);
            if ($text == 'node not found') {
                $text = file_get_contents($config['oxidized']['url'].'/node/fetch/'.(is_array($node_info) ? $node_info['group'] : $device['os']).'/'.$device['hostname']);
            }
        }
        if ($config['oxidized']['features']['versioning'] === true) {
            $config_versions = json_decode(file_get_contents($config['oxidized']['url'].'/node/version?node_full='.$device['hostname'].'&format=json'), true);
        }

        if (is_array($node_info) || is_array($config_versions)) {
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

            if (is_array($config_versions)) {
                echo '
                    <div class="col-sm-8">
                        <form class="form-horizontal" action="" method="post">
                            <div class="form-group">
                                <label for="config" class="col-sm-2 control-label">Config version</label>
                                <div class="col-sm-6">
                                    <select id="config" name="config" class="form-control">
                                        <option value="">Select version</option>
                ';

                $config_total = count($config_versions);
                foreach ($config_versions as $version) {
                    echo '<option value="'.$version['oid'].'|'.$version['date'].'|'.$config_total.'">'.$config_total.' :: '.$version['date'].' - '.$version['message'].'</option>';
                    $config_total--;
                }

                echo '
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-6">
                                      <button type="submit" class="btn btn-primary btn-sm">Show version</button>
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
        $language = 'ios';
        $geshi    = new GeSHi($text, $language);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
        $geshi->set_overall_style('color: black;');
        // $geshi->set_line_style('color: #999999');
        echo $geshi->parse_code();
    }
}//end if

$pagetitle[] = 'Config';
