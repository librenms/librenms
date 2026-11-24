<?php

namespace App\Graphs\Application;

class NfsClientRpc2Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 2;
    protected const string PALETTE = 'rainbow';
}
