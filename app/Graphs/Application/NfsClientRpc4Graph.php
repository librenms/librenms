<?php

namespace App\Graphs\Application;

class NfsClientRpc4Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 4;
    protected const string PALETTE = 'rainbow';
}
