<?php

/**
 * BillableSource.php
 *
 * A model whose traffic counters can be accounted on a traffic bill.
 * Counters come from the regular poller run for that model, billing never
 * queries the device itself.
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

interface BillableSource
{
    /**
     * Cumulative inbound octet counter as stored by the last poll, null if not polled yet
     */
    public function getBillingInOctets(): ?int;

    /**
     * Cumulative outbound octet counter as stored by the last poll, null if not polled yet
     */
    public function getBillingOutOctets(): ?int;

    /**
     * Unix timestamp the counters were read at, null if unknown
     */
    public function getBillingCounterTime(): ?int;

    /**
     * Link speed in bits per second used to reject impossible counter jumps, null for no limit
     */
    public function getBillingSpeed(): ?int;

    /**
     * Human readable name for log output
     */
    public function getBillingLabel(): string;
}
