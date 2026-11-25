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
    public function __construct(public readonly ?AlertTemplate $template = null)
    {
    }

    /**
     * The template mapped to the rule, or the default template if the rule has none.
     */
    public static function forRule(?int $ruleId): self
    {
        $template = AlertTemplate::whereHas('map', function ($query) use ($ruleId): void {
            $query->where('alert_rule_id', '=', $ruleId);
        })->first();

        return new self($template ?? AlertTemplate::where('name', '=', 'Default Alert Template')->first());
    }

    public function getTitle(AlertData $alert): string
    {
        $isRecovered = $alert->state == AlertState::RECOVERED;

        $title = $isRecovered
            ? 'Device ' . $alert->display . ' recovered from ' . ($alert->name ?: $alert->rule)
            : 'Alert for device ' . $alert->display . ' - ' . $alert->name;

        $templateTitle = $isRecovered ? $this->template?->title_rec : $this->template?->title;
        if (! empty($templateTitle)) {
            try {
                $title = Blade::render($templateTitle, ['alert' => $alert]);
            } catch (\Throwable) {
                // keep the default title
            }
        }

        if ($alert->state == AlertState::ACKNOWLEDGED) {
            $title .= ' Has been acknowledged';
        } elseif ($alert->state == AlertState::WORSE) {
            $title .= ' Has worsened';
        } elseif ($alert->state == AlertState::BETTER) {
            $title .= ' Has improved';
        } elseif ($alert->state == AlertState::CHANGED) {
            $title .= ' changed';
        }

        return $title;
    }

    public function getBody(AlertData $alert): string
    {
        $data = ['alert' => $alert];
        $templateBody = $this->template?->template;

        // No template (or an empty one) is not an error: use the built-in template without a warning.
        if (empty($templateBody)) {
            return Blade::render($this->defaultTemplate(), $data);
        }

        try {
            return Blade::render($templateBody, $data);
        } catch (\Exception $e) {
            return Blade::render($this->defaultTemplate($this->template->name ?? '', $e->getMessage()), $data);
        }
    }

    /**
     * The built-in body template. A warning line is added when it replaces a template that failed.
     */
    private function defaultTemplate(string $templateName = '', ?string $error = null): string
    {
        $template = '{{ $alert->title }}' . PHP_EOL .
            'Severity: {{ $alert->severity }}' . PHP_EOL .
            '@if ($alert->state == ' . AlertState::RECOVERED . ')Time elapsed: {{ $alert->elapsed }} @endif ' . PHP_EOL .
            'Timestamp: {{ $alert->timestamp }}' . PHP_EOL .
            'Unique-ID: {{ $alert->uid }}' . PHP_EOL .
            'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif ' . PHP_EOL .
            '@if ($alert->faults)Faults:' . PHP_EOL .
            '@foreach ($alert->faults as $key => $value)' . PHP_EOL .
            '  #{{ $key }}: {{ $value[\'string\'] }} @endforeach' . PHP_EOL .
            '@endif' . PHP_EOL .
            'Alert sent to: @foreach ($alert->contacts ?? [] as $key => $value) {{ $value }} <{{ $key }}> @endforeach' . PHP_EOL;

        if ($error !== null) {
            $template .= 'Warning! Fallback template used due to error in template ' . htmlspecialchars($templateName) . ': ' . htmlspecialchars($error) . PHP_EOL;
        }

        return $template;
    }
}
