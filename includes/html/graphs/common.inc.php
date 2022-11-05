<?php

// populate legacy global variables
$graph_params->scale_max = isset($scale_max) ? (int) $scale_max : $graph_params->scale_max;
$graph_params->scale_min = isset($scale_min) ? (int) $scale_min : $graph_params->scale_min;
$graph_params->scale_rigid = $scale_rigid ?? $graph_params->scale_rigid;
$graph_params->title = $graph_title ?? $graph_params->title;
