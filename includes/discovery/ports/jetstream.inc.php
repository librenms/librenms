<?php

foreach ($port_stats as $key => $val) {

    //search for ': copper' string and replace to 'gigabitEthernet[nospace]1/0/x'
    if (strpos($port_stats[$key]['ifDescr'], ': copper') !== false) {
        $port_stats[$key]['ifDescr'] = str_replace([' ', ':', 'copper'], '', $port_stats[$key]['ifDescr']);
    }

    //search for ': copper' string and replace to 'gigabitEthernet[nospace]1/0/x'
    if (strpos($port_stats[$key]['ifName'], ': copper') !== false) {
        $port_stats[$key]['ifName'] = str_replace([' ', ':', 'copper'], '', $port_stats[$key]['ifName']);
    }

    //search for ': fiber' string and replace to 'FiberEthernet[nospace]1/0/x'. Capital 'F' !!!
    if (strpos($port_stats[$key]['ifDescr'], ': fiber') !== false) {
        $port_stats[$key]['ifDescr'] = str_replace([' ', ':', 'fiber'], '', $port_stats[$key]['ifDescr']);
        $port_stats[$key]['ifDescr'] = str_replace('gigabit', 'Fiber', $port_stats[$key]['ifDescr']);
    }

    //search for ': fiber' string and replace to 'FiberEthernet[nospace]1/0/x'. Capital 'F' !!!
    if (strpos($port_stats[$key]['ifName'], ': fiber') !== false) {
        $port_stats[$key]['ifName'] = str_replace([' ', ':', 'fiber'], '', $port_stats[$key]['ifName']);
        $port_stats[$key]['ifName'] = str_replace('gigabit', 'Fiber', $port_stats[$key]['ifName']);
    }
}
