<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 */

/**
 * Package Search
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */
print_optionbar_start(28);
?>
<form method="post" action="" class="form-inline" role="form">
    <?php echo csrf_field() ?>
    <div class="form-group">
        <label for="package">Package</label>
        <input type="text" name="package" id="package" size=20 value="<?php echo $_POST['package']; ?>" class="form-control input-sm" placeholder="Any" />
    </div>
    <div class="form-group">
        <label for="version">Version</label>
        <input type="text" name="version" id="version" size=20 value="<?php echo $_POST['version']; ?>" class="form-control input-sm" placeholder="Any" />
    </div>
    <div class="form-group">
        <label for="version">Arch</label>
        <input type="text" name="arch" id="arch" size=20 value="<?php echo $_POST['arch']; ?>" class="form-control input-sm" placeholder="Any" />
    </div>
    <button type="submit" class="btn btn-default input-sm">Search</button>
</form>
<?php
print_optionbar_end();

if (isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

?>
<form method="post" action="search/search=packages/" id="result_form">
    <?php echo csrf_field() ?>
    <table class="table table-hover table-condensed table-striped">
        <tr>
            <td colspan="3"><strong>Packages</strong></td>
            <td><select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">
                <?php
                $result_options = ['10', '50', '100', '250', '500', '1000', '5000'];
                foreach ($result_options as $option) {
                    echo "<option value='$option'";
                    if ($results == $option) {
                        echo ' selected';
                    }
                    echo ">$option</option>";
                }
                ?>
            </select></td>
        </tr>
<?php

$count_query = 'SELECT COUNT(*) FROM ( ';
$full_query = '';
$query = 'SELECT packages.name FROM packages,devices ';
$param = [];

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$query .= " WHERE packages.device_id = devices.device_id AND packages.name LIKE '%" . $_POST['package'] . "%' $sql_where GROUP BY packages.name";

$where = '';
$ver = '';
$opt = '';

if (! empty($_POST['arch'])) {
    $where .= ' AND packages.arch = ?';
    $param[] = $_POST['arch'];
}

if (is_numeric($_REQUEST['device_id'])) {
    $where .= ' AND packages.device_id = ?';
    $param[] = $_REQUEST['device_id'];
}

$count_query .= $query . ' ) sub';
$query .= $where . ' ORDER BY packages.name, packages.arch, packages.version';
$count = dbFetchCell($count_query, $param);

if (! isset($_POST['page_number']) && $_POST['page_number'] < 1) {
    $page_number = 1;
} else {
    $page_number = $_POST['page_number'];
}

$start = ($page_number - 1) * $results;
$full_query = $full_query . $query . " LIMIT $start,$results";

?>
        <tr>
            <th>Package</th>
            <th>Version</th>
            <th>Arch</th>
            <th>Device</th>
        </tr>
<?php

$ordered = [];
foreach (dbFetchRows($full_query, $param) as $entry) {
    $tmp = dbFetchRows('SELECT packages.*,devices.hostname FROM packages,devices WHERE packages.device_id=devices.device_id AND packages.name = ?', [$entry['name']]);
    foreach ($tmp as $entry) {
        if (! is_array($ordered[$entry['name']])) {
            $ordered[$entry['name']] = [$entry];
        } else {
            $ordered[$entry['name']][] = $entry;
        }
    }
}

if (! empty($_POST['version'])) {
    [$opt, $ver] = explode(' ', $_POST['version']);
}

foreach ($ordered as $name => $entry) {
    $vers = [];
    $arch = [];
    $devs = [];
    foreach ($entry as $variation) {
        $variation['version'] = str_replace(':', '.', $variation['version']);
        if (! in_array($variation['version'], $vers) && (empty($ver) || version_compare($variation['version'], $ver, $opt))) {
            $vers[] = $variation['version'];
        }
        if (! in_array($variation['arch'], $arch)) {
            $arch[] = $variation['arch'];
        }
        if (! in_array($variation['hostname'], $devs)) {
            unset($variation['version']);
            $devs[] = generate_device_link($variation);
        }
    }
    if (sizeof($arch) > 0 && sizeof($vers) > 0) {
        ?>
        <tr>
            <td><a href="<?php echo \LibreNMS\Util\Url::generate(['page' => 'packages', 'name' => $name]); ?>"><?php echo $name; ?></a></td>
            <td><?php echo implode('<br/>', $vers); ?></td>
            <td><?php echo implode('<br/>', $arch); ?></td>
            <td><?php echo implode('<br/>', $devs); ?></td>
        </tr>
        <?php
    }
}
if ((int) ($count / $results) > 0 && $count != $results) {
    ?>
        <tr>
            <td colspan="6" align="center"><?php echo generate_pagination($count, $results, $page_number); ?></td>
        </tr>
    <?php
}
?>

    </table>
    <input type="hidden" name="page_number" id="page_number" value="<?php echo $page_number; ?>">
    <input type="hidden" name="results_amount" id="results_amount" value="<?php echo $results; ?>">
    <input type="hidden" name="package" id="results_packages" value="<?php echo $_POST['package']; ?>">
    <input type="hidden" name="version" id="results_version" value="<?php echo $_POST['version']; ?>">
    <input type="hidden" name="arch" id="results_arch" value="<?php echo $_POST['arch']; ?>">
</form>
<script type="text/javascript">
    function updateResults(results) {
       $('#results_amount').val(results.value);
       $('#page_number').val(1);
       $('#result_form').trigger( "submit" );
    }

    function changePage(page,e) {
        e.preventDefault();
        $('#page_number').val(page);
        $('#result_form').trigger( "submit" );
    }
</script>
