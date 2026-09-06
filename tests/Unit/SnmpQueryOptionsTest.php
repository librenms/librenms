<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Tests\TestCase;

class SnmpQueryOptionsTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $options = new SnmpQueryOptions();

        $this->assertSame('', $options->context);
        $this->assertContains('SNMPv2-MIB', $options->mibs);
        $this->assertSame([], $options->mibDirs);
        $this->assertTrue($options->allowBulk);
        $this->assertFalse($options->tolerateUnorderedIndexes);

        $this->assertFalse($options->numericOids);
        $this->assertFalse($options->numericIndexes);
        $this->assertTrue($options->outputMibNames);

        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->numericTimeticks);
        $this->assertFalse($options->asciiStrings);
        $this->assertFalse($options->hexStrings);
        $this->assertFalse($options->printUnits);
        $this->assertTrue($options->applyDisplayHints);

        $this->assertTrue($options->quickPrint);
        $this->assertTrue($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliNullResetsDefaults(): void
    {
        $options = new SnmpQueryOptions(
            context: 'custom-ctx',
            allowBulk: false,
            tolerateUnorderedIndexes: true,
            numericOids: true,
            numericIndexes: true,
            outputMibNames: false,
            numericEnums: false,
            numericTimeticks: false,
            asciiStrings: true,
            hexStrings: true,
            printUnits: true,
            applyDisplayHints: false,
            quickPrint: false,
            extendedIndex: false,
            allowUnderscores: true,
        );

        $options->parseCli(null);

        $this->assertSame('', $options->context);
        $this->assertTrue($options->allowBulk);
        $this->assertFalse($options->tolerateUnorderedIndexes);
        $this->assertFalse($options->numericOids);
        $this->assertFalse($options->numericIndexes);
        $this->assertTrue($options->outputMibNames);
        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->numericTimeticks);
        $this->assertFalse($options->asciiStrings);
        $this->assertFalse($options->hexStrings);
        $this->assertFalse($options->printUnits);
        $this->assertTrue($options->applyDisplayHints);
        $this->assertTrue($options->quickPrint);
        $this->assertTrue($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliOUneb(): void
    {
        $options = (new SnmpQueryOptions)->parseCli(['-OUneb']);

        $this->assertTrue($options->numericOids);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
        $this->assertFalse($options->asciiStrings);
        $this->assertFalse($options->hexStrings);
        $this->assertFalse($options->printUnits);
        $this->assertFalse($options->quickPrint);
        $this->assertFalse($options->extendedIndex);
        $this->assertFalse($options->numericTimeticks);
        $this->assertTrue($options->allowUnderscores);
        $this->assertTrue($options->outputMibNames);
    }

    public function testParseCliWithPuPreservesDisallowUnderscores(): void
    {
        $options = (new SnmpQueryOptions)->parseCli(['-OteQUsab', '-Pu']);

        $this->assertFalse($options->allowUnderscores);
        $this->assertTrue($options->numericTimeticks);
        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->quickPrint);
        $this->assertFalse($options->printUnits);
        $this->assertFalse($options->outputMibNames);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->asciiStrings);
        $this->assertFalse($options->hexStrings);
    }

    public function testParseCliHexStringsPrecedence(): void
    {
        // When 'x' comes after 'a', hexStrings wins
        $optionsX = (new SnmpQueryOptions)->parseCli(['-OteQUax']);
        $this->assertTrue($optionsX->hexStrings);
        $this->assertFalse($optionsX->asciiStrings);

        // When 'a' comes after 'x', asciiStrings wins
        $optionsA = (new SnmpQueryOptions)->parseCli(['-OteQUxa']);
        $this->assertTrue($optionsA->asciiStrings);
        $this->assertFalse($optionsA->hexStrings);

        // Standard -OQUsx
        $optionsOnlyX = (new SnmpQueryOptions)->parseCli(['-OQUsx']);
        $this->assertTrue($optionsOnlyX->hexStrings);
        $this->assertFalse($optionsOnlyX->asciiStrings);
    }

    public function testParseCliDisplayHints(): void
    {
        $options = (new SnmpQueryOptions)->parseCli(['-OUneb', '-Ih']);
        $this->assertFalse($options->applyDisplayHints);

        $optionsNormal = (new SnmpQueryOptions)->parseCli(['-OUneb']);
        $this->assertTrue($optionsNormal->applyDisplayHints);
    }

    public function testParseCliUnorderedIndexes(): void
    {
        $optionsC = (new SnmpQueryOptions)->parseCli(['-Cc']);
        $this->assertTrue($optionsC->tolerateUnorderedIndexes);

        $optionsI = (new SnmpQueryOptions)->parseCli(['-Ci']);
        $this->assertTrue($optionsI->tolerateUnorderedIndexes);
    }

    public function testParseCliMibVisibility(): void
    {
        $hideMib = (new SnmpQueryOptions)->parseCli('-OQUs');
        $this->assertFalse($hideMib->outputMibNames);
        $this->assertFalse($hideMib->numericEnums);

        $showMib = (new SnmpQueryOptions)->parseCli(['-OQUS']);
        $this->assertTrue($showMib->outputMibNames);
    }

    public function testParseCliMibsAppend(): void
    {
        $optionsSeparate = (new SnmpQueryOptions)->parseCli(['-m', '+EXTRA-MIB']);
        $this->assertContains('EXTRA-MIB', $optionsSeparate->mibs);
        $this->assertContains('SNMPv2-MIB', $optionsSeparate->mibs);

        $optionsCombined = (new SnmpQueryOptions)->parseCli(['-m+ANOTHER-MIB']);
        $this->assertContains('ANOTHER-MIB', $optionsCombined->mibs);
    }

    public function testParseCliMibsReplace(): void
    {
        $optionsSeparate = (new SnmpQueryOptions)->parseCli(['-m', 'MY-MIB:YOUR-MIB']);
        $this->assertSame(['MY-MIB', 'YOUR-MIB'], $optionsSeparate->mibs);

        $optionsCombined = (new SnmpQueryOptions)->parseCli(['-mFIRST-MIB:SECOND-MIB']);
        $this->assertSame(['FIRST-MIB', 'SECOND-MIB'], $optionsCombined->mibs);
    }

    public function testParseCliMibDirs(): void
    {
        $optionsSeparate = (new SnmpQueryOptions)->parseCli(['-M', '/opt/mibs:/extra/mibs']);
        $this->assertSame(['/opt/mibs', '/extra/mibs'], $optionsSeparate->mibDirs);

        $optionsCombined = (new SnmpQueryOptions)->parseCli(['-M/single/dir']);
        $this->assertSame(['/single/dir'], $optionsCombined->mibDirs);
    }

    public function testParseCliAcceptsSingleString(): void
    {
        $options = (new SnmpQueryOptions)->parseCli('-OUneb');
        $this->assertTrue($options->numericOids);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
    }
}
