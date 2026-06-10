<?php

foreach ($vars as $var => $value) {
    if ($value != '') {
        switch ($var) {
            case 'name':
                $where .= " AND `$var` = ?";
                $param[] = $value;
                break;
        }
    }
}

echo '<table cellspacing="0" cellpadding="5" width="100%">';

foreach (dbFetchRows("SELECT * FROM `packages` WHERE 1 $where GROUP BY `name`", $param) as $entry) {
    echo '<tr class="list">';
    echo '<td width=200><a href="' . \LibreNMS\Util\Url::generate($vars, ['name' => $entry['name']]) . '">' . $entry['name'] . '</a></td>';

    echo '<td>';
    foreach (dbFetchRows('SELECT * FROM `packages` WHERE `name` = ? ORDER BY version, build', [$entry['name']]) as $entry_v) {
        $entry['blah'][$entry_v['version']][$entry_v['build']][$entry_v['device_id']] = 1;
    }

    foreach ($entry['blah'] as $version => $bleu) {
        $content = '<div style="width: 800px;">';

        foreach ($bleu as $build => $bloo) {
            if ($build) {
                $dbuild = '-' . $build;
            } else {
                $dbuild = '';
            }

            $content .= '<div style="background-color: #eeeeee; margin: 5px;"><span style="font-weight: bold; ">' . $version . $dbuild . '</span>';
            foreach ($bloo as $device_id => $no) {
                $this_device = device_by_id_cache($device_id);
                $content .= '<span style="background-color: #f5f5f5; margin: 5px;">' . $this_device['hostname'] . '</span> ';
            }

            $content .= '</div>';
        }

        $content .= '</div>';
        if (empty($vars['name'])) {
            echo "<span style='margin:5px;'>" . \LibreNMS\Util\Url::overlibLink('', $version, $content) . '</span>';
        } else {
            echo "$version $content";
        }
    }//end foreach

    echo '<td>';
    echo '</tr>';
}//end foreach

echo '</table>';
