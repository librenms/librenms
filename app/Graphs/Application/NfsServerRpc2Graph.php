<?php

namespace App\Graphs\Application;

class NfsServerRpc2Graph extends NfsGraph
{
    protected const string STATS = 'server_rpc';
    protected const ?int PART = 2;
    protected const string PALETTE = 'rainbow';
}
