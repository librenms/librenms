<?php

/**
 * 2025_08_27_124900_add_transport_id_to_alert_template_map_table.php
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
        Schema::table('alert_template_map', function (Blueprint $table) {
            $table->unsignedInteger('transport_id')->nullable()->after('alert_rule_id');
            $table->unique(['alert_rule_id', 'transport_id'], 'alert_rule_transport');
        });
    }

    public function down(): void
    {
        Schema::table('alert_template_map', function (Blueprint $table) {
            $table->dropUnique('alert_rule_transport');
            $table->dropColumn('transport_id');
        });
    }
};
