<?php

/**
 * Template.php
 *
 * Base Template class
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Alert;

use App\Models\AlertTemplate;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Enum\AlertState;

class Template
{
    public $template;

    /**
     * Get the template details
     *
     * @param  array|null  $obj
     * @return mixed
     */
    public function getTemplate($obj = null)
    {
        if ($this->template) {
            // Return the cached template information.
            return $this->template;
        }
        $this->template = AlertTemplate::whereHas('map', function ($query) use ($obj): void {
            $query->where('alert_rule_id', '=', $obj['rule_id']);
        })->first();
        if (! $this->template) {
            $this->template = AlertTemplate::where('name', '=', 'Default Alert Template')->first();
        }

        return $this->template;
    }

    public function getTitle($data)
    {
        return $this->bladeTitle($data);
    }

    public function getBody($data)
    {
        return $this->bladeBody($data);
    }

    /**
     * Parse Blade body
     *
     * @param  array  $data
     * @return string
     */
    public function bladeBody($data)
    {
        $alert['alert'] = new AlertData($data['alert'] ?? $data);
        try {
            return Blade::render($data['template']->template, $alert);
        } catch (\Exception $e) {
            return Blade::render($this->getDefaultTemplate($data['template']->name ?? '', $e->getMessage()), $alert);
        }
    }

    /**
     * Parse Blade title
     *
     * @param  array  $data
     * @return string
     */
    public function bladeTitle($data)
    {
        $template = $data['template'] ?? null;
        $state = $data['state'] ?? ($data['alert']['state'] ?? null);
        $isRecovered = $state == AlertState::RECOVERED;

        $alertData = new AlertData($data['alert'] ?? $data);

        $display = $alertData['display'];
        $name = $alertData['name'];
        $defaultTitle = $isRecovered
            ? 'Device ' . $display . ' recovered from ' . ($name ?: $alertData['rule'])
            : 'Alert for device ' . $display . ' - ' . $name;

        $templateTitle = $isRecovered
            ? ($template->title_rec ?? null)
            : ($template->title ?? null);

        if (! empty($templateTitle)) {
            try {
                $title = Blade::render($templateTitle, ['alert' => $alertData]);
            } catch (\Throwable) {
                $title = $defaultTitle;
            }
        } else {
            $title = $defaultTitle;
        }

        if ($state == AlertState::ACKNOWLEDGED) {
            $title .= ' Has been acknowledged';
        } elseif ($state == AlertState::WORSE) {
            $title .= ' Has worsened';
        } elseif ($state == AlertState::BETTER) {
            $title .= ' Has improved';
        } elseif ($state == AlertState::CHANGED) {
            $title .= ' changed';
        }

        return $title;
    }

    public function getDefaultTemplate(string $template_name, string $error): string
    {
        return '{{ $alert->title }}' . PHP_EOL .
            'Severity: {{ $alert->severity }}' . PHP_EOL .
            '@if ($alert->state == ' . AlertState::RECOVERED . ')Time elapsed: {{ $alert->elapsed }} @endif ' . PHP_EOL .
            'Timestamp: {{ $alert->timestamp }}' . PHP_EOL .
            'Unique-ID: {{ $alert->uid }}' . PHP_EOL .
            'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif ' . PHP_EOL .
            '@if ($alert->faults)Faults:' . PHP_EOL .
            '@foreach ($alert->faults as $key => $value)' . PHP_EOL .
            '  #{{ $key }}: {{ $value[\'string\'] }} @endforeach' . PHP_EOL .
            '@endif' . PHP_EOL .
            'Alert sent to: @foreach ($alert->contacts as $key => $value) {{ $value }} <{{ $key }}> @endforeach' . PHP_EOL .
            'Warning! Fallback template used due to error in template ' . htmlspecialchars($template_name) . ': ' . htmlspecialchars($error) . PHP_EOL;
    }
}
