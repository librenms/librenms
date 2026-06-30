<?php

/**
 * Utf8SanitizeTest.php
 *
 * Tests for the Utf8Sanitize Eloquent cast.
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
 * @copyright  2026 LibreNMS Contributors
 */

namespace LibreNMS\Tests\Unit\Casts;

use App\Casts\Utf8Sanitize;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Tests\TestCase;
use Mockery;

class Utf8SanitizeTest extends TestCase
{
    private Utf8Sanitize $cast;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new Utf8Sanitize();
        $this->model = Mockery::mock(Model::class)->makePartial();
    }

    // ── set() ──────────────────────────────────────────────────────────────

    public function testSetNullReturnsNull(): void
    {
        $result = $this->cast->set($this->model, 'hrDeviceDescr', null, []);
        $this->assertNull($result);
    }

    public function testSetAlreadyUtf8PassesThrough(): void
    {
        $value = 'HP LaserJet 4350 Series';
        $this->assertSame($value, $this->cast->set($this->model, 'hrDeviceDescr', $value, []));
    }

    public function testSetWindows1252TrademarkConvertedToUtf8(): void
    {
        // Windows-1252 byte 0x99 is the TRADE MARK SIGN (™); UTF-8 is 0xE2 0x84 0xA2
        $win1252Input = "HP LaserJet\x99";
        $result = $this->cast->set($this->model, 'hrDeviceDescr', $win1252Input, []);
        $this->assertNotNull($result);
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'), 'Result must be valid UTF-8');
        $this->assertStringContainsString('™', $result);
    }

    public function testSetEmptyStringPassesThrough(): void
    {
        $this->assertSame('', $this->cast->set($this->model, 'hrDeviceDescr', '', []));
    }

    // ── get() ──────────────────────────────────────────────────────────────

    public function testGetReturnsValueAsIs(): void
    {
        $value = 'HP LaserJet 4350 Series™';
        $this->assertSame($value, $this->cast->get($this->model, 'hrDeviceDescr', $value, []));
    }

    public function testGetNullReturnsNull(): void
    {
        $this->assertNull($this->cast->get($this->model, 'hrDeviceDescr', null, []));
    }
}
