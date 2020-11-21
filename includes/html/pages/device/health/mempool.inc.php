<?php

use LibreNMS\Util\Html;
use LibreNMS\Util\Number;

$mempools = DeviceCache::getPrimary()->mempools;

if ($mempools->isNotEmpty()) {
    echo "<div class='panel panel-default'><div class='panel-heading'>";
    echo "<h3 class='panel-title'>Overview";
    $system = $mempools->where('mempool_class', 'system')->first();
    if ($system) {
        echo "<div class='pull-right'>$system->mempool_perc% used</div>";
    }
    echo "</h3></div><div class='panel-body'>";
    Html::graphRow(['device' => DeviceCache::getPrimary()->device_id, 'type' => 'device_mempool'], true);
    echo '</div></div>';
}

foreach ($mempools as $mempool) {
    echo "<div class='panel panel-default'><div class='panel-heading'>";

    if ($mempool->mempool_total === 100) {
        echo "                <h3 class='panel-title'>{$mempool->mempool_descr} <div class='pull-right'>$mempool->mempool_perc% used</div></h3>";
    } else {
        $total = Number::formatBi($mempool->mempool_total);
        $used = Number::formatBi($mempool->mempool_used);
        echo "<h3 class='panel-title'>{$mempool->mempool_descr} <div class='pull-right'>$used/$total - $mempool->mempool_perc% used</div></h3>";
    }
    echo "</div><div class='panel-body'>";
    Html::graphRow(['id' => $mempool->mempool_id, 'type' => 'mempool_usage'], true);
    echo '</div></div>';
}
