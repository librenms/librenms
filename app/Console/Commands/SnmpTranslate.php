<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\SnmpResponse;
use SnmpQuery;

class SnmpTranslate extends SnmpFetch
{
    protected $name = 'snmp:translate';
    protected array $oids;

    protected function getDevices(): Collection
    {
        if (empty($this->oids)) {
            $this->oids = [$this->deviceSpec];

            return new Collection([new Device]); // no device needed, supply dummy
        }

        $devices = parent::getDevices();

        if ($devices->isEmpty()) {
            $this->oids = [$this->deviceSpec, ...$this->oids];

            return new Collection([new Device]); // no device needed, supply dummy
        }

        return $devices;
    }

    protected function fetchData(): SnmpResponse
    {
        $res = new SnmpResponse('');
        // translate does not support multiple oids (should it?)
        foreach ($this->oids as $oid) {
            $translated = SnmpQuery::numeric($this->numeric)->translate($oid);

            // if we got the same back (ignoring . prefix) swap numeric
            if (empty($translated) || Str::start($oid, '.') == Str::start($translated, '.')) {
                $translated = SnmpQuery::numeric(! $this->numeric)->translate($oid);
            }

            $res->append(new SnmpResponse($translated . PHP_EOL));
        }

        return $res;
    }
}
