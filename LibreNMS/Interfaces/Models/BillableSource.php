<?php

/**
 * BillableSource.php
 *
 * A model whose traffic counters can be accounted on a traffic bill.
 * Billing reads the counters from the device each time it runs.
 *
 * To make a new kind of source billable:
 *  - implement this interface and use the App\Models\Traits\Billable trait on the model
 *  - give the model a morph alias in AppServiceProvider::configureMorphAliases()
 *  - add the model to Bill::SOURCE_TYPES
 *  - provide an ajax select controller to pick it in the bill forms (see billingSelectType())
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

namespace LibreNMS\Interfaces\Models;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface BillableSource
{
    /**
     * Name of this kind of source shown in the bill pages, e.g. "Port"
     */
    public static function billingTypeName(): string;

    /**
     * Type of the ajax select used to pick a source in the bill forms (ajax/select/{type})
     */
    public static function billingSelectType(): string;

    /**
     * Key listing these sources in the bills API
     */
    public static function billingApiKey(): string;

    /**
     * Columns of these sources returned by the bills API
     *
     * @return list<string>
     */
    public static function billingApiFields(): array;

    /**
     * Limit a query to sources that carry traffic right now, e.g. operationally up
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public static function filterBillingActive(Builder $query): void;

    /**
     * @return MorphToMany<\App\Models\Bill, covariant \Illuminate\Database\Eloquent\Model, \App\Models\BillCounter>
     */
    public function bills(): MorphToMany;

    /**
     * Billing only accounts sources on up devices handled by the local poller
     *
     * @return BelongsTo<Device, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function device(): BelongsTo;

    /**
     * Read the cumulative inbound and outbound octet counters from the device.
     * Returns null if either can not be read, so a failed read is never accounted as traffic.
     *
     * @return array{0: int, 1: int}|null
     */
    public function fetchBillingCounters(): ?array;

    /**
     * Link speed in bits per second used to reject impossible counter jumps, null for no limit
     */
    public function getBillingSpeed(): ?int;

    /**
     * Human readable name of the source, without the device
     */
    public function getBillingLabel(): string;

    /**
     * Html link to the source (with graph popup) for the bill pages
     */
    public function getBillingLink(): string;

    /**
     * Rrd holding the traffic of this source for the bill graphs, null if it has none.
     * multiplier converts the stored values to bits.
     *
     * @return array{filename: string, ds_in: string, ds_out: string, multiplier: int}|null
     */
    public function getBillingRrd(): ?array;
}
