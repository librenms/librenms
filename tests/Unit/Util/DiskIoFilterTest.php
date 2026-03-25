<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\DiskIoFilter;

final class DiskIoFilterTest extends TestCase
{
    public function testNormalizeSelectionDefaultsToPhysical(): void
    {
        $selection = DiskIoFilter::normalizeSelection(null, null);

        $this->assertSame('physical', $selection['view']);
        $this->assertSame('all', $selection['subtype']);
    }

    public function testNormalizeSelectionForcesAllSubtypeOnAllView(): void
    {
        $selection = DiskIoFilter::normalizeSelection('all', 'nvme');

        $this->assertSame('all', $selection['view']);
        $this->assertSame('all', $selection['subtype']);
    }

    public function testNormalizeSelectionFallsBackForInvalidSubtype(): void
    {
        $selection = DiskIoFilter::normalizeSelection('logical', 'nvme');

        $this->assertSame('logical', $selection['view']);
        $this->assertSame('all', $selection['subtype']);
    }

    public function testClassifyPhysicalDrives(): void
    {
        $this->assertSame(['view' => 'physical', 'subtype' => 'sd_family'], DiskIoFilter::classify('sda'));
        $this->assertSame(['view' => 'physical', 'subtype' => 'nvme'], DiskIoFilter::classify('nvme0n1'));
        $this->assertSame(['view' => 'physical', 'subtype' => 'mmcblk'], DiskIoFilter::classify('mmcblk0'));
    }

    public function testClassifyLogicalDrives(): void
    {
        $this->assertSame(['view' => 'logical', 'subtype' => 'partitions'], DiskIoFilter::classify('sda1'));
        $this->assertSame(['view' => 'logical', 'subtype' => 'partitions'], DiskIoFilter::classify('nvme0n1p1'));
        $this->assertSame(['view' => 'logical', 'subtype' => 'partitions'], DiskIoFilter::classify('md0p1'));
        $this->assertSame(['view' => 'logical', 'subtype' => 'dm'], DiskIoFilter::classify('dm-0'));
        $this->assertSame(['view' => 'logical', 'subtype' => 'md'], DiskIoFilter::classify('md0'));
        $this->assertSame(['view' => 'logical', 'subtype' => 'loop'], DiskIoFilter::classify('loop0'));
    }

    public function testMatchesRespectsViewAndSubtypeFilters(): void
    {
        $diskType = ['view' => 'physical', 'subtype' => 'nvme'];

        $this->assertTrue(DiskIoFilter::matches($diskType, 'all', 'all'));
        $this->assertTrue(DiskIoFilter::matches($diskType, 'physical', 'all'));
        $this->assertTrue(DiskIoFilter::matches($diskType, 'physical', 'nvme'));
        $this->assertFalse(DiskIoFilter::matches($diskType, 'logical', 'all'));
        $this->assertFalse(DiskIoFilter::matches($diskType, 'physical', 'sd_family'));
    }
}
