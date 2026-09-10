<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
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

        $this->assertSame(SnmpOidOutput::Module, $options->oidFormat);
        $this->assertFalse($options->numericIndexes);

        $this->assertSame(SnmpStringOutput::Guess, $options->stringFormat);
        $this->assertFalse($options->numericEnums);
        $this->assertFalse($options->numericTimeticks);
        $this->assertTrue($options->printUnits);
        $this->assertTrue($options->applyDisplayHints);

        $this->assertFalse($options->quickPrint);
        $this->assertFalse($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testQuickPrintDefaults(): void
    {
        $options = SnmpQueryOptions::quickPrint();

        $this->assertSame('', $options->context);
        $this->assertContains('SNMPv2-MIB', $options->mibs);
        $this->assertSame([], $options->mibDirs);
        $this->assertTrue($options->allowBulk);
        $this->assertFalse($options->tolerateUnorderedIndexes);

        $this->assertSame(SnmpOidOutput::Module, $options->oidFormat);
        $this->assertFalse($options->numericIndexes);

        $this->assertSame(SnmpStringOutput::Guess, $options->stringFormat);
        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->numericTimeticks);
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
            oidFormat: SnmpOidOutput::Ucd,
            numericIndexes: true,
            numericEnums: false,
            numericTimeticks: false,
            stringFormat: SnmpStringOutput::Ascii,
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
        $this->assertSame(SnmpOidOutput::Module, $options->oidFormat);
        $this->assertFalse($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->numericTimeticks);
        $this->assertSame(SnmpStringOutput::Guess, $options->stringFormat);
        $this->assertFalse($options->printUnits);
        $this->assertTrue($options->applyDisplayHints);
        $this->assertTrue($options->quickPrint);
        $this->assertTrue($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliOUneb(): void
    {
        $options = (new SnmpQueryOptions)->parseCli(['-OUneb']);

        $this->assertSame(SnmpOidOutput::Numeric, $options->oidFormat);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
        $this->assertSame(SnmpStringOutput::Guess, $options->stringFormat);
        $this->assertFalse($options->printUnits);
        $this->assertFalse($options->quickPrint);
        $this->assertFalse($options->extendedIndex);
        $this->assertFalse($options->numericTimeticks);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliWithPuPreservesDisallowUnderscores(): void
    {
        $options = (new SnmpQueryOptions)->parseCli(['-OteQUsab', '-Pu']);

        $this->assertTrue($options->allowUnderscores);
        $this->assertTrue($options->numericTimeticks);
        $this->assertTrue($options->numericEnums);
        $this->assertTrue($options->quickPrint);
        $this->assertFalse($options->printUnits);
        $this->assertTrue($options->numericIndexes);
        $this->assertSame(SnmpStringOutput::Ascii, $options->stringFormat);
    }

    public function testParseCliHexStringsPrecedence(): void
    {
        // When 'x' comes after 'a', hexStrings wins
        $optionsX = (new SnmpQueryOptions)->parseCli(['-OteQUax']);
        $this->assertSame(SnmpStringOutput::Hex, $optionsX->stringFormat);

        // When 'a' comes after 'x', asciiStrings wins
        $optionsA = (new SnmpQueryOptions)->parseCli(['-OteQUxa']);
        $this->assertSame(SnmpStringOutput::Ascii, $optionsA->stringFormat);

        // Standard -OQUsx
        $optionsOnlyX = (new SnmpQueryOptions)->parseCli(['-OQUsx']);
        $this->assertSame(SnmpStringOutput::Hex, $optionsOnlyX->stringFormat);
    }

    public function testParseCliNumericAndSymbolicPrecedence(): void
    {
        // When 's' comes after 'n', symbolic without MIB wins
        $optionsS = (new SnmpQueryOptions)->parseCli(['-Ons']);
        $this->assertSame(SnmpOidOutput::Suffix, $optionsS->oidFormat);

        // When 'n' comes after 's', numeric wins
        $optionsN = (new SnmpQueryOptions)->parseCli(['-Osn']);
        $this->assertSame(SnmpOidOutput::Numeric, $optionsN->oidFormat);

        // When 'S' comes after 'n', symbolic with MIB wins
        $optionsUpperS = (new SnmpQueryOptions)->parseCli(['-OnS']);
        $this->assertSame(SnmpOidOutput::Module, $optionsUpperS->oidFormat);
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
    }

    public function testParseCliMibVisibility(): void
    {
        $hideMib = (new SnmpQueryOptions)->parseCli('-OQUs');
        $this->assertSame(SnmpOidOutput::Suffix, $hideMib->oidFormat);
        $this->assertFalse($hideMib->numericEnums);

        $showMib = (new SnmpQueryOptions)->parseCli(['-OQUS']);
        $this->assertSame(SnmpOidOutput::Module, $showMib->oidFormat);
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
        $this->assertSame(SnmpOidOutput::Numeric, $options->oidFormat);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
    }
}
