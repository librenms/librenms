<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Data\TimeSeries\FieldKind;
use App\Data\TimeSeries\MetricDefinition;
use App\Data\TimeSeries\MetricField;
use App\Data\TimeSeries\MetricIdentity;
use App\Models\Device;
use App\Models\Port;
use App\Models\Processor;
use LibreNMS\Data\Store\MetricDefinitions;
use LibreNMS\Data\Store\MetricNames;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('datastores')]
final class MetricDefinitionsTest extends TestCase
{
    public function testDefinitionsAreFoundByMeasurement(): void
    {
        $definitions = new MetricDefinitions;

        $this->assertEquals($definitions->of('processor'), $definitions->of(new MetricIdentity('processor', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1])));
        $this->assertNull($definitions->of('not-a-measurement'));
        $this->assertNull($definitions->field('not-a-measurement', 'usage'));
        $this->assertNull($definitions->field('processor', 'not-a-field'));
    }

    public function testAReaderKnowsWhichPortFieldsAreCounters(): void
    {
        $port = new Port;
        $port->port_id = 12;
        $port->setRelation('device', new Device(['hostname' => 'localhost']));

        $field = new MetricDefinitions()->field($port->metricIdentity('port'), 'in_octets');

        $this->assertNotNull($field);
        $this->assertTrue($field->isCounter());
        $this->assertSame(FieldKind::Derive, $field->kind);
        $this->assertSame(0, $field->min);
        $this->assertSame(12500000000, $field->max);
    }

    public function testAReaderKnowsProcessorUsageIsAGauge(): void
    {
        $processor = new Processor(['processor_type' => 'hr', 'processor_index' => '1']);
        $processor->setRelation('device', new Device(['hostname' => 'localhost']));

        $field = new MetricDefinitions()->field($processor->metricIdentity('processor'), 'usage');

        $this->assertNotNull($field);
        $this->assertFalse($field->isCounter());
        $this->assertSame(-273, $field->min);
        $this->assertSame(1000, $field->max);
    }

    public function testEveryKindIsAnRrdDataSource(): void
    {
        $definition = RrdDefinition::fromMetric(new MetricDefinition(
            MetricField::gauge('value', -1, 1),
            MetricField::derive('derived', 0),
            MetricField::wrapping('counted'),
        ));

        $this->assertSame(
            (string) RrdDefinition::make()->addDataset('value', 'GAUGE', -1, 1)->addDataset('derived', 'DERIVE', 0)->addDataset('counted', 'COUNTER'),
            (string) $definition,
        );
    }

    /**
     * The data sources of existing port rrd files
     */
    public function testThePortRrdIsUnchanged(): void
    {
        $handBuilt = RrdDefinition::make()
            ->addDataset('INOCTETS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTOCTETS', 'DERIVE', 0, 12500000000)
            ->addDataset('INERRORS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTERRORS', 'DERIVE', 0, 12500000000)
            ->addDataset('INUCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTUCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('INNUCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTNUCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('INDISCARDS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTDISCARDS', 'DERIVE', 0, 12500000000)
            ->addDataset('INUNKNOWNPROTOS', 'DERIVE', 0, 12500000000)
            ->addDataset('INBROADCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTBROADCASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('INMULTICASTPKTS', 'DERIVE', 0, 12500000000)
            ->addDataset('OUTMULTICASTPKTS', 'DERIVE', 0, 12500000000);

        $metric = new MetricIdentity('port', ['hostname' => 'localhost', 'id' => 12]);
        $definition = new MetricDefinitions()->of($metric);
        $this->assertNotNull($definition);
        $this->assertSame($handBuilt->getCreateArguments(), RrdDefinition::fromMetric($definition, new MetricNames()->legacyOf($metric))->getCreateArguments());
    }

    /**
     * The data sources of existing processor rrd files
     */
    public function testTheProcessorRrdIsUnchanged(): void
    {
        $handBuilt = RrdDefinition::make()->addDataset('usage', 'GAUGE', -273, 1000);

        $metric = new MetricIdentity('processor', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1]);
        $definition = new MetricDefinitions()->of($metric);
        $this->assertNotNull($definition);
        $this->assertSame($handBuilt->getCreateArguments(), RrdDefinition::fromMetric($definition, new MetricNames()->legacyOf($metric))->getCreateArguments());
        $this->assertSame($handBuilt->getCreateArguments(), RrdDefinition::fromMetric($definition)->getCreateArguments(), 'processor fields kept their names');
    }

    public function testCanonicalPortFilesNameTheirDataSourcesAfterTheFields(): void
    {
        $definition = new MetricDefinitions()->of('port');
        $this->assertNotNull($definition);

        $this->assertStringStartsWith('DS:in_octets:DERIVE:', RrdDefinition::fromMetric($definition)->getArguments()[0]);
    }

    public function testEveryPortFieldHasTheNameItWasStoredUnder(): void
    {
        $metric = new MetricIdentity('port', ['hostname' => 'localhost', 'id' => 12]);
        $legacy = new MetricNames()->legacyOf($metric);
        $definition = new MetricDefinitions()->of($metric);
        $this->assertNotNull($legacy);
        $this->assertNotNull($definition);

        $this->assertSame(array_keys($definition->fields), array_keys($legacy->fields));
    }
}
