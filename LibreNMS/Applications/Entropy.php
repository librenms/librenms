<?php

namespace LibreNMS\Applications;

class Entropy extends ApplicationDefinition
{
    public const string TYPE = 'entropy';
    protected const ?string DOCS = 'Entropy';
    protected const ?string NAME = 'Random entropy';

    protected const array GRAPHS = [
        'entropy' => 'Random entropy',
    ];
}
