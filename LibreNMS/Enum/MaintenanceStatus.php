<?php

namespace LibreNMS\Enum;

enum MaintenanceStatus: int
{
    case NONE = 0;
    case SKIP_ALERTS = 1;
    case MUTE_ALERTS = 2;
    case RUN_ALERTS = 3;

    public static function fromBehavior(int|MaintenanceBehavior|null $behavior): MaintenanceStatus
    {
        if (is_int($behavior)) {
            $behavior = MaintenanceBehavior::from($behavior);
        }

        return match ($behavior) {
            MaintenanceBehavior::SKIP_ALERTS => MaintenanceStatus::SKIP_ALERTS,
            MaintenanceBehavior::MUTE_ALERTS => MaintenanceStatus::MUTE_ALERTS,
            MaintenanceBehavior::RUN_ALERTS => MaintenanceStatus::RUN_ALERTS,
            default => MaintenanceStatus::NONE,
        };
    }
}
