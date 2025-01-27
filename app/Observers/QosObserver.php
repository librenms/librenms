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
            if ($poll_interval > 0 && $qos->getOriginal('last_polled') > 0) {
                $qos->bytes_out_rate = $this->calcRate($qos->last_bytes_out, $qos->getOriginal('last_bytes_out'), $poll_interval);
                $qos->bytes_in_rate = $this->calcRate($qos->last_bytes_in, $qos->getOriginal('last_bytes_in'), $poll_interval);
                $qos->bytes_drop_out_rate = $this->calcRate($qos->last_bytes_drop_out, $qos->getOriginal('last_bytes_drop_out'), $poll_interval);
                $qos->bytes_drop_in_rate = $this->calcRate($qos->last_bytes_drop_in, $qos->getOriginal('last_bytes_drop_in'), $poll_interval);
                $qos->bytes_drop_out_pct = $this->calcPct($qos->bytes_drop_out_rate, $qos->bytes_out_rate, 2);
                $qos->bytes_drop_in_pct = $this->calcPct($qos->bytes_drop_in_rate, $qos->bytes_in_rate, 2);
                $qos->packets_out_rate = $this->calcRate($qos->last_packets_out, $qos->getOriginal('last_packets_out'), $poll_interval);
                $qos->packets_in_rate = $this->calcRate($qos->last_packets_in, $qos->getOriginal('last_packets_in'), $poll_interval);
                $qos->packets_drop_out_rate = $this->calcRate($qos->last_packets_drop_out, $qos->getOriginal('last_packets_drop_out'), $poll_interval);
                $qos->packets_drop_in_rate = $this->calcRate($qos->last_packets_drop_in, $qos->getOriginal('last_packets_drop_in'), $poll_interval);
                $qos->packets_drop_out_pct = $this->calcPct($qos->packets_drop_out_rate, $qos->packets_out_rate, 2);
                $qos->packets_drop_in_pct = $this->calcPct($qos->packets_drop_in_rate, $qos->packets_in_rate, 2);
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

    private function calcPct(int|null $dividend, int|null $divisor, int $precision): float|null
    {
        if (is_null($dividend) || is_null($divisor)) {
            return null;
        }

        if (! $divisor) {
            return null;
        }

        return round($dividend / $divisor, $precision);
    }
}
