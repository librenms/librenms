<?php

use App\Models\Mempool;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;

$mempools = DeviceCache::getPrimary()->mempools;

// if multiple, show composite graph
if ($mempools->count() > 1) {
    echo "<div class='panel panel-default'><div class='panel-heading'>";
    echo "<h3 class='panel-title'>Overview";
    /** @var Mempool $system */
    $system = $mempools->where('mempool_class', 'system')->first();
    if ($system) {
        $bytes = '';
        if ($system->mempool_total !== 100) {
            $total = Number::formatBi($system->mempool_total);
            $used = Number::formatBi($system->mempool_used);
            $bytes = "$used/$total - ";
        }
        echo "<div class='pull-right'>$bytes$system->mempool_perc% used</div>";
    }
    echo "</h3></div><div class='panel-body'>";
    Html::graphRow(['device' => DeviceCache::getPrimary()->device_id, 'type' => 'device_mempool'], true);
    echo '</div></div>';
}

foreach ($mempools as $mempool) {
    echo "<div class='panel panel-default'><div class='panel-heading'>";

    $bytes = '';
    if ($mempool->mempool_total !== 100) {
        $total = Number::formatBi($mempool->mempool_total);
        $used = Number::formatBi($mempool->mempool_used);
        $bytes = "$used/$total - ";
    }
    echo "<h3 class='panel-title'>{$mempool->mempool_descr} <div class='pull-right'>$bytes$mempool->mempool_perc% used</div></h3>";
    echo "</div><div class='panel-body'>";
    Html::graphRow(['id' => $mempool->mempool_id, 'type' => 'mempool_usage'], true);
    echo '</div></div>';
}
