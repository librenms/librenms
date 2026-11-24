<?php

/**
 * LoadBalancerSource.php
 *
 * Reads one vendor's load balancer data as the sections and items the tab shows
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\LoadBalancer;

use App\Http\Controllers\Device\Tabs\LoadBalancer\Data\LoadBalancerItem;
use Illuminate\Support\Collection;

interface LoadBalancerSource
{
    /**
     * How many items each section has, sections without items left out
     *
     * @return array<string, int> keyed by LoadBalancerSection value
     */
    public function counts(): array;

    /**
     * The vendor specific columns of a section, in display order.
     * Keys are fixed, they identify the column in item columns and in urls.
     * name, address, pool and status are taken by the common columns.
     *
     * @return array<string, string> column key => title
     */
    public function columns(LoadBalancerSection $section): array;

    /**
     * @return Collection<int, LoadBalancerItem> keyed by item id
     */
    public function items(LoadBalancerSection $section): Collection;

    /**
     * Members of a pool, empty for other items
     *
     * @return Collection<int, LoadBalancerItem>
     */
    public function members(LoadBalancerSection $section, LoadBalancerItem $item): Collection;

    /**
     * Graphs covering every item of a section
     *
     * @return array<string, string> graph type => title
     */
    public function sectionGraphs(LoadBalancerSection $section): array;

    /**
     * Graphs of one item, drawn with its id
     *
     * @return array<string, string> graph type => title
     */
    public function itemGraphs(LoadBalancerSection $section): array;
}
