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
$unitlen=5;
$bigdescrlen=25;
$smalldescrlen=25;
$dostack=0;
$printtotal=0;
$addarea=1;
$transparency=33;
$rrd_filename=rrd_name($device['hostname'],array('app',$app['app_type'],$app['app_id']));

$array=array(
    'qcache_queries_in_cache'=>array('descr'=>'Queries in cache','colour'=>'ffa500',),
    'qcache_hits'=>array('descr'=>'Cache hits','colour'=>'5ac18e',),
    'qcache_inserts'=>array('descr'=>'Inserts','colour'=>'4ca3dd',),
    'qcache_not_cached'=>array('descr'=>'Not cached','colour'=>'800000',),
    'qcache_lowmem_prunes'=>array('descr'=>'Low-mem prunes','colour'=>'ff0000',),
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
