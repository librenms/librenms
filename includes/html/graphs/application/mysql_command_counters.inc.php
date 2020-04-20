<?php
/*
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
* @copyright  2020 LibreNMS
* @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

require 'includes/html/graphs/common.inc.php';

$scale_min=0;
$colours='mixed';
$unit_text='Stats';
$unitlen=6;
$bigdescrlen=25;
$smalldescrlen=25;
$dostack=0;
$printtotal=0;
$addarea=1;
$transparency=33;
$rrd_filename=rrd_name($device['hostname'],array('app',$app['app_type'],$app['app_id']));

$array=array(
    'com_delete'=>array('descr'=>'Delete','colour'=>'ff0000',),
    'com_insert'=>array('descr'=>'Insert','colour'=>'5ac18e',),
    'com_insert_select'=>array('descr'=>'Insert Select','colour'=>'008080',),
    'com_load'=>array('descr'=>'Load data','colour'=>'003366',),
    'com_replace'=>array('descr'=>'Replace','colour'=>'ffa500',),
    'com_replace_select'=>array('descr'=>'Replace Select','colour'=>'ff7f50',),
    'com_select'=>array('descr'=>'Select','colour'=>'4ca3dd',),
    'com_update'=>array('descr'=>'Update','colour'=>'20b2aa',),
    'com_update_multi'=>array('descr'=>'Update Multiple','colour'=>'ff6666',),
);

$i=0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds=>$var) {
        $rrd_list[$i]['filename']=$rrd_filename;
        $rrd_list[$i]['descr']=$var['descr'];
        $rrd_list[$i]['ds']=$ds;
        $rrd_list[$i]['colour']=$var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
