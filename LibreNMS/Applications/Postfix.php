<?php

namespace LibreNMS\Applications;

class Postfix extends ApplicationDefinition
{
    public const string TYPE = 'postfix';
    protected const ?string DOCS = 'Postfix';

    protected const ?array OVERVIEW = [
        'messages',
        'qstats',
        'bytes',
        'sr',
        'deferral',
        'rejects',
    ];

    protected const array GRAPHS = [
        'qstats' => 'Queue',
        'messages' => 'Messages',
        'bytes' => 'Bytes',
        'sr' => 'Sender & Recipients',
        'deferral' => 'Deferals',
        'rejects' => 'Rejects',
    ];
}
