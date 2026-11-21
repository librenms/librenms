<?php

namespace LibreNMS\Applications;

class Freeradius extends ApplicationDefinition
{
    public const string TYPE = 'freeradius';
    protected const ?string DOCS = 'FreeRADIUS';
    protected const ?string NAME = 'FreeRADIUS';

    protected const array GRAPHS = [
        'access' => 'FreeRADIUS - Access',
        'auth' => 'FreeRADIUS - Auth',
        'acct' => 'FreeRADIUS - Acct',
        'proxy_access' => 'FreeRADIUS - Proxy Access',
        'proxy_auth' => 'FreeRADIUS - Proxy Auth',
        'proxy_acct' => 'FreeRADIUS - Proxy Acct',
        'queue' => 'FreeRADIUS - Queue',
    ];
}
