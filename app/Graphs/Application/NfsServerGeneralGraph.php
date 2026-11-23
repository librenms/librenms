<?php

namespace App\Graphs\Application;

class NfsServerGeneralGraph extends NfsGraph
{
    protected const string STATS = 'server_general';
    protected const bool PER_SECOND = false;
}
