<?php

namespace LibreNMS\Modules\DeviceStack;

use App\Observers\ModuleModelObserver;
use App\Models\Port;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Modules\DeviceStack;

/**
 * Reports discovery/poll progress for the synthetic stack ports only.
 *
 * Stack links materialize as rows of the shared core Port model, so observing
 * Port directly would also report every core port of every device. This
 * observer scopes the ModuleModelObserver output to the reserved synthetic
 * ifIndex range and registers itself only once per process.
 */
class StackPortObserver extends ModuleModelObserver
{
    private static bool $observed = false;

    public static function register(): void
    {
        if (self::$observed) {
            return;
        }

        Port::observe(new self());
        self::$observed = true;
    }

    /**
     * @param  Model  $model
     */
    public function saving($model): void
    {
        if ($model instanceof Port && $this->isStackPort($model)) {
            parent::saving($model);
        }
    }

    /**
     * @param  Model  $model
     */
    public function updated($model): void
    {
        if ($model instanceof Port && $this->isStackPort($model)) {
            parent::updated($model);
        }
    }

    /**
     * @param  Model  $model
     */
    public function restored($model): void
    {
        if ($model instanceof Port && $this->isStackPort($model)) {
            parent::restored($model);
        }
    }

    /**
     * @param  Model  $model
     */
    public function created($model): void
    {
        if ($model instanceof Port && $this->isStackPort($model)) {
            parent::created($model);
        }
    }

    /**
     * @param  Model  $model
     */
    public function deleted($model): void
    {
        if ($model instanceof Port && $this->isStackPort($model)) {
            parent::deleted($model);
        }
    }

    private function isStackPort(Port $port): bool
    {
        return (int) $port->ifIndex >= DeviceStack::IFINDEX_BASE
            && (int) $port->ifIndex <= DeviceStack::IFINDEX_MAX;
    }
}
