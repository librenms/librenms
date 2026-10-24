<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ApiDeviceUpdateTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;
    /** @var array<string, string> */
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create(['notes' => 'original', 'purpose' => 'original'])->refresh();
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $this->headers = ['X-Auth-Token' => $admin->createToken('test')->plainTextToken];
    }

    #[DataProvider('updatableFields')]
    public function testUpdatesAllowedFields(string $field, string $value): void
    {
        $this->json('PATCH', "/api/v0/devices/{$this->device->hostname}", [
            'field' => $field, 'data' => $value,
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('message', "Device $field field has been updated");

        $this->assertSame($value, $this->device->refresh()->getAttribute($field));
        if ($field === 'display_template') {
            $this->assertSame($value, $this->device->display);
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function updatableFields(): array
    {
        return [
            'notes' => ['notes', 'hello'],
            'purpose' => ['purpose', 'production'],
            'unchanged notes' => ['notes', 'original'],
            'display template' => ['display_template', 'newname'],
            'legacy poller field' => ['version', 'test version'],
        ];
    }

    #[DataProvider('falseFlags')]
    public function testClearsBooleanFlags(string $field, bool $multiple): void
    {
        $this->device->setAttribute($field, true);
        $this->device->save();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => $multiple ? ['notes', $field] : $field,
            'data' => $multiple ? ['changed', false] : false,
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertFalse($this->device->refresh()->getAttribute($field));
        $this->assertDatabaseHas('devices', ['device_id' => $this->device->device_id, $field => 0]);
        if ($multiple) {
            $this->assertSame('changed', $this->device->notes);
        }
    }

    /** @return array<string, array{string, bool}> */
    public static function falseFlags(): array
    {
        $cases = [];
        foreach (['disabled', 'ignore', 'ignore_status', 'disable_notify', 'status'] as $field) {
            $cases["single $field"] = [$field, false];
            $cases["multiple $field"] = [$field, true];
        }

        return $cases;
    }

    public function testClearsStatusReasonWithAnEmptyString(): void
    {
        $this->device->update(['status_reason' => 'snmp']);

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => 'status_reason', 'data' => '',
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertSame('', $this->device->refresh()->status_reason);
        $this->assertDatabaseHas('devices', ['device_id' => $this->device->device_id, 'status_reason' => '']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('nonUpdatableFields')]
    public function testRejectsFieldsBeforeAnyWrites(array $payload, string $field): void
    {
        $original = $this->device->getRawOriginal();
        $locations = Location::count();
        $message = "Device field '$field' is not updatable";
        if ($field === 'display') {
            $message .= '; use display_template instead';
        }

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", $payload, $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', $message);

        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->assertSame($locations, Location::count());
        $this->assertDatabaseMissing('locations', ['location' => 'Rejected API location']);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function nonUpdatableFields(): array
    {
        $cases = [];
        foreach (['this_field_does_not_exist', 'last_polled', 'device_id', 'hostname', 'display'] as $field) {
            $cases["single $field"] = [['field' => $field, 'data' => 'xyz'], $field];
            $cases["invalid last $field"] = [[
                'field' => ['notes', 'location', $field],
                'data' => ['hello', 'Rejected API location', 'xyz'],
            ], $field];
            $cases["invalid first $field"] = [[
                'field' => [$field, 'location', 'notes'],
                'data' => ['xyz', 'Rejected API location', 'hello'],
            ], $field];
        }

        return $cases;
    }

    public function testUpdatesMultipleFieldsIncludingLocation(): void
    {
        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => ['notes', 'purpose', 'location', 'override_sysLocation', 'display_template'],
            'data' => ['hello', 'production', 'API location', 1, 'newname'],
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('message', 'Device fields have been updated');

        $this->device->refresh();
        $this->assertSame('hello', $this->device->notes);
        $this->assertSame('production', $this->device->purpose);
        $this->assertSame('API location', $this->device->location->location);
        $this->assertSame(1, (int) $this->device->override_sysLocation);
        $this->assertSame('newname', $this->device->display_template);
        $this->assertSame('newname', $this->device->display);
    }

    public function testLocationReusesAnExistingLocation(): void
    {
        $location = Location::factory()->create();
        $count = Location::count();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => 'location', 'data' => $location->location,
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertSame($location->id, $this->device->refresh()->location_id);
        $this->assertSame($count, Location::count());
    }

    public function testUpdatesLocationIdDirectly(): void
    {
        $location = Location::factory()->create();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => 'location_id', 'data' => $location->id,
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertSame($location->id, $this->device->refresh()->location_id);
    }

    public function testExplicitNullClearsNullableFieldsAndLocation(): void
    {
        $location = Location::factory()->create();
        $this->device->location_id = $location->id;
        $this->device->save();
        $count = Location::count();

        foreach ([['field' => 'notes', 'data' => null], ['field' => ['purpose', 'location'], 'data' => [null, null]]] as $payload) {
            $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", $payload, $this->headers)
                ->assertStatus(200)->assertJsonPath('status', 'ok');
        }

        $this->device->refresh();
        $this->assertNull($this->device->notes);
        $this->assertNull($this->device->purpose);
        $this->assertNull($this->device->location_id);
        $this->assertSame($count, Location::count());
    }

    #[DataProvider('writeFailures')]
    public function testTransactionRollsBackEvenAfterTheDeviceWasUpdated(bool $invalidData, int $status): void
    {
        $original = $this->device->getRawOriginal();
        $locations = Location::count();
        $dispatcher = Device::getEventDispatcher();
        Device::setEventDispatcher(clone $dispatcher);
        $updated = false;
        Device::updated(function (Device $device) use (&$updated, $invalidData): void {
            if ($device->device_id === $this->device->device_id) {
                $updated = Device::whereKey($device->device_id)->value('notes') === 'changed';
                if ($invalidData) {
                    throw new \Illuminate\Database\QueryException('testing', 'update devices', [], new \PDOException('Simulated data error', 22000));
                }
                throw new \RuntimeException('Simulated failure after the device update');
            }
        });
        try {
            $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
                'field' => ['location', 'notes'], 'data' => ['Rollback API location', 'changed'],
            ], $this->headers)->assertStatus($status);
        } finally {
            Device::setEventDispatcher($dispatcher);
        }

        $this->assertTrue($updated, 'The failure must happen after the SQL update');
        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->assertSame($locations, Location::count());
        $this->assertDatabaseMissing('locations', ['location' => 'Rollback API location']);
    }

    /** @return list<array{bool, int}> */
    public static function writeFailures(): array
    {
        return [[false, 500], [true, 400]];
    }

    public function testRejectsMalformedJsonWithoutWriting(): void
    {
        $original = $this->device->getRawOriginal();
        $locations = Location::count();
        foreach (['{', 'null', '123', '"notes"', '[]'] as $content) {
            $server = $this->transformHeadersToServerVars($this->headers + ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
            $this->call('PATCH', "/api/v0/devices/{$this->device->device_id}", [], [], [], $server, $content)
                ->assertStatus(400)->assertJsonPath('status', 'error');
        }
        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->assertSame($locations, Location::count());
    }

    /**
     * @param  list<string>  $values
     */
    #[DataProvider('mismatchedData')]
    public function testRejectsMismatchedFieldAndDataCounts(array $values): void
    {
        $original = $this->device->getRawOriginal();
        $locations = Location::count();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => ['location', 'notes'], 'data' => $values,
        ], $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Device fields failed to be updated as the number of fields (2) does not match the supplied data (' . count($values) . ')');

        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->assertSame($locations, Location::count());
    }

    /**
     * @return array<string, array{list<string>}>
     */
    public static function mismatchedData(): array
    {
        return [
            'too few' => [['Rejected API location']],
            'too many' => [['Rejected API location', 'hello', 'extra']],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('malformedFields')]
    public function testRejectsMalformedFieldsWithoutWriting(array $payload): void
    {
        $original = $this->device->getRawOriginal();
        $locations = Location::count();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", $payload, $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('status', 'error');

        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->assertSame($locations, Location::count());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function malformedFields(): array
    {
        return [
            'numeric field' => [['field' => 42, 'data' => 'hello']],
            'nested field' => [['field' => ['location', ['notes']], 'data' => ['Rejected API location', 'hello']]],
            'missing array data' => [['field' => ['location', 'notes']]],
            'scalar array data' => [['field' => ['location', 'notes'], 'data' => 'hello']],
            'object fields' => [['field' => ['first' => 'location'], 'data' => ['Rejected API location']]],
            'object data' => [['field' => ['location'], 'data' => ['first' => 'Rejected API location']]],
            'numeric object fields' => [['field' => (object) ['notes'], 'data' => ['changed']]],
            'numeric object data' => [['field' => ['location'], 'data' => (object) ['Rejected API location']]],
            'missing scalar data' => [['field' => 'notes']],
            'missing location data' => [['field' => 'location']],
            'array for scalar field' => [['field' => 'notes', 'data' => ['x']]],
            'object for scalar field' => [['field' => 'notes', 'data' => ['a' => 1]]],
            'invalid after location' => [['field' => ['location', 'notes'], 'data' => ['Rejected API location', ['a' => 1]]]],
            'invalid before location' => [['field' => ['notes', 'location'], 'data' => [['a' => 1], 'Rejected API location']]],
            'duplicate fields' => [['field' => ['notes', 'notes'], 'data' => ['A', 'B']]],
            'alias collision' => [['field' => ['location', 'location_id'], 'data' => ['Rejected API location', 5]]],
            'reverse alias collision' => [['field' => ['location_id', 'location'], 'data' => [5, 'Rejected API location']]],
            'duplicate location' => [['field' => ['location', 'location'], 'data' => ['Rejected API location', 'Other location']]],
            'invalid IP after location' => [['field' => ['location', 'ip'], 'data' => ['Rejected API location', 'not an IP']]],
            'invalid integer after location' => [['field' => ['location', 'uptime'], 'data' => ['Rejected API location', 'not an integer']]],
            'null not-nullable field' => [['field' => ['location', 'status_reason'], 'data' => ['Rejected API location', null]]],
            'null boolean flag' => [['field' => ['notes', 'disabled'], 'data' => ['changed', null]]],
            'empty poller group' => [['field' => 'poller_group', 'data' => '']],
        ];
    }

    public function testUnauthenticatedRequestCannotUpdateDevice(): void
    {
        $original = $this->device->getRawOriginal();

        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => 'notes', 'data' => 'hello',
        ])->assertStatus(401);

        $this->assertSame($original, $this->device->refresh()->getRawOriginal());
        $this->json('PATCH', "/api/v0/devices/{$this->device->device_id}", [
            'field' => ['location', 'notes'], 'data' => ['Unauthorized API location', ['a' => 1]],
        ])->assertStatus(401);
        $this->assertDatabaseMissing('locations', ['location' => 'Unauthorized API location']);
    }

    public function testMissingDeviceReturnsNotFound(): void
    {
        $this->json('PATCH', '/api/v0/devices/missing.example', [
            'field' => 'notes', 'data' => 'hello',
        ], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Device missing.example not found');
    }
}
