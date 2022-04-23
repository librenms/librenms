<?php

$row = 1;

$device_id = $vars['device_id'];

$OBJCOMP = new LibreNMS\Component();

// Add a filter if supplied
if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $options['filter']['label'] = ['LIKE', $searchPhrase];
}

// Add a Sort option
if (! isset($sort) || empty($sort)) {
    // Nothing supplied, default is id ASC.
    $options['sort'] = 'id asc';
} else {
    $options['sort'] = $sort;
}

// Define the Limit parameters
if (isset($current)) {
    $start = (($current * $rowCount) - ($rowCount));
}
if ($rowCount != -1) {
    $options['limit'] = [$start, $rowCount];
}

$COMPONENTS = $OBJCOMP->getComponents($device_id, $options);

$response[] = [
    'id' => '<button type="submit" id="save-form" class="btn btn-success btn-sm" title="Save current component disable/ignore settings">Save</button><button type="submit" id="form-reset" class="btn btn-danger btn-sm" title="Reset form to when the page was loaded">Reset</button>',
    'label' => '&nbsp;',
    'status' => '<button type="submit" id="warning-select" class="btn btn-default btn-sm" title="Disable alerting on all currently warning components">Warning</button>&nbsp;<button type="submit" id="critical-select" class="btn btn-default btn-sm" title="Disable alerting on all currently critical components">Critical</button>',
    'disable' => '<button type="submit" id="disable-toggle" class="btn btn-default btn-sm" title="Toggle polling for all components">Toggle</button><button type="button" id="disable-select" class="btn btn-default btn-sm" title="Disable polling on all components">Select All</button>',
    'ignore' => '<button type="submit" id="ignore-toggle" class="btn btn-default btn-sm" title="Toggle alert tag for all components">Toggle</button><button type="button" id="ignore-select" class="btn btn-default btn-sm" title="Disable alert tag on all components">Select All</button>',
];

foreach ($COMPONENTS[$device_id] as $ID => $AVP) {
    if ($AVP['status'] == 0) {
        $class = 'green';
        $status = 'Ok';
    } elseif ($AVP['status'] == 1) {
        $class = 'grey';
        $status = 'Warning';
    } else {
        // Critical
        $class = 'red';
        $status = 'Critical';
    }
    $response[] = [
        'id' => $ID,
        'type' => $AVP['type'],
        'label' => $AVP['label'],
        'status' => "<span name='status_" . $ID . "' class='" . $class . "'>" . $status . '</span>',
        'disable' => '<input type="checkbox" class="disable-check" name="dis_' . $ID . '"' . ($AVP['disabled'] ? 'checked' : '') . '>',
        'ignore' => '<input type="checkbox" class="ignore-check" name="ign_' . $ID . '"' . ($AVP['ignore'] ? 'checked' : '') . '>',
    ];
}//end foreach

$output = [
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => ! empty($COMPONENTS[$device_id]) ? count($COMPONENTS[$device_id]) : 0,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
