<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpQuickPrint;
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

        $this->assertSame(SnmpQuickPrint::None, $options->quickPrint);
        $this->assertFalse($options->valueOnly);
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

        $this->assertSame(SnmpQuickPrint::Equals, $options->quickPrint);
        $this->assertFalse($options->valueOnly);
        $this->assertTrue($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliNullSetsDefaults(): void
    {
        $options = (new NetSnmpOptions)->parseCli(null);

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
        $this->assertSame(SnmpQuickPrint::Equals, $options->quickPrint);
        $this->assertTrue($options->extendedIndex);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliOUneb(): void
    {
        $options = (new NetSnmpOptions)->parseCli(['-OUneb']);

        $this->assertSame(SnmpOidOutput::Numeric, $options->oidFormat);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
        $this->assertSame(SnmpStringOutput::Guess, $options->stringFormat);
        $this->assertFalse($options->printUnits);
        $this->assertSame(SnmpQuickPrint::None, $options->quickPrint);
        $this->assertFalse($options->extendedIndex);
        $this->assertFalse($options->numericTimeticks);
        $this->assertFalse($options->allowUnderscores);
    }

    public function testParseCliWithPuPreservesDisallowUnderscores(): void
    {
        $options = (new NetSnmpOptions)->parseCli(['-OteQUsab', '-Pu']);

        $this->assertTrue($options->allowUnderscores);
        $this->assertTrue($options->numericTimeticks);
        $this->assertTrue($options->numericEnums);
        $this->assertSame(SnmpQuickPrint::Equals, $options->quickPrint);
        $this->assertFalse($options->printUnits);
        $this->assertTrue($options->numericIndexes);
        $this->assertSame(SnmpStringOutput::Ascii, $options->stringFormat);
    }

    public function testParseCliHexStringsPrecedence(): void
    {
        // When 'x' comes after 'a', hexStrings wins
        $optionsX = (new NetSnmpOptions)->parseCli(['-OteQUax']);
        $this->assertSame(SnmpStringOutput::Hex, $optionsX->stringFormat);

        // When 'a' comes after 'x', asciiStrings wins
        $optionsA = (new NetSnmpOptions)->parseCli(['-OteQUxa']);
        $this->assertSame(SnmpStringOutput::Ascii, $optionsA->stringFormat);

        // Standard -OQUsx
        $optionsOnlyX = (new NetSnmpOptions)->parseCli(['-OQUsx']);
        $this->assertSame(SnmpStringOutput::Hex, $optionsOnlyX->stringFormat);
    }

    public function testParseCliNumericAndSymbolicPrecedence(): void
    {
        // When 's' comes after 'n', symbolic without MIB wins
        $optionsS = (new NetSnmpOptions)->parseCli(['-Ons']);
        $this->assertSame(SnmpOidOutput::Suffix, $optionsS->oidFormat);

        // When 'n' comes after 's', numeric wins
        $optionsN = (new NetSnmpOptions)->parseCli(['-Osn']);
        $this->assertSame(SnmpOidOutput::Numeric, $optionsN->oidFormat);

        // When 'S' comes after 'n', symbolic with MIB wins
        $optionsUpperS = (new NetSnmpOptions)->parseCli(['-OnS']);
        $this->assertSame(SnmpOidOutput::Module, $optionsUpperS->oidFormat);
    }

    public function testParseCliDisplayHints(): void
    {
        $options = (new NetSnmpOptions)->parseCli(['-OUneb', '-Ih']);
        $this->assertFalse($options->applyDisplayHints);

        $optionsNormal = (new NetSnmpOptions)->parseCli(['-OUneb']);
        $this->assertTrue($optionsNormal->applyDisplayHints);
    }

    public function testParseCliUnorderedIndexes(): void
    {
        $optionsC = (new NetSnmpOptions)->parseCli(['-Cc']);
        $this->assertTrue($optionsC->tolerateUnorderedIndexes);
    }

    public function testParseCliMibVisibility(): void
    {
        $hideMib = (new NetSnmpOptions)->parseCli('-OQUs');
        $this->assertSame(SnmpOidOutput::Suffix, $hideMib->oidFormat);
        $this->assertFalse($hideMib->numericEnums);

        $showMib = (new NetSnmpOptions)->parseCli(['-OQUS']);
        $this->assertSame(SnmpOidOutput::Module, $showMib->oidFormat);
    }

    public function testParseCliMibsAppend(): void
    {
        $optionsSeparate = (new NetSnmpOptions)->parseCli(['-m', '+EXTRA-MIB']);
        $this->assertContains('EXTRA-MIB', $optionsSeparate->mibs);
        $this->assertContains('SNMPv2-MIB', $optionsSeparate->mibs);

        $optionsCombined = (new NetSnmpOptions)->parseCli(['-m+ANOTHER-MIB']);
        $this->assertContains('ANOTHER-MIB', $optionsCombined->mibs);
    }

    public function testParseCliMibsReplace(): void
    {
        $optionsSeparate = (new NetSnmpOptions)->parseCli(['-m', 'MY-MIB:YOUR-MIB']);
        $this->assertSame(['MY-MIB', 'YOUR-MIB'], $optionsSeparate->mibs);

        $optionsCombined = (new NetSnmpOptions)->parseCli(['-mFIRST-MIB:SECOND-MIB']);
        $this->assertSame(['FIRST-MIB', 'SECOND-MIB'], $optionsCombined->mibs);
    }

    public function testParseCliMibDirs(): void
    {
        $optionsSeparate = (new NetSnmpOptions)->parseCli(['-M', '/opt/mibs:/extra/mibs']);
        $this->assertSame(['/opt/mibs', '/extra/mibs'], $optionsSeparate->mibDirs);

        $optionsCombined = (new NetSnmpOptions)->parseCli(['-M/single/dir']);
        $this->assertSame(['/single/dir'], $optionsCombined->mibDirs);
    }

    public function testParseCliAcceptsSingleString(): void
    {
        $options = (new NetSnmpOptions)->parseCli('-OUneb');
        $this->assertSame(SnmpOidOutput::Numeric, $options->oidFormat);
        $this->assertTrue($options->numericIndexes);
        $this->assertTrue($options->numericEnums);
    }

    public function testParseCliSupportsQandVOptions(): void
    {
        $parser = new NetSnmpOptions();

        $optionsUpperQ = $parser->parseCli(['-OQ']);
        $this->assertSame(SnmpQuickPrint::Equals, $optionsUpperQ->quickPrint);
        $this->assertFalse($optionsUpperQ->valueOnly);
        $this->assertContains('-OQ', $parser->buildOutputFlags($optionsUpperQ));

        $optionsLowerQ = $parser->parseCli(['-Oq']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsLowerQ->quickPrint);
        $this->assertFalse($optionsLowerQ->valueOnly);
        $this->assertContains('-Oq', $parser->buildOutputFlags($optionsLowerQ));

        $optionsV = $parser->parseCli(['-Ov']);
        $this->assertTrue($optionsV->valueOnly);
        $this->assertSame(SnmpOidOutput::Module, $optionsV->oidFormat);
        $this->assertContains('-Ov', $parser->buildOutputFlags($optionsV));

        $optionsQv = $parser->parseCli(['-Oqv']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsQv->quickPrint);
        $this->assertTrue($optionsQv->valueOnly);
        $this->assertSame(SnmpOidOutput::Module, $optionsQv->oidFormat);
        $this->assertContains('-Oqv', $parser->buildOutputFlags($optionsQv));

        $optionsUpperQv = $parser->parseCli(['-OQv']);
        $this->assertSame(SnmpQuickPrint::Equals, $optionsUpperQv->quickPrint);
        $this->assertTrue($optionsUpperQv->valueOnly);
        $this->assertSame(SnmpOidOutput::Module, $optionsUpperQv->oidFormat);
        $this->assertContains('-OQv', $parser->buildOutputFlags($optionsUpperQv));

        $optionsVqn = $parser->parseCli(['-Ovqn']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsVqn->quickPrint);
        $this->assertTrue($optionsVqn->valueOnly);
        $this->assertSame(SnmpOidOutput::Numeric, $optionsVqn->oidFormat);
        $this->assertContains('-Oqvn', $parser->buildOutputFlags($optionsVqn));

        $optionsQvn = $parser->parseCli(['-Oqvn']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsQvn->quickPrint);
        $this->assertTrue($optionsQvn->valueOnly);
        $this->assertSame(SnmpOidOutput::Numeric, $optionsQvn->oidFormat);
        $this->assertContains('-Oqvn', $parser->buildOutputFlags($optionsQvn));

        $optionsNvq = $parser->parseCli(['-Onvq']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsNvq->quickPrint);
        $this->assertTrue($optionsNvq->valueOnly);
        $this->assertSame(SnmpOidOutput::Numeric, $optionsNvq->oidFormat);
        $this->assertContains('-Oqvn', $parser->buildOutputFlags($optionsNvq));

        $optionsVqs = $parser->parseCli(['-Ovqs']);
        $this->assertSame(SnmpQuickPrint::NoEquals, $optionsVqs->quickPrint);
        $this->assertTrue($optionsVqs->valueOnly);
        $this->assertSame(SnmpOidOutput::Suffix, $optionsVqs->oidFormat);
        $this->assertContains('-Oqvs', $parser->buildOutputFlags($optionsVqs));
    }
}
