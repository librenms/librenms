<?php

namespace App\Graphs\Application;

class HttpAccessLogCombinedMethodsGraph extends HttpAccessLogCombinedGraph
{
    protected function stats(): array
    {
        // request methods are their own labels
        $methods = ['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT'];

        return array_combine($methods, $methods);
    }
}
