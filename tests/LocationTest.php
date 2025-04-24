<?php

$regexes = [
    '[x.xx,x.xx] (brackets with comma)' => '/\[\s*(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{0,1},\s{0,1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))\s*\]/',
    'x.xx x.xx (space)'    => '/^(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))$/',
    'x.xx,x.xx (comma)'    => '/^(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{0,1},\s{0,1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))$/',
];

$locations = [
    'london [12.45 ,12.45]',
    'london [12.45 , 12.45] text',
    'london [12.45,12.45]',
    '[55.123, 14.456] text',
    '[-23.456,100.654]',
    '12.45,12.45',
    '12.45, 12.45',
    '12.45 ,12.45',
    '12.45 , 12.45',
    'text 12.45,12.45',
    '12.45,12.45 text',
    '12.45 12.45',
    'text 14.55 14.55',
    '14.55 14.55 text',
    '[text]',

];

foreach ($locations as $location) {
    echo "Testing: \"$location\"\n";
    $matched = false;

    foreach ($regexes as $label => $regex) {
        if (preg_match($regex, $location, $matches)) {
            echo "  ✔  Valid via '$label' regex:\n";
            echo "    → lat: " . $matches['lat'] . ", lng: " . $matches['lng'] . "\n";
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        echo "  ✘ No valid location \n";
    }

    echo "\n";
}
