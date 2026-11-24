<?php

namespace App\Graphs\Application;

class NfsServerRpc3Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 3;
    protected const string PALETTE = 'rainbow';
}
