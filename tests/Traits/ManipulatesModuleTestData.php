<?php
/**
 * ManipulatesModuleTestData.php
 *
 * -Description-
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Traits;

use LibreNMS\Util\Module;
use LibreNMS\Util\Number;

trait ManipulatesModuleTestData
{
    protected function prepareDataForDisplay(array $left, array $right, string $module): array
    {
        $moduleInstance = Module::fromName($module);

        $left = $this->indexByCompositeKey($left, $moduleInstance);
        $right = $this->indexByCompositeKey($right, $moduleInstance);

        return $this->flattenForComparison($left, $right);
    }

    /**
     * Re-index rows by composite key generated from sort columns.
     */
    private function indexByCompositeKey(array $data, \LibreNMS\Interfaces\Module $module): array
    {
        $indexed = [];
        foreach ($data as $table => $rows) {
            $indexed[$table] = $this->reindexRows($table, $rows, $module);
        }

        return $indexed;
    }

    private function reindexRows(string $table, array $rows, \LibreNMS\Interfaces\Module $module): array
    {
        $sortColumns = $module->getSortColumns($table);

        $reindexed = [];
        foreach ($rows as $key => $row) {
            $compositeKey = $this->buildCompositeKey($row, $sortColumns) ?? $key;
            $reindexed[$compositeKey] = $row;
        }

        ksort($reindexed);
        return $reindexed;
    }

    private function buildCompositeKey(array $row, array $sortColumns): ?string
    {
        if (empty($sortColumns)) {
            return null;
        }

        $keyParts = [];
        foreach ($sortColumns as $column) {
            $keyParts[] = $row[$column] ?? '';
        }

        return implode('|', $keyParts);
    }

    /**
     * Flatten nested arrays into dot-notation for side-by-side comparison.
     * Only includes rows that exist in BOTH left and right datasets.
     */
    private function flattenForComparison(array $left, array $right): array
    {
        $allTables = array_unique(array_merge(array_keys($left), array_keys($right)));

        $leftFlat = [];
        $rightFlat = [];

        foreach ($allTables as $table) {
            $leftTable = $left[$table] ?? [];
            $rightTable = $right[$table] ?? [];

            // Only process keys that exist in BOTH left and right
            $commonKeys = array_intersect(array_keys($leftTable), array_keys($rightTable));
            sort($commonKeys);

            foreach ($commonKeys as $index => $key) {
                $this->flattenRow($leftTable[$key], $table, $index, $leftFlat);
                $this->flattenRow($rightTable[$key], $table, $index, $rightFlat);
            }
        }

        return [$leftFlat, $rightFlat];
    }

    private function flattenRow(array $row, string $table, int $index, array &$output): void
    {
        foreach ($row as $field => $value) {
            $output["$table.$index.$field"] = is_float($value) ? Number::cast($value) : $value;
        }
    }
}
