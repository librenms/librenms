<?php

/*

LibreNMS Application for monitoring power consumption and cost

@link       https://www.upaya.net.au/
@copyright  2021 Ben Carbery
@author     Ben Carbery <yrebrac@upaya.net.au>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

namespace LibreNMS\Applications;

class Powermon extends ApplicationDefinition
{
    public const string TYPE = 'powermon';
    protected const ?string DOCS = 'PowerMon';
    protected const ?string NAME = 'PowerMon';

    protected const array GRAPHS = [
        'consumption' => 'PowerMon - Consumption',
    ];
}
