<?php

namespace App\Actions\Alerts;

use App\Models\AlertRule;
use RuntimeException;

final class SaveAlertRule
{
    /**
     * @param  array<model-property<AlertRule>, mixed>  $attributes
     * @param  array{devices: list<int>, groups: list<int>, locations: list<int>}  $maps
     */
    public function execute(AlertRule $rule, array $attributes, array $maps): AlertRule
    {
        return $rule->getConnection()->transaction(function () use ($rule, $attributes, $maps): AlertRule {
            if ($rule->exists) {
                $rule = $rule->newQuery()->lockForUpdate()->findOrFail($rule->id);
            }

            $attributes['extra'] = array_replace($rule->extra ?? [], $attributes['extra']);
            $rule->fill($attributes);
            $rule->disabled ??= false;
            if (! $rule->save()) {
                throw new RuntimeException(__('This alert rule could not be saved'));
            }

            $rule->devices()->sync($maps['devices']);
            $rule->groups()->sync($maps['groups']);
            $rule->locations()->sync($maps['locations']);

            return $rule;
        });
    }
}
