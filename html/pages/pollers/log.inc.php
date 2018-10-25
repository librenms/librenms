<?php
/**
 * log.inc.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$no_refresh = true;
$pagetitle[] = 'Poll Log';
if (isset($vars['filter'])) {
    $type = $vars['filter'];
}
?>
<table id="poll-log" class="table table-condensed table-hover table-striped">
    <thead>
    <tr>
        <th data-column-id="hostname">Hostname</th>
        <th data-column-id="last_polled">Last Polled</th>
        <th data-column-id="poller_group">Poller Group</th>
        <th data-column-id="last_polled_timetaken" data-order="desc">Polling Duration (Seconds)</th>
    </tr>
    </thead>
</table>

<script>

    searchbar = "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
        "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">"+
        "<a href='<?php echo generate_url(['page' => 'pollers', 'tab' => 'log']); ?>' class='btn btn-primary btn-sm <?php echo $vars['filter'] == 'unpolled' ? '' : 'active' ?>'>All devices</a> "+
        "<a href='<?php echo generate_url(['page' => 'pollers', 'tab' => 'log', 'filter' => 'unpolled']); ?>' class='btn btn-danger btn-sm <?php echo $vars['filter'] == 'unpolled' ? 'active' : '' ?>'>Unpolled devices</a>"+
        "</div><div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div>";

    var grid = $("#poll-log").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        columnSelection: false,
        templates: {
            header: searchbar
        },
        post: function ()
        {
            return {
                id: "poll-log",
                type: "<?php echo $type;?>"
            };
        },
        url: "ajax_table.php"
    });

</script>
