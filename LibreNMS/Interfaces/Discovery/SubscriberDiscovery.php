<?php

/**
 * SubscriberDiscovery.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Interfaces\Discovery;

use App\Models\SubscriberCount;
use Illuminate\Support\Collection;

interface SubscriberDiscovery
{
    /**
     * Subscriber sessions (BNG, BRAS, PPPoE/IPoE concentrator) that are up, by session type
     * (SubscriberCount::SESSION_TYPES). One per session type for the device (port_id null) and
     * per access port where the device reports it. This is called at discovery and polling,
     * so it should only use a few walks of aggregated counters, not walk every session.
     * Returns null when the device did not answer, the counts are then kept as they are.
     *
     * @return Collection<int, SubscriberCount>|null
     */
    public function discoverSubscribers(): ?Collection;
}
