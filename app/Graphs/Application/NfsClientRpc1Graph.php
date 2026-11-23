<?php

namespace App\Graphs\Application;

class NfsClientRpc1Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 1;
    protected const string PALETTE = 'rainbow';
}
