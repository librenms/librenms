<?php

// FIXME svn stuff still using optc etc, won't work, needs updating!
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Process\Process;

if (Gate::allows('showConfig', DeviceCache::getPrimary())) {
    if (LibrenmsConfig::get('rancid_repo_type') == 'git-bare' && is_dir($rancid_path)) {
        echo '<div style="clear: both;">';

        print_optionbar_start('', '');
        echo is_null(LibrenmsConfig::get('rancid_repo_url')) ? 'Git repository non-browsable'
            : '<a href="' . htmlspecialchars(LibrenmsConfig::get('rancid_repo_url')) . '/?a=blob;hb=HEAD;p=' . basename((string) $rancid_path) . ';f=' . $rancid_file . '">Git repository</a>';
        print_optionbar_end();

        $process = new Process(['git', 'ls-tree', '-r', 'HEAD'], $rancid_path);
        $process->run();
        $full_tree = explode(PHP_EOL, $process->getOutput());
        foreach ($full_tree as $ft) {
            [$perm, $type, $hash_path] = explode(' ', $ft, 3);
            [$hash, $file] = explode("\t", $hash_path);
            if (strcmp($file, (string) $rancid_file) === 0) {
                $process = new Process(['git', 'cat-file', $type, $hash], $rancid_path);
                $process->run();
                $text = $process->getOutput();
            }
        }
    } elseif (! empty($rancid_file)) {
        echo '<div style="clear: both;">';

        print_optionbar_start('', '');

        echo "<span style='font-weight: bold;'>Config</span> &#187; ";

        if (empty($vars['rev'])) {
            echo '<span class="pagemenu-selected">';
            echo generate_link('Latest', ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig']);
            echo '</span>';
        } else {
            echo generate_link('Latest', ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig']);
        }

        if (LibrenmsConfig::get('rancid_repo_type') == 'svn') {
            $svn_binary = LibrenmsConfig::locateBinary('svn');
            if (is_executable($svn_binary)) {
                $sep = ' | ';

                $process = new Process([$svn_binary, 'log', '-l 8', '-q', '--xml', $rancid_file], $rancid_path);
                $process->run();
                $svnlogs_xmlstring = $process->getOutput();
                $svnlogs = [];

                $svnlogs_xml = simplexml_load_string($svnlogs_xmlstring);
                foreach ($svnlogs_xml->logentry as $svnlogentry) {
                    $rev = $svnlogentry['revision'];
                    $ts = strtotime($svnlogentry->date);
                    $svnlogs[] = ['rev' => $rev, 'date' => $ts];
                }

                $revlist = [];

                foreach ($svnlogs as $svnlog) {
                    echo $sep;
                    $revlist[] = $svnlog['rev'];

                    if ($vars['rev'] == $svnlog['rev']) {
                        echo '<span class="pagemenu-selected">';
                    }

                    $linktext = 'r' . $svnlog['rev'] . ' <small>' . date(LibrenmsConfig::get('dateformat.byminute'), $svnlog['date']) . '</small>';
                    echo generate_link($linktext, ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig', 'rev' => $svnlog['rev']]);

                    if ($vars['rev'] == $svnlog['rev']) {
                        echo '</span>';
                    }

                    $sep = ' | ';
                }
            }
        }//end if
        if (LibrenmsConfig::get('rancid_repo_type') == 'git') {
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

                $linktext = 'r' . $gitlog['rev'] . ' <small>' . date(LibrenmsConfig::get('dateformat.byminute'), $gitlog['date']) . '</small>';
                echo generate_link($linktext, ['page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig', 'rev' => $gitlog['rev']]);

                if ($vars['rev'] == $gitlog['rev']) {
                    echo '</span>';
                }

                $sep = ' | ';
            }
        }

        print_optionbar_end();

        if (LibrenmsConfig::get('rancid_repo_type') == 'svn') {
            $svn_binary = LibrenmsConfig::locateBinary('svn');
            if (is_executable($svn_binary) && in_array($vars['rev'], $revlist)) {
                $process = new Process([$svn_binary, 'diff', '-c', 'r' . $vars['rev'], $rancid_file], $rancid_path);
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
        } elseif (LibrenmsConfig::get('rancid_repo_type') == 'git') {
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

        if (LibrenmsConfig::get('rancid_ignorecomments')) {
            $lines = explode("\n", (string) $text);
            for ($i = 0; $i < count($lines); $i++) {
                if ($lines[$i][0] == '#') {
                    unset($lines[$i]);
                }
            }

            $text = implode("\n", $lines);
        }
    }//end if

    if (! empty($author)) {
        echo '
                          <div class="panel panel-primary">
                              <div class="panel-heading">Author: <strong>' . e($author) . '</strong></div>';
        if (! empty($msg)) {
            echo '
                              <ul class="list-group">
                                  <li class="list-group-item"><strong>Message:</strong> ' . e($msg) . '</li>
                              </ul>';
        }
        echo '
                          </div>';
    }
    if (! empty($text)) {
        $language = isset($previous_config) ? 'diff' : LibrenmsConfig::getOsSetting($device['os'], 'config_highlighting', 'ios');
        $geshi = new GeSHi(htmlspecialchars_decode((string) $text, ENT_QUOTES | ENT_HTML5), $language);
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
