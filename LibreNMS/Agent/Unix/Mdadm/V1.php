<?php

namespace LibreNMS\Agent\Unix\Mdadm;

use LibreNMS\Agent\Application;
use LibreNMS\RRD\RrdDefinition;

class V1 extends Application
{
    public function poll(): void
    {
        $payload = $this->fetchPayload('mdadm', 1);
        if ($payload !== null) {
            $this->pollLegacy($payload);
        }
    }

    public function pollLegacy(array $payload): void
    {
        $name = 'mdadm';
        $rrd_def = RrdDefinition::make()
            ->addDataset('level', 'GAUGE', 0)
            ->addDataset('size', 'GAUGE', 0)
            ->addDataset('disc_count', 'GAUGE', 0)
            ->addDataset('hotspare_count', 'GAUGE', 0)
            ->addDataset('degraded', 'GAUGE', 0)
            ->addDataset('sync_speed', 'GAUGE', 0)
            ->addDataset('sync_completed', 'GAUGE', 0);

        $metrics = [];
        foreach ($payload['data'] ?? [] as $data) {
            $array_name = $data['name'];
            $fields = [
                'level'          => str_replace('raid', '', (string) $data['level']),
                'size'           => $data['size'],
                'disc_count'     => $data['disc_count'],
                'hotspare_count' => $data['hotspare_count'],
                'degraded'       => $data['degraded'],
                'sync_speed'     => $data['sync_speed'],
                'sync_completed' => $data['sync_completed'],
            ];
            $metrics[$array_name] = $fields;
            $tags = [
                'name'     => $array_name,
                'app_id'   => $this->app->app_id,
                'rrd_def'  => $rrd_def,
                'rrd_name' => ['app', $name, $this->app->app_id, $array_name],
            ];
            app('Datastore')->put($this->os->getDeviceArray(), 'app', $tags, $fields);
        }
        \update_application($this->app, 'OK', $metrics);
    }
}
