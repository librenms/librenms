<?php

namespace App\Graphs\Application;

class NfsServerRpc6Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 6;
    protected const string PALETTE = 'rainbow';
}
