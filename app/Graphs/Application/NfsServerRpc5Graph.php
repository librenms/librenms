<?php

namespace App\Graphs\Application;

class NfsServerRpc5Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 5;
    protected const string PALETTE = 'rainbow';
}
