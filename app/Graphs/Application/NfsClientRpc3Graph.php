<?php

namespace App\Graphs\Application;

class NfsClientRpc3Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 3;
    protected const string PALETTE = 'rainbow';
}
