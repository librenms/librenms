<?php
/**
 * rrdcleanup.inc.php
 *
 * Web page to display orphaned RRD Files
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
 * @copyright  2019 Thomas Berberich <sourcehhdoctor@gmail.com>
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

$no_refresh = true;

if (Auth::user()->hasGlobalAdmin()) {

    $delete_rrd_file_list = $_POST['rrd_files'];

    if (count($delete_rrd_file_list)) {
        $delete_result = delete_rrd_files($delete_rrd_file_list);

        $message_list = array();
        $error_list = array();

        foreach ($delete_result as $rrd_file) {
            if ($rrd_file['deleted']) {
                $message_list[] = $rrd_file['file'];
            }
            else {
                $error_list[] = $rrd_file['file'];
            }
        }
        if (count($message_list)) {
            $message = "<b>Deleting Files done</b>";
            print_message(join('<br>', array_merge(array($message), $message_list)));
        }
        if (count($error_list)) {
            $message = "<b>Deleting Files failed</b>";
            print_error(join('<br>', array_merge(array($message), $error_list)));
        }
    }

    $rrd_list = orphaned_rrd_files();

    ?>
    <div class="panel-group" id="accordion">
      <form name="form_rrd_cleanup" class="form-horizontal" action="" method="post" role="form">
      <?=csrf_field()?>
      <row>
        <legend>orphaned RRD Files</legend>
      </row>
    <?php
    foreach ($rrd_list as $host => $file_list) {
        $anchor = md5('rrd_cleanup_' . $host);
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#<?=$anchor?>" style="" class="collapsed" aria-expanded="false">
                <i class="fa fa-caret-down"></i>
                <?=$host?>&nbsp;(<?=count($file_list) ?>)
              </a>
            </h4>
          </div>
          <div id="<?=$anchor?>" class="panel-collapse collapse">
        <?php
        if (count($file_list)) {
        ?>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>delete File</th>
                  <th>Filename</th>
                  <th>Age</th>
                  <th>Last Modification</th>
                  <th>Size</th>
                </tr>
              </thead>
              <tbody>
            <?php
            foreach ($file_list as $file) {
            ?>
                <tr>
                  <td><input type="checkbox" name="rrd_files[]" id="rrd_files[]" value="<?=$host?>/<?=$file['name']?>"></td>
                  <td><?=$file['name'] ?></td>
                  <td><?=$file['age'] ?></td>
                  <td><?=$file['date'] ?></td>
                  <td><?=$file['size'] ?></td>
                </tr>
            <?php
            }
            ?>
              </tbody>
            </table>
        <?php
        }
        else {
        ?>
            no orphaned RRDs
        <?php
        }
        ?>
          </div>
        </div>
    <?php
    }
    ?>
        <hr>
        <button type="submit" name="submit" class="btn btn-danger">Remove selected RRD Files</button>
      </form>
    </div>
    <?php
} else {
    include 'includes/html/error-no-perm.inc.php';
}
?>
