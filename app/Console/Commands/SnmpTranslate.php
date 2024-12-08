<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Support\Collection;
use LibreNMS\Config;
use LibreNMS\Data\Source\SnmpResponse;
use SnmpQuery;

class SnmpTranslate extends SnmpFetch
{
    protected $name = 'snmp:translate';
    protected array $oids;

    public function __construct()
    {
        parent::__construct();

        // remove numeric option as this shows both
        $options = $this->getDefinition()->getOptions();
        unset($options['numeric']);
        $this->getDefinition()->setOptions($options);
    }

    protected function getDevices(): Collection
    {
        if (empty($this->oids)) {
            $this->oids = [$this->deviceSpec];

            return new Collection([new Device]); // no device needed, supply dummy
        }

        $devices = parent::getDevices();
        if ($devices->isNotEmpty()) {
            return $devices;
        }

        // check if the "device" is an valid os, if it is, use that for the dummy device
        if (Config::has('os.' . $this->deviceSpec)) {
            return new Collection([new Device(['os' => $this->deviceSpec])]);
        }

        $this->oids = [$this->deviceSpec, ...$this->oids];

        // no device needed, supply dummy
        return new Collection([new Device]);
    }

    protected function fetchData($device): SnmpResponse
    {
        $res = new SnmpResponse('');
        // translate does not support multiple oids (should it?)
        foreach ($this->oids as $oid) {
            $textual = SnmpQuery::numeric(false)->device($device)->mibs(['ALL'])->translate($oid);
            $numeric = SnmpQuery::numeric(true)->device($device)->mibs(['ALL'])->translate($oid);

            $response = new SnmpResponse("$textual = $numeric\n");
            $res = $res->append($response);
        }

        return $res;
    }
}
