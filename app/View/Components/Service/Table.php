<?php

namespace App\View\Components\Service;

use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Services;

class Table extends Component
{
    /**
     * @param  Collection<int, Service>  $services
     */
    public function __construct(
        public Collection $services,
        public bool $graphs = false,
    ) {
    }

    public function statusClass(Service $service): string
    {
        return match ((int) $service->service_status) {
            0 => 'label-success',
            1 => 'label-warning',
            2 => 'label-danger',
            default => 'label-info',
        };
    }

    public function statusText(Service $service): string
    {
        return match ((int) $service->service_status) {
            0 => __('OK'),
            1 => __('WARNING'),
            2 => __('CRITICAL'),
            default => __('UNKNOWN'),
        };
    }

    /**
     * The data sources of the service, as ds => title.
     *
     * @return array<string, string>
     */
    public function dataSources(Service $service): array
    {
        $serviceDs = htmlspecialchars_decode((string) $service->service_ds);
        $checkScript = Services::customCheckPath($service->service_type);
        if (is_file($checkScript)) {
            include $checkScript; // may define $check_ds
            if (isset($check_ds) && is_string($check_ds)) {
                $serviceDs = $check_ds;
            }
        }

        $sources = [];
        foreach ((array) json_decode($serviceDs, true) as $ds => $info) {
            $sources[(string) $ds] = (string) ($info['full_name'] ?? $ds);
        }

        return $sources;
    }

    public function render(): View
    {
        return view('components.service.table');
    }
}
