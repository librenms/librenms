<?php

namespace App\Graphs\Application;

use Illuminate\Support\Str;

/**
 * The sub states of the sub_<unit type> state type in the sub_state_type var, or of every sub_<unit type>
 */
class SystemdSubGraph extends SystemdGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'sub_state_type' => 'nullable|string'];
    }

    protected function stateTypes(array $systemdMapper): array
    {
        $subStateType = $this->params->input->string('sub_state_type');
        if ($subStateType !== null) {
            return [$subStateType];
        }

        // the part before the last underscore must be 'sub', so sub_service is drawn and load is not
        $isSubType = fn (string $stateType) => str_contains($stateType, '_') && Str::beforeLast($stateType, '_') === 'sub';

        return array_values(array_filter(array_keys($systemdMapper), $isSubType));
    }

    protected function prefixLabels(): bool
    {
        return $this->params->input->string('sub_state_type') === null;
    }
}
