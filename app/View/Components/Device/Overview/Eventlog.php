<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Enum\Severity;

class Eventlog extends Component
{
    /**
     * @var array<int, array{entry: \App\Models\Eventlog, severityClass: string, port: ?\App\Models\Port}>
     */
    public array $rows;

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Eventlog>  $eventlogs
     * @param  \Illuminate\Support\Collection<int, \App\Models\Port>  $ports
     */
    public function __construct(
        public Device $device,
        \Illuminate\Support\Collection $eventlogs,
        \Illuminate\Support\Collection $ports,
    ) {
        $this->rows = $eventlogs->map(fn (\App\Models\Eventlog $entry): array => [
            'entry' => $entry,
            'severityClass' => match ($entry->severity) {
                Severity::Ok => 'label-success',
                Severity::Error => 'label-danger',
                Severity::Info => 'label-info',
                Severity::Notice => 'label-primary',
                Severity::Warning => 'label-warning',
                default => 'label-default',
            },
            'port' => $entry->type === 'interface' ? $ports->get($entry->reference) : null,
        ])->values()->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.eventlog');
    }
}
