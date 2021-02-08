<?php
/**
 * ComponentTest.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\ComponentPref;
use App\Models\ComponentStatusLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use LibreNMS\Component;
use LibreNMS\Tests\DBTestCase;

class ComponentTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testDeleteComponent()
    {
        $target = \App\Models\Component::factory()->create();

        $this->assertTrue(\App\Models\Component::where('id', $target->id)->exists(), 'Failed to create component, this shouldn\'t happen');

        $component = new Component();
        $component->deleteComponent($target->id);

        $this->assertFalse(\App\Models\Component::where('id', $target->id)->exists(), 'deleteComponent failed to delete the component.');
    }

    public function testGetComponentsEmpty()
    {
        $this->assertEquals([], (new Component())->getComponents(43));
    }

    public function testGetComponentsOptionsType()
    {
        $target = \App\Models\Component::factory()->create();
        $component = new Component();

        $actual = $component->getComponents($target->device_id, ['type' => $target->type]);

        $this->assertCount(1, $actual);
        $this->assertArrayHasKey($target->device_id, $actual);
        $this->assertEquals($this->buildExpected($target), $actual);
    }

    public function testGetComponentsOptionsFilterNotIgnore()
    {
        \App\Models\Component::factory()->create(['device_id' => 1, 'ignore' => 1]);
        $target = \App\Models\Component::factory()->times(2)->create(['device_id' => 1, 'ignore' => 0]);
        $component = new Component();

        $actual = $component->getComponents(1, ['filter' => ['ignore' => ['=', 0]]]);

        $this->assertEquals($this->buildExpected($target), $actual);
    }

    public function testGetComponentsOptionsComplex()
    {
        \App\Models\Component::factory()->create(['label' => 'Search Phrase']);
        \App\Models\Component::factory()->times(2)->create(['label' => 'Something Else']);
        $target = \App\Models\Component::factory()->times(2)->create(['label' => 'Search Phrase']);
        \App\Models\Component::factory()->create(['label' => 'Search Phrase']);
        $component = new Component();

        $options = [
            'filter' => ['label' => ['like', 'Search Phrase']],
            'sort' => 'id desc',
            'limit' => [1, 2],
        ];
        $actual = $component->getComponents(null, $options);

        $this->assertEquals($this->buildExpected($target->reverse()->values()), $actual);
    }

    public function testGetFirstComponentID()
    {
        $input = [
            1 => [37 => [], 14 => []],
            2 => [19 => []],
        ];
        $component = new Component();
        $this->assertEquals(19, $component->getFirstComponentID($input, 2));
        $this->assertEquals(37, $component->getFirstComponentID($input[1]));
    }

    public function testGetComponentCount()
    {
        \App\Models\Component::factory()->times(2)->create(['device_id' => 1, 'type' => 'three']);
        \App\Models\Component::factory()->create(['device_id' => 2, 'type' => 'three']);
        \App\Models\Component::factory()->create(['device_id' => 2, 'type' => 'one']);

        $component = new Component();
        $this->assertEquals(['three' => 3, 'one' => 1], $component->getComponentCount());
        $this->assertEquals(['three' => 2], $component->getComponentCount(1));
        $this->assertEquals(['three' => 1, 'one' => 1], $component->getComponentCount(2));
    }

    public function testSetComponentPrefs()
    {
        // Nightmare function, no where near exhaustive
        $base = \App\Models\Component::factory()->create();
        $component = new Component();

        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: null_val, was added with value: ", $base->device_id, 'component', 3, $base->id]);
        $nullVal = $this->buildExpected($base)[$base->device_id];
        $nullVal[$base->id]['null_val'] = null;
        $component->setComponentPrefs($base->device_id, $nullVal);
        $this->assertEquals('', ComponentPref::where(['component' => $base->id, 'attribute' => 'null_val'])->first()->value);

        \Log::shouldReceive('event')->withArgs(["Component $base->id has been modified: label => new label", $base->device_id, 'component', 3, $base->id]);
        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: thirty, was added with value: 30", $base->device_id, 'component', 3, $base->id]);
        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: json, was added with value: {\"json\":\"array\"}", $base->device_id, 'component', 3, $base->id]);
        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: null_val, was deleted.", $base->device_id, 'component', 4]);
        $multiple = $this->buildExpected($base)[$base->device_id];
        $multiple[$base->id]['label'] = 'new label';
        $multiple[$base->id]['thirty'] = 30;
        $multiple[$base->id]['json'] = json_encode(['json' => 'array']);
        $component->setComponentPrefs($base->device_id, $multiple);

        \Log::shouldReceive('event')->withArgs(["Component $base->id has been modified: label => ", $base->device_id, 'component', 3, $base->id]);
        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: thirty, was deleted.", $base->device_id, 'component', 4]);
        \Log::shouldReceive('event')->withArgs(["Component: $base->type($base->id). Attribute: json, was deleted.", $base->device_id, 'component', 4]);
        $uc = \App\Models\Component::find($base->id);
        $this->assertEquals('new label', $uc->label);
        $this->assertEquals(30, $uc->prefs->where('attribute', 'thirty')->first()->value);
        $this->assertEquals($multiple[$base->id]['json'], $uc->prefs->where('attribute', 'json')->first()->value);

        \Log::shouldReceive('event')->times(0);
        $remove = $this->buildExpected($base)[$base->device_id];
        $component->setComponentPrefs($base->device_id, $remove);
        $this->assertFalse(ComponentPref::where('component', $base->id)->exists());
    }

    public function testCreateComponent()
    {
        $device_id = rand(1, 32);
        $type = Str::random(9);
        $component = (new Component())->createComponent($device_id, $type);

        $this->assertCount(1, $component);
        $component_id = array_key_first($component);

        $expected = ['type' => $type, 'label' => '', 'status' => 0, 'ignore' => 0, 'disabled' => 0, 'error' => ''];

        $this->assertEquals([$component_id => $expected], $component);

        $fromDb = \App\Models\Component::find($component_id);
        $this->assertEquals($device_id, $fromDb->device_id);
        $this->assertEquals($type, $fromDb->type);

        $log = $fromDb->logs->first();
        $this->assertEquals($log->status, 0);
        $this->assertEquals($log->message, 'Component Created');
    }

    public function testGetComponentStatusLog()
    {
        // invalid id fails
        $component = new Component();

        $this->assertEquals(0, $component->createStatusLogEntry(434242, 0, 'failed'), 'incorrectly added log');

        $message = Str::random(8);
        $model = \App\Models\Component::factory()->create();
        $log_id = $component->createStatusLogEntry($model->id, 1, $message);
        $this->assertNotEquals(0, $log_id, ' failed to create log');

        $log = ComponentStatusLog::find($log_id);
        $this->assertEquals(1, $log->status);
        $this->assertEquals($message, $log->message);
    }

    private function buildExpected($target)
    {
        $collection = $target instanceof \App\Models\Component ? collect([$target]) : $target;

        return $collection->groupBy('device_id')->map(function ($group) {
            return $group->keyBy('id')->map(function ($model) {
                $base = ['type' => null, 'label' => null, 'status' => 0, 'ignore' => 0, 'disabled' => 0, 'error' => null];
                $merge = $model->toArray();
                unset($merge['device_id'], $merge['id']);

                return array_merge($base, $merge);
            });
        })->toArray();
    }
}
