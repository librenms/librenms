<?php
/**
 * 2025_09_09_170500_add_indexes_to_device_outages.php
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_outages', function (Blueprint $table) {
            $table->index('up_again');
            $table->index(['up_again', 'going_down']);
        });
    }

    public function down(): void
    {
        Schema::table('device_outages', function (Blueprint $table) {
            $table->dropIndex('device_outages_up_again_going_down_index');
            $table->dropIndex('device_outages_up_again_index');
        });
    }
};
