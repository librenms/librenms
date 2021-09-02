<?php
/**
 * validate.inc.php
 *
 * -Description-
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\ValidationResult;
use LibreNMS\Validator;

$no_refresh = true;

?>

<div class="container-fluid" id="messagebox">
    <div class="row">
        <div class="col-md-12">
            <span id="message"></span>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">

<?php

$validator = new Validator();
$validator->validate();

foreach ($validator->getAllResults() as $group => $results) {
    echo '<div class="panel-group" style="margin-bottom: 5px"><div class="panel panel-default"><div class="panel-heading"> ';
    echo "<h4 class='panel-title'><a data-toggle='collapse' data-target='#${group}Body'>";
    echo ucfirst($group);

    $group_status = $validator->getGroupStatus($group);

    if ($group_status == ValidationResult::SUCCESS) {
        echo ' <span class="text-success pull-right">Ok</span>';
    } elseif ($group_status == ValidationResult::WARNING) {
        echo ' <span class="text-warning pull-right">Warning</span>';
    } elseif ($group_status == ValidationResult::FAILURE) {
        echo ' <span class="text-danger pull-right">Failure</span>';
    }
    echo '</a></h4>';
    echo " </div><div id='${group}Body' class='panel-collapse collapse";
    if ($group_status !== ValidationResult::SUCCESS) {
        echo ' in';
    }

    echo "'><div class='panel-body'>";

    foreach ($results as $rnum => $result) {
        /** @var ValidationResult $result */
        echo '<div class="panel';
        if ($result->getStatus() == ValidationResult::SUCCESS) {
            echo ' panel-success"><div class="panel-heading bg-success"> Ok: ';
        } elseif ($result->getStatus() == ValidationResult::WARNING) {
            echo ' panel-warning"><div class="panel-heading bg-warning"> Warning: ';
        } elseif ($result->getStatus() == ValidationResult::FAILURE) {
            echo ' panel-danger"><div class="panel-heading bg-danger"> Fail: ';
        }

        echo $result->getMessage();
        echo '</div>';

        if ($result->hasFix() || $result->hasList()) {
            echo '<div class="panel-body">';
            if ($result->hasFix()) {
                echo 'Fix: <code>';
                foreach ((array) $result->getFix() as $fix) {
                    echo '<br />' . linkify($fix) . PHP_EOL;
                }
                echo '</code>';
                if ($result->hasList()) {
                    echo '<br /><br />';
                }
            }

            if ($result->hasList()) {
                $list = $result->getList();
                $short_size = 10;

                echo "<ul id='shortList$group$rnum' class='list-group' style='margin-bottom: -1px'>";
                echo "<li class='list-group-item active'>" . $result->getListDescription() . '</li>';

                foreach (array_slice($list, 0, $short_size) as $li) {
                    echo "<li class='list-group-item'>$li</li>";
                }
                echo '</ul>';

                if (count($list) > $short_size) {
                    echo "<button style='margin-top: 3px' type='button' class='btn btn-default' id='button$group$rnum'";
                    echo " onclick='expandList(\"$group$rnum\");'>Show all</button>";

                    echo "<ul id='extraList$group$rnum' class='list-group' style='display:none'>";

                    foreach (array_slice($list, $short_size) as $li) {
                        echo "<li class='list-group-item'>$li</li>";
                    }
                    echo '</ul>';
                }
            }
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div></div></div></div>';
}

?>
        </div>
    </div>
</div>
<script>
    function expandList($id) {
        var item = $("#extraList" + $id);
        console.log(item);
        $("#extraList" + $id).show();
        $("#button" + $id).hide();
    }
</script>
