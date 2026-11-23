<?php

namespace App\Graphs\Application;

class NfsClientRpc6Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 6;
    protected const string PALETTE = 'rainbow';
}
