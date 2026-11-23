<?php

namespace App\Graphs\Application;

class NfsServerRpc4Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 4;
    protected const string PALETTE = 'rainbow';
}
