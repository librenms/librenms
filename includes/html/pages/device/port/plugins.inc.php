<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2018 PipoCanaja <pipocanaja@gmail.com>
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

$pagetitle[] = 'Plugins';
$no_refresh = true;
?>

<h3>Plugins</h3>
<hr>
<?php
echo \LibreNMS\Plugins::call('port_container', [$device, $port]);
