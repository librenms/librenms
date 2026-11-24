<?php

namespace App\Graphs\Application;

class NfsServerCacheGraph extends NfsGraph
{
    protected const string STATS = 'server_cache';
    protected const bool PER_SECOND = false;
}
