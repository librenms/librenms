<?php

namespace App\Graphs\Application;

class NfsClientRpc5Graph extends NfsGraph
{
    protected const string STATS = 'client_rpc';
    protected const ?int PART = 5;
    protected const string PALETTE = 'rainbow';
}
