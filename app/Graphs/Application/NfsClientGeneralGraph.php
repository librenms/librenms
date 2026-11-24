<?php

namespace App\Graphs\Application;

class NfsClientGeneralGraph extends NfsGraph
{
    protected const string STATS = 'client_general';
    protected const bool PER_SECOND = false;
}
