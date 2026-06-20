<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrateAlertRules();
        $this->migrateAlertTemplates();
    }

    private function migrateAlertRules(): void
    {
        $rules = DB::table('alert_rules')->get();

        foreach ($rules as $ruleRow) {
            $changed = false;
            $builder = $ruleRow->builder;
            $query = $ruleRow->query;

            if (str_contains((string) $builder, '"field":"devices.status_reason"')) {
                $builderData = json_decode($builder, true);
                if (is_array($builderData)) {
                    $builderChanged = $this->updateBuilderNode($builderData);
                    if ($builderChanged) {
                        $builder = json_encode($builderData);
                        $changed = true;
                    }
                }
            }

            if (str_contains((string) $query, 'devices.status_reason = ') || str_contains((string) $query, 'devices.status_reason != ')) {
                $query = preg_replace('/devices\.status_reason = \'([^\']+)\'/', 'devices.status_reason LIKE \'%$1%\'', $query);
                $query = preg_replace('/devices\.status_reason != \'([^\']+)\'/', 'devices.status_reason NOT LIKE \'%$1%\'', $query);
                $changed = true;
            }

            if ($changed) {
                DB::table('alert_rules')->where('id', $ruleRow->id)->update([
                    'builder' => $builder,
                    'query' => $query,
                ]);
            }
        }
    }

    private function updateBuilderNode(array &$node): bool
    {
        $changed = false;
        if (isset($node['condition']) && isset($node['rules'])) {
            foreach ($node['rules'] as &$subNode) {
                if ($this->updateBuilderNode($subNode)) {
                    $changed = true;
                }
            }
        } elseif (isset($node['field']) && $node['field'] === 'devices.status_reason') {
            if ($node['operator'] === 'equal') {
                $node['operator'] = 'contains';
                $changed = true;
            } elseif ($node['operator'] === 'not_equal') {
                $node['operator'] = 'not_contains';
                $changed = true;
            }
        }

        return $changed;
    }

    private function migrateAlertTemplates(): void
    {
        $templates = DB::table('alert_templates')->get();

        $replaceFunc = function ($str, &$changed) {
            if (empty($str)) {
                return $str;
            }

            $newStr = preg_replace('/\$alert->status_reason\s*==\s*\'([^\']+)\'/', 'str_contains((string) $alert->status_reason, \'$1\')', (string) $str);
            $newStr = preg_replace('/\$alert->status_reason\s*!=\s*\'([^\']+)\'/', '! str_contains((string) $alert->status_reason, \'$1\')', $newStr);

            if ($newStr !== $str) {
                $changed = true;
            }

            return $newStr;
        };

        foreach ($templates as $templateRow) {
            $changed = false;

            $templateStr = $replaceFunc($templateRow->template, $changed);
            $titleStr = $replaceFunc($templateRow->title, $changed);
            $titleRecStr = $replaceFunc($templateRow->title_rec, $changed);

            if ($changed) {
                DB::table('alert_templates')->where('id', $templateRow->id)->update([
                    'template' => $templateStr,
                    'title' => $titleStr,
                    'title_rec' => $titleRecStr,
                ]);
            }
        }
    }
};
