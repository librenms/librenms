<?php

//
// do not display hourly average
//
if (isset($vars['gstats_no_hourly'])) {
    if ($vars['gstats_no_hourly'] == 0) {
        $no_hourly = false;
    } elseif ($vars['gstats_no_hourly'] == 1) {
        $no_hourly = true;
    } else {
        $no_hourly = false;
    }
} else {
    $no_hourly = false;
}

//
// do not display hourly min
//
if (isset($vars['gstats_no_hourly_min'])) {
    if ($vars['gstats_no_hourly_min'] == 0) {
        $no_hourly_min = false;
    } elseif ($vars['gstats_no_hourly_min'] == 1) {
        $no_hourly_min = true;
    } else {
        $no_hourly_min = true;
    }
} else {
    $no_hourly_min = true;
}

//
// do not display hourly max
//
if (isset($vars['gstats_no_hourly_max'])) {
    if ($vars['gstats_no_hourly_max'] == 0) {
        $no_hourly_max = false;
    } elseif ($vars['gstats_no_hourly_max'] == 1) {
        $no_hourly_max = true;
    } else {
        $no_hourly_max = true;
    }
} else {
    $no_hourly_max = true;
}

//
// do not display daily average
//
if (isset($vars['gstats_no_daily'])) {
    if ($vars['gstats_no_daily'] == 0) {
        $no_daily = false;
    } elseif ($vars['gstats_no_daily'] == 1) {
        $no_daily = true;
    } else {
        $no_daily = false;
    }
} else {
    $no_daily = false;
}

//
// do not display daily min
//
if (isset($vars['gstats_no_daily_min'])) {
    if ($vars['gstats_no_daily_min'] == 0) {
        $no_daily_min = false;
    } elseif ($vars['gstats_no_daily_min'] == 1) {
        $no_daily_min = true;
    } else {
        $no_daily_min = true;
    }
} else {
    $no_daily_min = true;
}

//
// do not display daily max
//
if (isset($vars['gstats_no_daily_max'])) {
    if ($vars['gstats_no_daily_max'] == 0) {
        $no_daily_max = false;
    } elseif ($vars['gstats_no_daily_max'] == 1) {
        $no_daily_max = true;
    } else {
        $no_daily_max = true;
    }
} else {
    $no_daily_max = true;
}

//
// do not display weekly average
//
if (isset($vars['gstats_no_weekly'])) {
    if ($vars['gstats_no_weekly'] == 0) {
        $no_weekly = false;
    } elseif ($vars['gstats_no_weekly'] == 1) {
        $no_weekly = true;
    } else {
        $no_weekly = false;
    }
} else {
    $no_weekly = false;
}

//
// do not display weekly min
//
if (isset($vars['gstats_no_weekly_min'])) {
    if ($vars['gstats_no_weekly_min'] == 0) {
        $no_weekly_min = false;
    } elseif ($vars['gstats_no_weekly_min'] == 1) {
        $no_weekly_min = true;
    } else {
        $no_weekly_min = true;
    }
} else {
    $no_weekly_min = true;
}

//
// do not display weekly max
//
if (isset($vars['gstats_no_weekly_max'])) {
    if ($vars['gstats_no_weekly_max'] == 0) {
        $no_weekly_max = false;
    } elseif ($vars['gstats_no_weekly_max'] == 1) {
        $no_weekly_max = true;
    } else {
        $no_weekly_max = true;
    }
} else {
    $no_weekly_max = true;
}

//
// do not display percentile
//
if (isset($vars['gstats_no_percentile'])) {
    if ($vars['gstats_no_percentile'] == 0) {
        $no_percentile = false;
    } elseif ($vars['gstats_no_percentile'] == 1) {
        $no_percentile = true;
    } else {
        $no_percentile = false;
    }
} else {
    $no_percentile = false;
}

//
// display percentile x0
//
if (isset($vars['gstats_no_percentile_x0'])) {
    if ($vars['gstats_no_percentile_x0'] == 0) {
        $no_percentile_x0 = false;
    } elseif ($vars['gstats_no_percentile_x0'] == 1) {
        $no_percentile_x0 = true;
    } else {
        $no_percentile_x0 = true;
    }
} else {
    $no_percentile_x0 = true;
}

//
// percentile x0 value, default 90
//
if (isset($vars['gstats_percentile_x0_val'])) {
    if (! is_numeric($vars['gstats_percentile_x0_val'])) {
        $vars['gstats_percentile_x0_val'] = 90;
    } elseif ($vars['gstats_percentile_x0_val'] <= 0) {
        $vars['gstats_percentile_x0_val'] = 90;
    } elseif ($vars['gstats_percentile_x0_val'] >= 100) {
        $vars['gstats_percentile_x0_val'] = 90;
    }
    $percentile_x0 = $vars['gstats_percentile_x0_val'];
} else {
    $percentile_x0 = 90;
}

//
// display percentile x1
//
if (isset($vars['gstats_no_percentile_x1'])) {
    if ($vars['gstats_no_percentile_x1'] == 0) {
        $no_percentile_x1 = false;
    } elseif ($vars['gstats_no_percentile_x1'] == 1) {
        $no_percentile_x1 = true;
    } else {
        $no_percentile_x1 = true;
    }
} else {
    $no_percentile_x1 = true;
}

//
// percentile x1 value, default 95
//
if (isset($vars['gstats_percentile_x1_val'])) {
    if (! is_numeric($vars['gstats_percentile_x1_val'])) {
        $vars['gstats_percentile_x1_val'] = 95;
    } elseif ($vars['gstats_percentile_x1_val'] <= 0) {
        $vars['gstats_percentile_x1_val'] = 95;
    } elseif ($vars['gstats_percentile_x1_val'] >= 100) {
        $vars['gstats_percentile_x1_val'] = 95;
    }
    $percentile_x1 = $vars['gstats_percentile_x1_val'];
} else {
    $percentile_x1 = 95;
}

require 'includes/html/graphs/generic_stats.inc.php';
