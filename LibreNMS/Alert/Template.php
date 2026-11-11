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
    public ?AlertTemplate $template = null;

    /**
     * Get the template details
     */
    public function getTemplate(?AlertData $alert = null): ?AlertTemplate
    {
        if ($this->template) {
            // Return the cached template information.
            return $this->template;
        }

        $ruleId = $alert?->rule_id;

        $this->template = AlertTemplate::whereHas('map', function ($query) use ($ruleId): void {
            $query->where('alert_rule_id', '=', $ruleId);
        })->first();
        if (! $this->template) {
            $this->template = AlertTemplate::where('name', '=', 'Default Alert Template')->first();
        }

        return $this->template;
    }

    public function getTitle(AlertData $alert): string
    {
        return $this->bladeTitle($alert);
    }

    public function getBody(AlertData $alert): string
    {
        return $this->bladeBody($alert);
    }

    /**
     * Parse Blade body
     */
    public function bladeBody(AlertData $alert): string
    {
        $template = $alert->template;
        $templateBody = $template instanceof AlertTemplate ? $template->template : ($template['template'] ?? null);
        $templateName = $template instanceof AlertTemplate ? $template->name : ($template['name'] ?? '');

        if (empty($templateBody)) {
            return Blade::render($this->getDefaultTemplate($templateName, 'No template defined'), ['alert' => $alert]);
        }

        try {
            return Blade::render((string) $templateBody, ['alert' => $alert]);
        } catch (\Exception $e) {
            return Blade::render($this->getDefaultTemplate($templateName, $e->getMessage()), ['alert' => $alert]);
        }
    }

    /**
     * Parse Blade title
     */
    public function bladeTitle(AlertData $alert): string
    {
        $template = $alert->template instanceof AlertTemplate ? $alert->template : null;
        $state = $alert->state;
        $isRecovered = $state == AlertState::RECOVERED;

        $defaultTitle = $isRecovered
            ? 'Device ' . $alert->display . ' recovered from ' . ($alert->name ?: $alert->rule)
            : 'Alert for device ' . $alert->display . ' - ' . $alert->name;

        $templateTitle = $isRecovered ? $template?->title_rec : $template?->title;

        $title = $defaultTitle;
        if (! empty($templateTitle)) {
            try {
                $title = Blade::render($templateTitle, ['alert' => $alert]);
            } catch (\Throwable) {
                // fall back to the default title
            }
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
