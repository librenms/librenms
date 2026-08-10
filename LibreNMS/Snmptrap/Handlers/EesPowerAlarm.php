<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class EesPowerAlarm implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $trapOid = $trap->getTrapOid();

        // Emerson sends alarmTrap plus alarmActiveTrap/alarmCeaseTrap.
        // alarmTrap has the full data, so suppress the secondary trap.
        if (
            str_contains($trapOid, 'alarmActiveTrap') ||
            str_contains($trapOid, 'alarmCeaseTrap') ||
            str_contains($trapOid, '6302.2.1.5.2') ||
            str_contains($trapOid, '6302.2.1.5.3')
        ) {
            return;
        }
        $alarmIndex = $this->getTrapValue($trap, 'alarmIndex');
        $alarmTime = $this->getTrapValue($trap, 'alarmTime') ?: 'unknown-time';
        $alarmStatusChange = $this->getTrapValue($trap, 'alarmStatusChange');
        $alarmSeverity = $this->getTrapValue($trap, 'alarmSeverity');
        $alarmDescription = $this->getTrapValue($trap, 'alarmDescription') ?: 'No alarm description';
        $alarmType = $this->getTrapValue($trap, 'alarmType') ?: 'unknown';

        $statusText = $this->statusText($trapOid, $alarmStatusChange);
        $severityText = $this->severityText($alarmSeverity);
        $eventSeverity = $this->eventSeverity($statusText, $alarmSeverity);

        $message = sprintf(
            'EES Power Alarm %s: %s | severity=%s | type=%s | index=%s | time=%s',
            $statusText,
            $alarmDescription,
            $severityText,
            $alarmType,
            $alarmIndex ?: 'none',
            $alarmTime
        );

        $trap->log($message, $eventSeverity);
    }

    private function getTrapValue(Trap $trap, string $oidName): ?string
    {
        $oid = $trap->findOid($oidName);

        if (! $oid) {
            return null;
        }

        return trim($trap->getOidData($oid), "\" \t\n\r\0\x0B");
    }

    private function statusText(string $trapOid, ?string $statusChange): string
    {
        if (str_contains($trapOid, 'alarmActiveTrap') || str_contains($trapOid, '6302.2.1.5.2')) {
            return 'activated';
        }

        if (str_contains($trapOid, 'alarmCeaseTrap') || str_contains($trapOid, '6302.2.1.5.3')) {
            return 'cleared';
        }

        $number = $this->numberFromValue($statusChange);

        return match ($number) {
            1 => 'activated',
            2 => 'cleared',
            default => $statusChange ?: 'unknown',
        };
    }

    private function severityText(?string $severity): string
    {
        $number = $this->numberFromValue($severity);

        return match ($number) {
            1 => 'unknown',
            2 => 'normal',
            3 => 'warning',
            4 => 'minor',
            5 => 'major',
            6 => 'critical',
            7 => 'unmanaged',
            8 => 'restricted',
            9 => 'testing',
            10 => 'disabled',
            default => $severity ?: 'unknown',
        };
    }

    private function eventSeverity(string $statusText, ?string $severity): Severity
    {
        if ($statusText === 'cleared') {
            return Severity::Ok;
        }

        $number = $this->numberFromValue($severity);

        return match ($number) {
            6 => Severity::Error,
            5 => Severity::Error,
            4 => Severity::Warning,
            3 => Severity::Warning,
            2 => Severity::Ok,
            default => Severity::Info,
        };
    }

    private function numberFromValue(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('/\((\d+)\)/', $value, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^\s*(\d+)\s*$/', $value, $matches)) {
            return (int) $matches[1];
        }

        $lower = strtolower($value);

        return match (true) {
            str_contains($lower, 'unknown') => 1,
            str_contains($lower, 'normal') => 2,
            str_contains($lower, 'warning') => 3,
            str_contains($lower, 'minor') => 4,
            str_contains($lower, 'major') => 5,
            str_contains($lower, 'critical') => 6,
            str_contains($lower, 'unmanaged') => 7,
            str_contains($lower, 'restricted') => 8,
            str_contains($lower, 'testing') => 9,
            str_contains($lower, 'disabled') => 10,

            // Important: check deactivated before activated.
            str_contains($lower, 'deactivated') => 2,
            str_contains($lower, 'cleared') => 2,
            str_contains($lower, 'activated') => 1,

            default => null,
        };
    }
}
