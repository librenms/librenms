<?php

namespace LibreNMS\DB;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use LibreNMS\Interfaces\Models\Keyable;

/**
 * Insert and update a HasMany relation's rows in bulk. Use it instead of
 * SyncsModels for large tables that need no model events.
 *
 * - Input rows match existing records by the related model's Keyable
 *   composite key, within the relation's parent.
 * - Candidates are found by $lookupColumn. It should be selective and part
 *   of the composite key. Records are read as plain arrays, not models.
 * - Only fillable columns are written. The primary key, foreign key and
 *   timestamps are set here; updated_at records when a row was last seen.
 * - Records absent from the input are kept as history. Extra records that
 *   share an input row's key are deleted, and the oldest one is kept.
 * - Writes use the query builder, which skips casts, mutators and events.
 *   Callers must supply database-ready values.
 * - The related table needs an integer primary key and no other unique index.
 * - Runs for the same parent must not overlap.
 */
final readonly class BulkTableSync
{
    private const int CHUNK_SIZE = 1000;

    /** Reused to compute composite keys without hydrating a model per row. */
    private Model&Keyable $keyModel;
    private string $primaryKey;
    private string $foreignKey;
    private string $createdAt;
    private string $updatedAt;
    /** @var array<string, int> fillable columns this class may write, as keys */
    private array $writable;

    /**
     * @param  HasMany<covariant Model, covariant Model>  $relation
     */
    public function __construct(private HasMany $relation, private string $lookupColumn)
    {
        $model = $relation->getRelated();
        if (! $model instanceof Keyable) {
            throw new InvalidArgumentException('Bulk sync requires a Keyable model.');
        }

        $createdAt = $model->getCreatedAtColumn();
        $updatedAt = $model->getUpdatedAtColumn();
        if (! $model->usesTimestamps() || $createdAt === null || $updatedAt === null) {
            throw new InvalidArgumentException('Bulk sync requires a model with timestamps.');
        }

        $this->keyModel = clone $model;
        $this->primaryKey = $model->getKeyName();
        $this->foreignKey = $relation->getForeignKeyName();
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $managed = [$this->primaryKey, $this->foreignKey, $createdAt, $updatedAt];
        $this->writable = array_flip(array_diff($model->getFillable(), $managed));

        if (! isset($this->writable[$lookupColumn])) {
            throw new InvalidArgumentException("Bulk sync lookup column $lookupColumn must be fillable.");
        }
    }

    /**
     * Rows sharing a key: the last one wins. $timestamp defaults to now.
     * $merge adjusts a matched row before comparison, e.g. to keep existing values.
     *
     * @param  iterable<Model|array<string, scalar|null>>  $rows
     * @param  (Closure(array<string, mixed> $row, array<string, mixed> $existing): array<string, mixed>)|null  $merge
     * @return array{inserted: int, updated: int, unchanged: int, deleted: int}
     */
    public function sync(iterable $rows, ?string $timestamp = null, ?Closure $merge = null): array
    {
        $timestamp ??= $this->relation->getRelated()->freshTimestampString();
        $counts = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0, 'deleted' => 0];

        foreach (array_chunk($this->prepare($rows), self::CHUNK_SIZE, true) as $chunk) {
            foreach ($this->syncChunk($chunk, $timestamp, $merge) as $type => $count) {
                $counts[$type] += $count;
            }
        }

        return $counts;
    }

    /**
     * @param  iterable<Model|array<string, scalar|null>>  $rows
     * @return array<string, array<string, mixed>> keyed by composite key
     */
    private function prepare(iterable $rows): array
    {
        $parentKey = $this->relation->getParentKey();
        $prepared = [];

        foreach ($rows as $row) {
            $row = array_intersect_key($row instanceof Model ? $row->getAttributes() : $row, $this->writable);
            if (! isset($row[$this->lookupColumn])) {
                // whereIn() can never find a null, so the row would be inserted again on every run
                throw new InvalidArgumentException("Bulk sync rows require a non-null $this->lookupColumn.");
            }
            $row[$this->foreignKey] = $parentKey;
            $prepared[$this->key($row)] = $row;
        }

        return $prepared;
    }

    /**
     * Matched rows only needing a new last-seen time share one narrow UPDATE.
     * Changed rows go into one upsert of their full records, which keeps
     * their primary keys and created_at.
     *
     * @param  array<string, array<string, mixed>>  $pending  rows keyed by composite key
     * @param  (Closure(array<string, mixed>, array<string, mixed>): array<string, mixed>)|null  $merge
     * @return array{inserted: int, updated: int, unchanged: int, deleted: int}
     */
    private function syncChunk(array $pending, string $timestamp, ?Closure $merge): array
    {
        $matched = $unchanged = $changed = $duplicates = [];

        foreach ($this->existing($pending) as $record) {
            $key = $this->key($record);
            if (isset($matched[$key])) {
                $duplicates[] = $record[$this->primaryKey];
                continue;
            }
            if (! isset($pending[$key])) {
                continue; // history sharing the lookup value
            }

            $row = $pending[$key];
            unset($pending[$key]);
            $matched[$key] = true;

            $changes = $this->changes($merge ? $merge($row, $record) : $row, $record);
            if ($changes) {
                $changed[] = array_replace($record, $changes, [$this->updatedAt => $timestamp]);
            } else {
                $unchanged[] = $record[$this->primaryKey];
            }
        }

        if ($unchanged) {
            $this->query()->whereIntegerInRaw($this->primaryKey, $unchanged)->update([$this->updatedAt => $timestamp]);
        }
        if ($changed) {
            $this->query()->upsert($changed, [$this->primaryKey], [...array_keys($this->writable), $this->updatedAt]);
        }
        foreach (array_chunk($duplicates, self::CHUNK_SIZE) as $ids) {
            $this->query()->whereIntegerInRaw($this->primaryKey, $ids)->delete();
        }
        $this->insert($pending, $timestamp); // rows left over matched nothing

        return ['inserted' => count($pending), 'updated' => count($changed), 'unchanged' => count($unchanged), 'deleted' => count($duplicates)];
    }

    /**
     * Records sharing a lookup value with the rows, oldest first.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function existing(array $rows): array
    {
        $lookup = array_values(array_unique(array_column($rows, $this->lookupColumn), SORT_STRING));

        return array_map(fn (object $record) => (array) $record, $this->query()->reorder()
            ->whereIn($this->lookupColumn, $lookup)
            ->orderBy($this->primaryKey)
            ->get()->all());
    }

    /**
     * Writable values in $values that differ from $record.
     * Values compare as strings, because the database returns strings for some types.
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function changes(array $values, array $record): array
    {
        $changes = [];
        foreach (array_intersect_key($values, $this->writable) as $column => $value) {
            $current = $record[$column] ?? null;
            if (is_bool($value)) {
                $value = (int) $value; // what Laravel binds
            }
            if (($value === null) !== ($current === null) || (string) $value !== (string) $current) {
                $changes[$column] = $value;
            }
        }

        return $changes;
    }

    /**
     * @param  array<string, array<string, mixed>>  $rows
     */
    private function insert(array $rows, string $timestamp): void
    {
        // A multi-row INSERT needs the same columns in every row
        $byColumns = [];
        foreach ($rows as $row) {
            $row[$this->createdAt] = $row[$this->updatedAt] = $timestamp;
            ksort($row);
            $byColumns[implode(',', array_keys($row))][] = $row;
        }

        foreach ($byColumns as $group) {
            $this->query()->insert($group);
        }
    }

    /**
     * A fresh query scoped to the relation's parent.
     * toBase() can return a shared builder, so clone it before adding clauses.
     */
    private function query(): Builder
    {
        return clone $this->relation->toBase();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function key(array $row): string
    {
        $this->keyModel->setRawAttributes($row);

        return (string) $this->keyModel->getCompositeKey();
    }
}
