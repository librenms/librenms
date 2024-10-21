<?php

namespace App\Observers;

use App\Models\Qos;

class QosObserver
{
    /**
     * Handle the Qos "updating" event.
     *
     * @param  \App\Models\Qos  $qos
     * @return void
     */
    public function updating(Qos $qos)
    {
        if ($qos->isDirty('last_polled')) {
            $poll_interval = $qos->last_polled - $qos->getOriginal('last_polled');
            if ($poll_interval > 0) {
                $qos->traffic_out_rate = $this->calcRate($qos->last_traffic_out, $qos->getOriginal('last_traffic_out'), $poll_interval);
                $qos->traffic_in_rate = $this->calcRate($qos->last_traffic_in, $qos->getOriginal('last_traffic_in'), $poll_interval);
                $qos->traffic_drop_out_rate = $this->calcRate($qos->last_traffic_drop_out, $qos->getOriginal('last_traffic_drop_out'), $poll_interval);
                $qos->traffic_drop_in_rate = $this->calcRate($qos->last_traffic_drop_in, $qos->getOriginal('last_traffic_drop_in'), $poll_interval);
                $qos->packet_out_rate = $this->calcRate($qos->last_packet_out, $qos->getOriginal('last_packet_out'), $poll_interval);
                $qos->packet_in_rate = $this->calcRate($qos->last_packet_in, $qos->getOriginal('last_packet_in'), $poll_interval);
                $qos->packet_drop_out_rate = $this->calcRate($qos->last_packet_drop_out, $qos->getOriginal('last_packet_drop_out'), $poll_interval);
                $qos->packet_drop_in_rate = $this->calcRate($qos->last_packet_drop_in, $qos->getOriginal('last_packet_drop_in'), $poll_interval);
            }
        }
    }

    private function calcRate(int|null $val, int|null $lastval, int $interval): int|null
    {
        if (is_null($val)) {
            return null;
        }

        if ($interval <= 0) {
            return null;
        }

        if ($lastval > $val) {
            return null;
        }

        return intval(($val - $lastval) / $interval);
    }
}
