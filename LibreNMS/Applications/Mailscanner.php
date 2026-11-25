<?php

namespace LibreNMS\Applications;

class Mailscanner extends ApplicationDefinition
{
    public const string TYPE = 'mailscanner';
    protected const ?string DOCS = 'Mailscanner';

    protected const ?array OVERVIEW = [];

    protected const array GRAPHS = [
        'sent' => 'Mailscanner - Sent / Received',
        'spam' => 'Mailscanner - Spam / Virus',
        'reject' => 'Mailscanner - Rejected / Waiting / Relayed',
    ];
}
