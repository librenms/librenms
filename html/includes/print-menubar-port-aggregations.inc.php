<?php
use LibreNMS\Authentication\Auth;
?>

    <!-- PORTS -->
        <li class="dropdown-submenu">
          <a href="ports/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-link fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> <span class="hidden-sm">Port Aggregates</span></a>
          <ul class="dropdown-menu scrollable-menu">

<?php

if (Auth::user()->hasGlobalRead()) {
    # Builds some special case configuration into aggregates
    ## BEGIN
    $custom_agg_name_to_index = [];
    $custom_agg_to_build = [];
    # Build mapping of aggregate names to their menu indexes
    # Also build a list of aggregates that should dynamically
    #  pull interfaces from other aggregates
    foreach ($config['custom_aggregation'] as $index => $entry) {
        if (gettype($entry) == 'array') {
            foreach ($entry as $name => $value) {
                $custom_agg_name_to_index[$name] = $index;
                if (gettype($value) == 'array') {
                    array_push($custom_agg_to_build, $index);
                }
            }
        }
    }

    # Rebuilt the composite aggregates
    foreach ($custom_agg_to_build as $cab) {
        foreach ($config['custom_aggregation'][$cab] as $aggregate_name => $source_indexes) {
            $new_agg = [$aggregate_name => ''];
            foreach ($source_indexes as $key => $index_name) {
                $new_agg[$aggregate_name] .= ',' . $config['custom_aggregation'][$custom_agg_name_to_index[$index_name]][$index_name];
            }
            $new_agg[$aggregate_name] = ltrim($new_agg[$aggregate_name], ',');
            $config['custom_aggregation'][$cab] = $new_agg;
        }
    }
    ## END

    if ($config['custom_aggregation']) {
        foreach ($config['custom_aggregation'] as $index => $entry) {
            if ($entry == '') {
                echo('            <li role="presentation" class="divider"></li>');
            } else {
                foreach ($entry as $title => $interfaces) {
                    # In order to work around librenms default foreslash behavior, convert all / to &slsh;
                    $str = htmlentities(preg_replace('/\//', '&slsh;', $interfaces));
                    echo('            <li><a href="aggregates/label=' . $title . '/iface=' . $str . '"><i class="fa fa-users fa-fw fa-lg" aria-hidden="true"></i>' . $title . '</a></li>');
                }
            }
        }
    }
}

?>

          </ul>
        </li>
