<?php

namespace LibreNMS\Applications;

class Nvidia extends ApplicationDefinition
{
    public const string TYPE = 'nvidia';
    protected const ?string DOCS = 'Nvidia GPU';

    protected const ?array OVERVIEW = [
        'sm',
        'mem',
        'enc',
        'dec',
        'rxpci',
        'txpci',
        'fb',
        'bar1',
        'mclk',
        'pclk',
        'pwr',
        'temp',
        'pviol',
        'tviol',
        'sbecc',
        'dbecc',
    ];

    protected const array GRAPHS = [
        'sm' => 'GPU Utilization',
        'mem' => 'Memory Utilization',
        'enc' => 'Encoder Utilization',
        'dec' => 'Decoder Utilization',
        'fb' => 'Frame Buffer Memory Usage',
        'bar1' => 'Bar1 Memory Usage',
        'rxpci' => 'PCIe RX',
        'txpci' => 'PCIe TX',
        'pwr' => 'Power Usage',
        'temp' => 'Temperature',
        'mclk' => 'Memory Clock',
        'pclk' => 'GPU Clock',
        'pviol' => 'Power Violation Percentage',
        'tviol' => 'Thermal Violation Boolean',
        'sbecc' => 'Single Bit ECC Errors',
        'dbecc' => 'Double Bit ECC Errors',
    ];
}
