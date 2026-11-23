<?php

namespace App\Graphs\Application;

class NfsServerRpc1Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 1;
    protected const string PALETTE = 'rainbow';
}
