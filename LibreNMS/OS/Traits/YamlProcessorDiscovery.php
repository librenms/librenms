<?php

namespace LibreNMS\OS\Traits;

use App\Facades\LibrenmsConfig;
use App\Models\Processor;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Yaml\IndexField;
use LibreNMS\Discovery\Yaml\OidField;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
use LibreNMS\Discovery\YamlDiscoveryDefinition;

trait YamlProcessorDiscovery
{
    /**
     * @return Collection<Processor>
     */
    public function discoverYamlProcessors(): Collection
    {
        $discovery = YamlDiscoveryDefinition::make(Processor::class)
            ->addField(new YamlDiscoveryField('precision', 'processor_precision', 1))
            ->addField(new OidField('value', 'processor_usage'))
            ->addField(new YamlDiscoveryField('descr', 'processor_descr', 'Processor'))
            ->addField(new YamlDiscoveryField('type', 'processor_type', $this->getName()))
            ->addField(new YamlDiscoveryField('entPhysicalIndex', default: 0))
            ->addField(new YamlDiscoveryField('warn_percent', default: LibrenmsConfig::get('processor_perc_warn', 75)))
            ->addField(new IndexField('index', 'processor_index'));

        return $discovery->discover($this->getDiscovery('processors'), [
            'entPhysicalIndex' => 0,
            'hrDeviceIndex' => 0,
            'processor_descr' => 'Processor',
            'processor_precision' => 1,
        ]);
    }
}
