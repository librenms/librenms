<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage opengridscheduler
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

namespace LibreNMS\Applications;

class Opengridscheduler extends ApplicationDefinition
{
    public const string TYPE = 'opengridscheduler';
    protected const ?string DOCS = 'Open Grid Scheduler';
    protected const ?string NAME = 'Open Grid Scheduler';

    protected const array GRAPHS = [
        'ogs' => 'Open Grid Scheduler Job Queue',
    ];
}
