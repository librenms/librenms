<?php

namespace LibreNMS\Tests\Unit\Models\Traits;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Tests\TestCase;

class FilterableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::connection('testing_memory')->create('filterable_regions', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('testing_memory')->create('filterable_locations', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::connection('testing_memory')->create('filterable_devices', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('hostname')->nullable();
            $table->string('os')->nullable();
            $table->integer('uptime')->nullable();
            $table->timestamps();
        });
    }

    private function device(array $deviceAttrs, ?string $location = null, ?string $region = null): FilterableDevice
    {
        if ($location) {
            $locationAttrs = ['location' => $location];

            if ($region) {
                $reg = FilterableRegion::create(['name' => $region]);
                $locationAttrs['region_id'] = $reg->id;
            }

            $loc = FilterableLocation::create($locationAttrs);
            $deviceAttrs['location_id'] = $loc->id;
        }

        return FilterableDevice::create($deviceAttrs);
    }

    // -------------------------------------------------------------------------
    // filterValidationRules
    // -------------------------------------------------------------------------

    public function test_filter_validation_rules_contains_all_operator_map_keys(): void
    {
        $rules = FilterableDevice::filterValidationRules();

        $this->assertArrayHasKey('filter', $rules);
        $this->assertArrayHasKey('filter.*', $rules);
        $this->assertArrayHasKey('filter.*.*', $rules);
    }

    public function test_filter_validation_rules_rejects_unknown_operator(): void
    {
        $rules = FilterableDevice::filterValidationRules();
        $closure = collect($rules['filter.*'])->first(fn ($r) => $r instanceof \Closure);

        $failed = false;
        $closure('filter.hostname', ['bogus_op' => 'value'], function () use (&$failed): void {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    public function test_filter_validation_rules_accepts_known_operator(): void
    {
        $rules = FilterableDevice::filterValidationRules();
        $closure = collect($rules['filter.*'])->first(fn ($r) => $r instanceof \Closure);

        $failed = false;
        $closure('filter.hostname', ['eq' => 'router1'], function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    // -------------------------------------------------------------------------
    // Equality operators
    // -------------------------------------------------------------------------

    public function test_eq(): void
    {
        $this->device(['hostname' => 'router1', 'os' => 'ios']);
        $this->device(['hostname' => 'router2', 'os' => 'eos']);

        $results = FilterableDevice::applyFilters(['hostname' => ['eq' => 'router1']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router1', $results->first()->hostname);
    }

    public function test_neq(): void
    {
        $this->device(['hostname' => 'router1']);
        $this->device(['hostname' => 'router2']);

        $results = FilterableDevice::applyFilters(['hostname' => ['neq' => 'router1']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router2', $results->first()->hostname);
    }

    // -------------------------------------------------------------------------
    // Comparison operators
    // -------------------------------------------------------------------------

    public function test_gt(): void
    {
        $this->device(['hostname' => 'a', 'uptime' => 100]);
        $this->device(['hostname' => 'b', 'uptime' => 200]);
        $this->device(['hostname' => 'c', 'uptime' => 300]);

        $results = FilterableDevice::applyFilters(['uptime' => ['gt' => 200]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('c', $results->first()->hostname);
    }

    public function test_gte(): void
    {
        $this->device(['hostname' => 'a', 'uptime' => 100]);
        $this->device(['hostname' => 'b', 'uptime' => 200]);

        $results = FilterableDevice::applyFilters(['uptime' => ['gte' => 200]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('b', $results->first()->hostname);
    }

    public function test_lt(): void
    {
        $this->device(['hostname' => 'a', 'uptime' => 100]);
        $this->device(['hostname' => 'b', 'uptime' => 200]);

        $results = FilterableDevice::applyFilters(['uptime' => ['lt' => 200]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('a', $results->first()->hostname);
    }

    public function test_lte(): void
    {
        $this->device(['hostname' => 'a', 'uptime' => 100]);
        $this->device(['hostname' => 'b', 'uptime' => 200]);

        $results = FilterableDevice::applyFilters(['uptime' => ['lte' => 100]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('a', $results->first()->hostname);
    }

    // -------------------------------------------------------------------------
    // String pattern operators
    // -------------------------------------------------------------------------

    public function test_contains(): void
    {
        $this->device(['hostname' => 'core-router-01']);
        $this->device(['hostname' => 'edge-switch-01']);

        $results = FilterableDevice::applyFilters(['hostname' => ['contains' => 'router']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('core-router-01', $results->first()->hostname);
    }

    public function test_not_contains(): void
    {
        $this->device(['hostname' => 'core-router-01']);
        $this->device(['hostname' => 'edge-switch-01']);

        $results = FilterableDevice::applyFilters(['hostname' => ['not_contains' => 'router']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('edge-switch-01', $results->first()->hostname);
    }

    public function test_starts_with(): void
    {
        $this->device(['hostname' => 'core-router-01']);
        $this->device(['hostname' => 'edge-switch-01']);

        $results = FilterableDevice::applyFilters(['hostname' => ['starts_with' => 'core']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('core-router-01', $results->first()->hostname);
    }

    public function test_ends_with(): void
    {
        $this->device(['hostname' => 'core-router-01']);
        $this->device(['hostname' => 'edge-switch-02']);

        $results = FilterableDevice::applyFilters(['hostname' => ['ends_with' => '01']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('core-router-01', $results->first()->hostname);
    }

    // -------------------------------------------------------------------------
    // Null / empty operators
    // -------------------------------------------------------------------------

    public function test_is_empty_matches_null(): void
    {
        $this->device(['hostname' => 'router1', 'os' => null]);
        $this->device(['hostname' => 'router2', 'os' => 'ios']);

        $results = FilterableDevice::applyFilters(['os' => ['is_empty' => null]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router1', $results->first()->hostname);
    }

    public function test_is_empty_matches_empty_string(): void
    {
        $this->device(['hostname' => 'router1', 'os' => '']);
        $this->device(['hostname' => 'router2', 'os' => 'ios']);

        $results = FilterableDevice::applyFilters(['os' => ['is_empty' => null]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router1', $results->first()->hostname);
    }

    public function test_is_not_empty(): void
    {
        $this->device(['hostname' => 'router1', 'os' => null]);
        $this->device(['hostname' => 'router2', 'os' => '']);
        $this->device(['hostname' => 'router3', 'os' => 'ios']);

        $results = FilterableDevice::applyFilters(['os' => ['is_not_empty' => null]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router3', $results->first()->hostname);
    }

    // -------------------------------------------------------------------------
    // Set operators
    // -------------------------------------------------------------------------

    public function test_in(): void
    {
        $this->device(['hostname' => 'router1', 'os' => 'ios']);
        $this->device(['hostname' => 'router2', 'os' => 'eos']);
        $this->device(['hostname' => 'router3', 'os' => 'junos']);

        $results = FilterableDevice::applyFilters(['os' => ['in' => ['ios', 'eos']]])->get();

        $this->assertCount(2, $results);
        $this->assertEqualsCanonicalizing(['ios', 'eos'], $results->pluck('os')->all());
    }

    public function test_not_in(): void
    {
        $this->device(['hostname' => 'router1', 'os' => 'ios']);
        $this->device(['hostname' => 'router2', 'os' => 'eos']);
        $this->device(['hostname' => 'router3', 'os' => 'junos']);

        $results = FilterableDevice::applyFilters(['os' => ['not_in' => ['ios', 'eos']]])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('junos', $results->first()->os);
    }

    public function test_in_with_comma_separated_string(): void
    {
        $this->device(['hostname' => 'router1', 'os' => 'ios']);
        $this->device(['hostname' => 'router2', 'os' => 'eos']);
        $this->device(['hostname' => 'router3', 'os' => 'junos']);

        $results = FilterableDevice::applyFilters(['os' => ['in' => 'ios,eos']])->get();

        $this->assertCount(2, $results);
    }

    public function test_in_operator_with_empty_values(): void
    {
        $this->device(['hostname' => 'router1', 'os' => 'ios']);

        $results = FilterableDevice::applyFilters(['os' => ['in' => []]])->get();
        $this->assertCount(0, $results);

        $results = FilterableDevice::applyFilters(['os' => ['in' => '']])->get();
        $this->assertCount(0, $results);
    }

    public function test_multiple_fields_apply_and_logic(): void
    {
        $this->device(['hostname' => 'core-rt-01', 'os' => 'ios']);
        $this->device(['hostname' => 'edge-rt-01', 'os' => 'ios']);
        $this->device(['hostname' => 'core-rt-01', 'os' => 'junos']);

        $results = FilterableDevice::applyFilters([
            'hostname' => ['contains' => 'core'],
            'os' => ['eq' => 'ios'],
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('core-rt-01', $results->first()->hostname);
        $this->assertEquals('ios', $results->first()->os);
    }

    // -------------------------------------------------------------------------
    // Relation filtering
    // -------------------------------------------------------------------------

    public function test_relation_eq(): void
    {
        $this->device(['hostname' => 'router1'], 'London');
        $this->device(['hostname' => 'router2'], 'Paris');

        $results = FilterableDevice::applyFilters(['location.location' => ['eq' => 'London']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router1', $results->first()->hostname);
    }

    public function test_relation_contains(): void
    {
        $this->device(['hostname' => 'router1'], 'London DC');
        $this->device(['hostname' => 'router2'], 'Paris DC');

        $results = FilterableDevice::applyFilters(['location.location' => ['contains' => 'London']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router1', $results->first()->hostname);
    }

    public function test_relation_neq(): void
    {
        $this->device(['hostname' => 'router1'], 'London');
        $this->device(['hostname' => 'router2'], 'Paris');

        $results = FilterableDevice::applyFilters(['location.location' => ['neq' => 'London']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('router2', $results->first()->hostname);
    }

    public function test_deeply_nested_relation_filtering(): void
    {
        $this->device(['hostname' => 'uk-router'], 'London', 'Europe');
        $this->device(['hostname' => 'us-router']);

        $results = FilterableDevice::applyFilters([
            'location.region.name' => ['eq' => 'Europe'],
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('uk-router', $results->first()->hostname);
    }

    public function test_relation_neq_includes_records_with_no_relation(): void
    {
        $this->device(['hostname' => 'lon-gw'], 'London');
        $this->device(['hostname' => 'par-gw'], 'Paris');
        $this->device(['hostname' => 'orphan-gw', 'location_id' => null]);

        $results = FilterableDevice::applyFilters([
            'location.location' => ['neq' => 'London'],
        ])->get();

        $this->assertCount(2, $results);
        $hostnames = $results->pluck('hostname')->all();
        $this->assertContains('par-gw', $hostnames);
        $this->assertContains('orphan-gw', $hostnames);
    }

    // -------------------------------------------------------------------------
    // applyFilterSearch helper
    // -------------------------------------------------------------------------

    public function test_apply_filter_search_or_logic(): void
    {
        $this->device(['hostname' => 'core-router', 'os' => 'ios']);
        $this->device(['hostname' => 'edge-switch', 'os' => 'eos']);
        $this->device(['hostname' => 'spine-leaf',  'os' => 'junos']);

        // filterSearch on Device searches both hostname and os
        $results = FilterableDevice::applyFilters(['search' => ['contains' => 'ios']])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('core-router', $results->first()->hostname);
    }

    public function test_apply_filter_search_not_contains_and_logic(): void
    {
        $this->device(['hostname' => 'core-router', 'os' => 'ios']);
        $this->device(['hostname' => 'edge-switch', 'os' => 'eos']);

        $results = FilterableDevice::applyFilters(['search' => ['not_contains' => 'ios']])->get();

        // AND logic: hostname NOT LIKE AND os NOT LIKE — only edge-switch qualifies
        $this->assertCount(1, $results);
        $this->assertEquals('edge-switch', $results->first()->hostname);
    }

    // -------------------------------------------------------------------------
    // Ignored / invalid input
    // -------------------------------------------------------------------------

    public function test_non_filterable_field_is_ignored(): void
    {
        $this->device(['hostname' => 'router1']);

        $results = FilterableDevice::applyFilters(['secret' => ['eq' => 'value']])->get();

        $this->assertCount(1, $results);
    }

    public function test_unknown_operator_is_ignored(): void
    {
        $this->device(['hostname' => 'router1']);

        $results = FilterableDevice::applyFilters(['hostname' => ['bogus' => 'value']])->get();

        $this->assertCount(1, $results);
    }

    public function test_non_array_operators_are_ignored(): void
    {
        $this->device(['hostname' => 'router1']);

        /** @phpstan-ignore-next-line */
        $results = FilterableDevice::applyFilters(['hostname' => 'not-an-array'])->get();

        $this->assertCount(1, $results);
    }
}

// ---------------------------------------------------------------------------
// Inline test doubles
// ---------------------------------------------------------------------------

/**
 * @property int $id
 * @property string|null $location
 * @property int|null $region_id
 */
class FilterableLocation extends Model
{
    protected $connection = 'testing_memory';
    protected $table = 'filterable_locations';
    protected $guarded = [];
    public $timestamps = false;

    public function region(): BelongsTo
    {
        return $this->belongsTo(FilterableRegion::class, 'region_id');
    }
}

/**
 * @property int $id
 * @property string|null $region
 */
class FilterableRegion extends Model
{
    protected $connection = 'testing_memory';
    protected $table = 'filterable_regions';
    protected $guarded = [];
    public $timestamps = false;
}

/**
 * @property int $id
 * @property string|null $hostname
 * @property string|null $os
 * @property int|null $uptime
 * @property int|null $location_id
 */
class FilterableDevice extends Model
{
    use Filterable;

    protected $connection = 'testing_memory';
    protected $table = 'filterable_devices';
    protected $guarded = [];
    public $timestamps = false;

    protected array $filterable = ['hostname', 'os', 'uptime', 'location.location', 'search', 'location.region.name'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(FilterableLocation::class, 'location_id');
    }

    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['hostname', 'os'], $query, $value, $config);
    }
}
