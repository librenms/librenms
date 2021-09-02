<?php
/**
 * LibreNMS PeeringDB Integration
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$asn = $vars['bgpLocalAs'];

?>
<div class="row">
     <div class="col-sm-4">
         <div class="table-responsive">
             <table id="ixlist" class="table table-bordered table-striped">
                 <thead>
                     <tr>
                         <th data-column-id="exchange" data-sortable="false">Exchange</th>
                         <th data-column-id="action" data-sortable="false"></th>
                         <th data-column-id="links" data-sortable="false"></th>
                     </tr>
                 </thead>
             </table>
         </div>
     </div>
</div>

<script>
    var grid = $("#ixlist").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id:          'ix-list',
                asn:         '<?php echo $asn; ?>',
            };
        },
        url: "ajax_table.php"
    });
</script>
