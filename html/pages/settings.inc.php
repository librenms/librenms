<?php
/**
 * settings.inc.php
 *
 * Web page to display settings
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
 * @copyright  2015 Daniel Preussker <f0o@devilcode.org>
 * @copyright  2016 Tony Murray <murraytony@gmail.com>
 * @author     f0o <f0o@devilcode.org>
 */

$config['memcached']['enable'] = false;
?>

<div class="container-fluid">
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


if (is_admin() === true) {
    echo '<ul class="nav nav-tabs">';
    $pages = dbFetchRows("SELECT DISTINCT `config_group` FROM `config` WHERE `config_group` IS NOT NULL AND `config_group` != ''");
    array_unshift($pages, array('config_group' => 'Global')); // Add Global tab
    $curr_page = isset($vars['sub']) ? $vars['sub'] : 'Global';

    foreach ($pages as $sub_page) {
        $sub_page = $sub_page['config_group'];
        $page_name = ucfirst($sub_page) . ' Settings';
        echo '<li';
        if ($sub_page == $curr_page) {
            echo ' class="active"';
        }
        echo '><a href="';
        echo generate_url(array(
            'page' => 'settings',
            'sub' => $sub_page
        ));
        echo '">' . $page_name . '</a></li>';
    }

    echo '</ul></div></div><br />';

    if (isset($vars['sub']) && $vars['sub'] != 'Global') {
        if (file_exists("pages/settings/" . mres($vars['sub']) . ".inc.php")) {
            require_once "pages/settings/" . mres($vars['sub']) . ".inc.php";
        } else {
            print_error("This settings page doesn't exist, please go to the main settings page");
        }
    } else {

        /**
         * Array-To-Table
         * @param array $a N-Dimensional, Associative Array
         * @return string
         */

        function a2t($a)
        {
            $r = '<table class="table table-condensed table-hover"><tbody>';
            foreach ($a as $k => $v) {
                if (!empty($v)) {
                    $r .= '<tr><td class="col-md-2"><i><b>' . $k . '</b></i></td><td class="col-md-10">';
                    $r .= is_array($v) ? a2t($v) : '<code>' . wordwrap($v, 75, '<br/>') . '</code>';
                    $r .= '</td></tr>';
                }
            }
            $r .= '</tbody></table>';
            return $r;
        }

        echo '<div class="table-responsive">' . a2t($config) . '</div>';

        if ($debug && $_SESSION['userlevel'] >= '10') {
            echo("<pre>");
            print_r($config);
            echo("</pre>");
        }
    }
} else {
    include 'includes/error-no-perm.inc.php';
}
?>
</div>
