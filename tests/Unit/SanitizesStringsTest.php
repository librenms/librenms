<?php

/**
 * SanitizesStringsTest.php
 *
 * Tests for the SanitizesStrings model trait.
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
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Traits\SanitizesStrings;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Tests\TestCase;

class SanitizesStringsTest extends TestCase
{
    private function triggerSaving(Model $model): void
    {
        // Use reflection to invoke the protected fireModelEvent method
        $method = new \ReflectionMethod($model, 'fireModelEvent');
        $method->invoke($model, 'saving');
    }

    public function testValidUtf8PassesThrough(): void
    {
        $model = new SanitizesStringsTestModel;
        $model->name = 'Normal ASCII string';
        $this->triggerSaving($model);

        $this->assertEquals('Normal ASCII string', $model->name);
    }

    public function testValidUtf8MultibytePasses(): void
    {
        $model = new SanitizesStringsTestModel;
        $model->name = 'Øverbyvegen';
        $this->triggerSaving($model);

        $this->assertEquals('Øverbyvegen', $model->name);
    }

    public function testWindows1252IsConverted(): void
    {
        $model = new SanitizesStringsTestModel;
        // \xAE is the registered trademark symbol in Windows-1252
        $model->name = "david\xAE Hybrid Mail Printer";
        $this->triggerSaving($model);

        // Should become the UTF-8 registered trademark symbol ®
        $this->assertEquals("david\xC2\xAE Hybrid Mail Printer", $model->name);
    }

    public function testLatin1IsConverted(): void
    {
        $model = new SanitizesStringsTestModel;
        // \xD8 is Ø in Latin-1/Windows-1252
        $model->name = "\xD8verbyvegen";
        $this->triggerSaving($model);

        $this->assertEquals('Øverbyvegen', $model->name);
    }

    public function testSetRawAttributesSanitized(): void
    {
        $model = new SanitizesStringsTestModel;
        // Simulate SyncsModels path: setRawAttributes bypasses mutators
        $model->setRawAttributes(['name' => "david\xAE Printer", 'count' => 5]);
        $this->triggerSaving($model);

        $this->assertEquals("david\xC2\xAE Printer", $model->name);
        // Non-string attributes should be untouched
        $this->assertEquals(5, $model->count);
    }

    public function testEmptyStringIsUntouched(): void
    {
        $model = new SanitizesStringsTestModel;
        $model->name = '';
        $this->triggerSaving($model);

        $this->assertEquals('', $model->name);
    }
}

/**
 * @property string $name
 * @property int $count
 */
class SanitizesStringsTestModel extends Model
{
    use SanitizesStrings;

    protected $guarded = [];
}
