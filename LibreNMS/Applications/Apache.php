<?php

namespace LibreNMS\Applications;

class Apache extends ApplicationDefinition
{
    public const string TYPE = 'apache';
    protected const ?string DOCS = 'Apache';

    protected const ?array OVERVIEW = [
        'bits',
        'hits',
        'scoreboard',
        'cpu',
    ];

    protected const array GRAPHS = [
        'bits' => 'Traffic',
        'hits' => 'Hits',
        'cpu' => 'CPU Utilisation',
        'scoreboard' => 'Scoreboard Statistics',
    ];
}
