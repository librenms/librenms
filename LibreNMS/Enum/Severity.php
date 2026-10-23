<?php

namespace LibreNMS\Enum;

enum Severity: int
{
    case Unknown = 0;
    case Ok = 1;
    case Info = 2;
    case Notice = 3;
    case Warning = 4;
    case Error = 5;

    /**
     * Convert the severity string of an alert rule (ok, warning, critical) to a Severity.
     */
    public static function fromAlertRule(?string $severity): self
    {
        return match ($severity) {
            'ok' => self::Ok,
            'warning' => self::Warning,
            'critical' => self::Error,
            default => self::Unknown,
        };
    }

    /**
     * Convert the severity to the matching SensorState.
     *
     * @throws \Exception If the severity is not supported for state sensors
     */
    public function toSensorState(): SensorState
    {
        return match ($this) {
            self::Ok => SensorState::Ok,
            self::Warning => SensorState::Warning,
            self::Error => SensorState::Error,
            self::Unknown => SensorState::Unknown,
            default => throw new \Exception("Severity $this->name is not supported for state sensors"),
        };
    }
}
