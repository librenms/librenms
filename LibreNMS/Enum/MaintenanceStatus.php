<?php

namespace LibreNMS\Enum;

enum MaintenanceStatus: int
{
    case None = 0;
    case SkipAlerts = 1;
    case MuteAlerts = 2;
    case RunAlerts = 3;

    public static function fromBehavior(int|MaintenanceBehavior|null $behavior): MaintenanceStatus
    {
        if (is_int($behavior)) {
            $behavior = MaintenanceBehavior::from($behavior);
        }

        return match ($behavior) {
            MaintenanceBehavior::SkipAlerts => MaintenanceStatus::SkipAlerts,
            MaintenanceBehavior::MuteAlerts => MaintenanceStatus::MuteAlerts,
            MaintenanceBehavior::RunAlerts => MaintenanceStatus::RunAlerts,
            default => MaintenanceStatus::None,
        };
    }
}
