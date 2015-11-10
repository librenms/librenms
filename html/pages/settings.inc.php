<?php

/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 */

/*
 * Global Settings
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Page
 */

if (empty($vars['sub'])) {
    $page_name = 'Global';
}
else {
    $page_name = ucfirst($vars['sub']);
}

$pagetitle[] = $page_name . ' Settings';
$config['memcached']['enable'] = false;
?>

<div class="container-fluid">
    <h2>
<?php

echo $pagetitle[0];

?>
    </h2>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <span id="message"></span>
        </div>
    </div>
</div>

<?php

if (isset($vars['sub'])) {

    if (file_exists("pages/settings/".mres($vars['sub']).".inc.php")) {
        require_once "pages/settings/".mres($vars['sub']).".inc.php";
    }
    else {
        print_error("This settings page doesn't exist, please go to the main settings page");
    }

}
else {

?>

<div class="container-fluid">
    <div class="row">
<?php
    foreach (dbFetchRows("SELECT `config_group` FROM `config` GROUP BY `config_group`") as $sub_page) {
        $sub_page = $sub_page['config_group'];
?>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <a class="btn btn-primary" href="<?php echo(generate_url(array('page'=>'settings','sub'=>$sub_page))); ?>"><?php echo ucfirst($sub_page); ?> Settings</a>
        </div>
<?php
    }
?>
    </div>
</div>
<br />
<?php

/**
 * Array-To-Table
 * @param array $a N-Dimensional, Associative Array
 * @return string
 */

    function a2t($a) {
        $r = "<table class='table table-condensed table-hover'><tbody>";
        foreach( $a as $k=>$v ) {
            if( !empty($v) ) {
                $r .= "<tr><td class='col-md-2'><i><b>".$k."</b></i></td><td class='col-md-10'>".(is_array($v)?a2t($v):"<code>".wordwrap($v,75,"<br/>")."</code>")."</td></tr>";
            }
        }
        $r .= '</tbody></table>';
        return $r;
    }
    if( $_SESSION['userlevel'] >= 10 ) {
        echo "<div class='table-responsive'>".a2t($config)."</div>";
    }
    else {
        include 'includes/error-no-perm.inc.php';
    }

    if ($_SESSION['userlevel'] >= '10') {

        if ($debug) {
            echo("<pre>");
            print_r($config);
            echo("</pre>");
        }
    }
    else {
        include 'includes/error-no-perm.inc.php';
    }
}
?>
