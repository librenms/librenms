<style>
    .list-group-item.active {
        background-color: rgb(217, 255, 0);
        /* Highlight color */
        color: white;
        /* Text color */
        font-weight: bold;
    }

    li.list-group-item.active:hover {
        color: #ffffff;
        background: yellow;
    }
</style>
<?php

// FIXME svn stuff still using optc etc, won't work, needs updating!
use LibreNMS\Config;
use Symfony\Component\Process\Process;



if (Auth::user()->hasGlobalAdmin()) {
    if (! empty($rancid_file)) {
        echo '<div style="clear: both;">';

        print_optionbar_start('', '');

        echo "<span style='font-weight: bold;'>Config</span> &#187; ";

        if (! $vars['rev']) {
            echo '<span class="pagemenu-selected">';
            echo generate_link('Latest', ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig']);
            echo '</span>';
        } else {
            echo generate_link('Latest', ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig']);
        }

        if (Config::get('rancid_repo_type') == 'svn' && function_exists('svn_log')) {
            $sep = ' | ';
            $svnlogs = svn_log($rancid_file, SVN_REVISION_HEAD, null, 8);
            $revlist = [];

            foreach ($svnlogs as $svnlog) {
                echo $sep;
                $revlist[] = $svnlog['rev'];

                if ($vars['rev'] == $svnlog['rev']) {
                    echo '<span class="pagemenu-selected">';
                }

                $linktext = 'r' . $svnlog['rev'] . ' <small>' . date(Config::get('dateformat.byminute'), strtotime($svnlog['date'])) . '</small>';
                echo generate_link($linktext, ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig', 'rev' => $svnlog['rev']]);

                if ($vars['rev'] == $svnlog['rev']) {
                    echo '</span>';
                }

                $sep = ' | ';
            }
        } //end if
        if (Config::get('rancid_repo_type') == 'git') {
            $sep = ' | ';

            $process = new Process(['git', 'log', '-n 8', '--pretty=format:%h;%ct', $rancid_file], $rancid_path);
            $process->run();
            $gitlogs_raw = explode(PHP_EOL, $process->getOutput());
            $gitlogs = [];

            foreach ($gitlogs_raw as $gl) {
                [$rev, $ts] = explode(';', $gl);
                $gitlogs[] = ['rev' => $rev, 'date' => $ts];
            }

            $revlist = [];

            foreach ($gitlogs as $gitlog) {
                echo $sep;
                $revlist[] = $gitlog['rev'];

                if ($vars['rev'] == $gitlog['rev']) {
                    echo '<span class="pagemenu-selected">';
                }

                $linktext = 'r' . $gitlog['rev'] . ' <small>' . date(Config::get('dateformat.byminute'), $gitlog['date']) . '</small>';
                echo generate_link($linktext, ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig', 'rev' => $gitlog['rev']]);

                if ($vars['rev'] == $gitlog['rev']) {
                    echo '</span>';
                }

                $sep = ' | ';
            }
        }

        print_optionbar_end();

        if (Config::get('rancid_repo_type') == 'svn') {
            if (function_exists('svn_log') && in_array($vars['rev'], $revlist)) {
                [$diff, $errors] = svn_diff($rancid_file, $vars['rev'] - 1, $rancid_file, $vars['rev']);
                if (! $diff) {
                    $text = 'No Difference';
                } else {
                    $text = '';
                    while (! feof($diff)) {
                        $text .= fread($diff, 8192);
                    }

                    fclose($diff);
                    fclose($errors);
                }
            } else {
                $fh = fopen($rancid_file, 'r');
                if ($fh === false) {
                    echo '<div class="alert alert-warning">Error: Cannot open Rancid configuration file for this device.</div>';

                    return;
                }
                if (filesize($rancid_file) == 0) {
                    echo '<div class="alert alert-warning">Error: Rancid configuration file for this device is empty.</div>';

                    return;
                }
                $text = fread($fh, filesize($rancid_file));
                fclose($fh);
            }
        } elseif (Config::get('rancid_repo_type') == 'git') {
            if (in_array($vars['rev'], $revlist)) {
                $process = new Process(['git', 'diff', $vars['rev'] . '^', $vars['rev'], $rancid_file], $rancid_path);
                $process->run();
                $diff = $process->getOutput();
                if (! $diff) {
                    $text = 'No Difference';
                } else {
                    $text = $diff;
                    $previous_config = $vars['rev'] . '^';
                }
            } else {
                $fh = fopen($rancid_file, 'r');
                if ($fh === false) {
                    echo '<div class="alert alert-warning">Error: Cannot open Rancid configuration file for this device.</div>';

                    return;
                }
                if (filesize($rancid_file) == 0) {
                    echo '<div class="alert alert-warning">Error: Rancid configuration file for this device is empty.</div>';

                    return;
                }
                $text = fread($fh, filesize($rancid_file));
                fclose($fh);
            }
        }

        if (Config::get('rancid_ignorecomments')) {
            $lines = explode("\n", $text);
            for ($i = 0; $i < count($lines); $i++) {
                if ($lines[$i][0] == '#') {
                    unset($lines[$i]);
                }
            }

            $text = join("\n", $lines);
        }
    } elseif (Config::get('oxidized.enabled') === true && Config::has('oxidized.url')) {
        // Try with hostname as set in librenms first
        $oxidized_hostname = $device['hostname'];
        // fetch info about the node and then a list of versions for that node
        $node_info = json_decode((new \App\ApiClients\Oxidized())->getContent('/node/show/' . $oxidized_hostname . '?format=json'), true);
        if (! empty($node_info['last']['start'])) {
            $node_info['last']['start'] = date(Config::get('dateformat.long'), strtotime($node_info['last']['start']));
        }
        if (! empty($node_info['last']['end'])) {
            $node_info['last']['end'] = date(Config::get('dateformat.long'), strtotime($node_info['last']['end']));
        }
        // Try other hostname format if Oxidized request failed
        if (! $node_info) {
            // Adjust hostname based on whether domain was already in it or not
            if (strpos($oxidized_hostname, '.') !== false) {
                // Use short name
                $oxidized_hostname = strtok($device['hostname'], '.');
            } elseif (Config::get('mydomain')) {
                $oxidized_hostname = $device['hostname'] . '.' . Config::get('mydomain');
            }

            // Try Oxidized again with new hostname, if it has changed
            if ($oxidized_hostname != $device['hostname']) {
                $node_info = json_decode((new \App\ApiClients\Oxidized())->getContent('/node/show/' . $oxidized_hostname . '?format=json'), true);
            }
        }

        if (Config::get('oxidized.features.versioning') === true) { // fetch a list of versions
            $config_versions = json_decode((new \App\ApiClients\Oxidized())->getContent('/node/version?node_full=' . (isset($node_info['full_name']) ? $node_info['full_name'] : $oxidized_hostname) . '&format=json'), true);
        }

        $config_total = 1;
        if (is_array($config_versions)) {
            $config_total = count($config_versions);
        }

        if ($config_total > 1) {
            // populate current_version
            if (isset($_POST['config'])) {
                [$oid, $date, $version] = explode('|', htmlspecialchars($_POST['config']));
                $current_config = ['oid' => $oid, 'date' => $date, 'version' => $version];
            } else { // no version selected
                $current_config = ['oid' => $config_versions[0]['oid'], 'date' => $config_versions[0]['date'], 'version' => $config_total];
            }

            // populate previous_version
            if (isset($_POST['diff'])) { // diff requested
                [$oid, $date, $version] = explode('|', $_POST['prevconfig']);
                if (isset($oid) && $oid != $current_config['oid']) {
                    $previous_config = ['oid' => $oid, 'date' => $date, 'version' => $version];
                } elseif ($current_config['version'] != 1) {  // assume previous, unless current is first config
                    foreach ($config_versions as $key => $version) {
                        if ($version['oid'] == $current_config['oid']) {
                            $prev_key = $key + 1;
                            $previous_config['oid'] = $config_versions[$prev_key]['oid'];
                            $previous_config['date'] = $config_versions[$prev_key]['date'];
                            $previous_config['version'] = $config_total - $prev_key;
                            break;
                        }
                    }
                } else {
                    print_error('No previous version, please select a different version.');
                }
            }

            if (isset($previous_config)) {
                $uri = '/node/version/diffs?node=' . $oxidized_hostname;
                if (! empty($node_info['group'])) {
                    $uri .= '&group=' . $node_info['group'];
                }
                $uri .= '&oid=' . urlencode($current_config['oid']) . '&date=' . urlencode($current_config['date']) . '&num=' . urlencode($current_config['version']) . '&oid2=' . $previous_config['oid'] . '&format=text';

                $text = (new \App\ApiClients\Oxidized())->getContent($uri); // fetch diff
            } else {
                // fetch current_version
                $text = (new \App\ApiClients\Oxidized())->getContent('/node/version/view?node=' . $oxidized_hostname . (! empty($node_info['group']) ? '&group=' . $node_info['group'] : '') . '&oid=' . urlencode($current_config['oid']) . '&date=' . urlencode($current_config['date']) . '&num=' . urlencode($current_config['version']) . '&format=text');
            }
        } else {  // just fetch the only version
            $text = (new \App\ApiClients\Oxidized())->getContent('/node/fetch/' . (! empty($node_info['group']) ? $node_info['group'] . '/' : '') . $oxidized_hostname);
        }

        if (is_array($node_info) || $config_total > 1) {
            echo '<br />
                <div class="row">
            ';

            if (is_array($node_info)) {
                // Dynamically fetch base URL
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];

                // Ensure the base URL is correct
                $uri_parts = explode('/', $_SERVER['REQUEST_URI']);
                $base_url = $protocol . $host . '/' . $uri_parts[1] . '/' . $uri_parts[2] . '/showconfig';

                // Extract the current configuration from the URL
                $current_config = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));


                // Check if any configuration is active
                $is_l2_config_expanded = in_array($current_config, [
                    "gvrp_config",
                    "stp_config",
                    "basic_arp",
                    "vlan_config",
                    "igmp_snooping",
                    "lldp_config",
                    "ddm_config",
                ]);

                echo '

                    <div class="col-md-4 col-sm-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                    <a class="btn btn-link" data-toggle="collapse" href="#l2ConfigDropdown" role="button" aria-expanded="' . ($is_l2_config_expanded ? 'true' : 'false') . '" aria-controls="l2ConfigDropdown" style="color:rgb(255, 255, 255); font-weight: bold; text-decoration: none;">
                        L2 Configuration
                    </a>
                    <a href="' . $base_url . '" class="close" style="float: right; color:rgb(255, 251, 251); font-size: 20px; text-decoration: none;" aria-label="Close">&times;</a>
                  </div>
                            <div id="l2ConfigDropdown" class="collapse ' . ($is_l2_config_expanded ? 'show' : '') . '">
                                <ul class="list-group" style="list-style-type: none; padding: 0; margin: 0;">
                                    <li class="list-group-item ' . ($current_config === "gvrp_config" ? "active" : "") . '">
                                        <a href="' . $base_url . '/gvrp_config">GVRP Configuration</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "stp_config" ? "active" : "") . '">
                                        <a href="' . $base_url . '/stp_config">STP Configuration</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "basic_arp" ? "active" : "") . '">
                                        <a href="' . $base_url . '/basic_arp">Basic ARP</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "vlan_config" ? "active" : "") . '">
                                        <a href="' . $base_url . '/vlan_config">VLAN Configuration</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "igmp_snooping" ? "active" : "") . '">
                                        <a href="' . $base_url . '/igmp_snooping">IGMP Snooping</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "lldp_config" ? "active" : "") . '">
                                        <a href="' . $base_url . '/lldp_config">LLDP Configuration</a>
                                    </li>
                                    <li class="list-group-item ' . ($current_config === "ddm_config" ? "active" : "") . '">
                                        <a href="' . $base_url . '/ddm_config">DDM Configuration</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                        <div class="col-md-8 col-sm-12">
                        <div class="panel panel-primary">
                        <div class="panel-heading">Tab: <strong>' . $current_config . '</strong></div>
    ';
    
    // Print "demo1" if the current configuration is gvrp_config
            if ($current_config === "gvrp_config") {
                echo "<div class='config'>gvrp config</div>";
            }
            if ($current_config === "stp_config") {
                echo "stp config";
            }

    echo '
        </div>
        </div>

        <div class="col-md-12 col-sm-12">
        </div>
 
                    <div class="col-md-4 col-sm-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">Sync status: <strong>' . $node_info['last']['status'] . '</strong></div>
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Node:</strong> ' . $node_info['name'] . '</li>
                                <li class="list-group-item"><strong>IP:</strong> ' . $node_info['ip'] . '</li>
                                <li class="list-group-item"><strong>Model:</strong> ' . $node_info['model'] . '</li>
                                <li class="list-group-item" style="overflow:hidden">
                                    <strong>Last Sync:</strong> ' . $node_info['last']['end'] . ' 
                                    &nbsp;<button class="btn btn-primary btn-xs" style="float: right;" name="queue-refresh" onclick=\'refresh_oxidized_node("' . $device['hostname'] . '")\'>Refresh</button>
                                </li>
                            </ul>
                        </div>
                    </div>

                   

                    
                ';
            }













            if ($config_total > 1) {
                echo '
                    <div class="col-sm-8">
                        <form class="form-horizontal" action="" method="post">
                            ' . csrf_field() . '
                            <div class="form-group">
                                <label for="config" class="col-sm-2 control-label">Config version</label>
                                <div class="col-sm-6">
                                    <select id="config" name="config" class="form-control">
                ';

                $i = $config_total;
                foreach ($config_versions as $version) {
                    echo '<option value="' . $version['oid'] . '|' . $version['date'] . '|' . $config_total . '" ';
                    if ($current_config['oid'] == $version['oid']) {
                        $author = $version['author']['name'];
                        $msg = $version['message'];
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
                    echo $i . ' :: ' . $version['date'] . '</option>';
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
    } //end if

    if (! empty($author)) {
        echo '
                          <div class="panel panel-primary">
                              <div class="panel-heading">Author: <strong>' . $author . '</strong></div>';
        if (! empty($msg)) {
            echo '
                              <ul class="list-group">
                                  <li class="list-group-item"><strong>Message:</strong> ' . $msg . '</li>
                              </ul>';
        }
        echo '
                          </div>';
    }
    if (!empty($text)) {
        $language = isset($previous_config) ? 'diff' : Config::getOsSetting($device['os'], 'config_highlighting', 'ios');
        $geshi = new GeSHi(htmlspecialchars_decode($text, ENT_QUOTES | ENT_HTML5), $language);
    
        // Extract command blocks using refined regex
        preg_match_all('/! (show [^\n]+)(.*?)(?=\n! show|\z)/s', $geshi->source, $matches, PREG_SET_ORDER);
    
        $commands = [];
        foreach ($matches as $match) {
            $command = trim($match[1]);
            $details = trim($match[2]);
            $commands[$command] = $details;
        }
    
        // Debugging the commands array
        // dd($commands);
    
        // Generate the dropdown
        echo '<div class="config">';
        echo '<div class="col-md-4 col-sm-12">';
    
        echo '<select id="commandDropdown" class="form-control">';
        foreach ($commands as $cmd => $details) {
            echo "<option value=\"" . htmlspecialchars($details) . "\">$cmd</option>";
        }
        echo '</select>';
        echo '</div>';
        echo '<button id="showDetails" class="btn btn-primary">Show Details</button>';
        echo '<pre id="commandDetails" style="display:none; margin-top: 10px; border: 1px solid #ccc; padding: 10px;"></pre>';
        echo '</div>';
    
        // Add JavaScript to handle dropdown change
        echo <<<HTML
    <script>
    document.getElementById('showDetails').addEventListener('click', function() {
        var dropdown = document.getElementById('commandDropdown');
        var details = dropdown.options[dropdown.selectedIndex].value;
        var detailsBox = document.getElementById('commandDetails');
        detailsBox.textContent = details;
        detailsBox.style.display = 'block';
    });
    </script>
    HTML;
    }
    
    
    
    
    
    
    
} //end if

$pagetitle[] = 'Config';

?>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>