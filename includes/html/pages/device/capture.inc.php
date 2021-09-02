<?php
/**
 * capture.inc.php
 *
 * View and download troubleshooting information
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$no_refresh = true;
$pagetitle[] = 'Capture';

if (! Auth::user()->hasGlobalAdmin()) {
    print_error('Insufficient Privileges');
} else {
    ?>
    <h2>Capture Debug Information</h2>
    <ul class="nav nav-tabs">
        <li role="presentation" class="active"><a data-toggle="tab" href="#discovery">Discovery</a></li>
        <li role="presentation"><a data-toggle="tab" href="#poller">Poller</a></li>
        <li role="presentation"><a data-toggle="tab" href="#snmp">SNMP</a></li>
        <li role="presentation"><a data-toggle="tab" href="#alerts">Alerts</a></li>
    </ul>
    <div class="tab-content">
    <?php
    $tabs = [
        'discovery' => 'ajax_output.php?id=capture&format=text&type=discovery&hostname=' . $device['hostname'],
        'poller'    => 'ajax_output.php?id=capture&format=text&type=poller&hostname=' . $device['hostname'],
        'snmp'      => 'ajax_output.php?id=capture&format=text&type=snmpwalk&hostname=' . $device['hostname'],
        'alerts'    => 'ajax_output.php?id=query&format=text&type=alerts&hostname=' . $device['hostname'],
    ];

    foreach ($tabs as $tab => $url) {
        ?>
        <div id="<?php echo $tab ?>" class="tab-pane fade <?php echo $tab == 'discovery' ? ' in active' : '' ?>">
        <div class="row"><div class="col-md-12">
        <div class="btn-toolbar" role="toolbar" style="margin:5px 0 5px 0">
        <button type="button" class="btn btn-success" id="run-<?php echo $tab ?>"><i class="fa fa-play fa-lg"></i> Run</button>
        <button type="button" class="btn btn-primary" id="copy-<?php echo $tab ?>"><i class="fa fa-clipboard fa-lg"></i> Copy</button>
        <a class="btn btn-warning" href="<?php echo str_replace('text', 'download', $url) ?>"><i class="fa fa-download fa-lg"></i> Download</a>
        </div></div></div>
        <div class="row"><div class="col-md-12">
        <textarea readonly id="output-<?php echo $tab ?>" class="form-control" rows="30" placeholder="Output" style="resize:vertical;"></textarea>
        </div></div>
        </div>
        <script type="text/javascript">

            document.getElementById('copy-<?php echo $tab ?>').onclick = function() {
                output = document.getElementById("output-<?php echo $tab ?>");
                output.select();
                try {
                    document.execCommand('copy');
                } catch (err) {
                    alert('Unsupported Browser!');
                }
            };

            document.getElementById('run-<?php echo $tab ?>').onclick = function () {
                output = document.getElementById("output-<?php echo $tab ?>");
                xhr = new XMLHttpRequest();
                xhr.open("GET", "<?php echo $url ?>", true);
                xhr.onprogress = function (e) {
                    output.innerHTML = e.currentTarget.responseText;
                    output.scrollTop = output.scrollHeight - output.clientHeight; // scrolls the output area
                };
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        console.log("Complete");
                    }
                };
                xhr.send();
            };
        </script>
        <?php
    }
    echo '</div>';
}
