<?php

namespace App\Models;

use App\Facades\LibrenmsConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Data\Source\Icmp\FpingResponse;

class DeviceStats extends DeviceRelatedModel
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'ping_last_timestamp',
        'ping_rtt_last',
        'ping_rtt_prev',
        'ping_rtt_avg',
        'ping_loss_last',
        'ping_loss_prev',
        'ping_loss_avg',
    ];

    public function fillStats(FpingResponse $response): void
    {
        $avg_factor = LibrenmsConfig::get('device_stats_avg_factor');
        $this->ping_last_timestamp = Carbon::now();

        // Only update the latency if we have data
        if ($response->avg_latency) {
            $this->ping_rtt_prev = $this->ping_rtt_last ?: $response->avg_latency;
            $this->ping_rtt_last = $response->avg_latency;
            // Average is calculated as the exponential weighted moving average
            $this->ping_rtt_avg = $this->ping_rtt_avg ? $this->ping_rtt_avg + (($this->ping_rtt_last - $this->ping_rtt_avg) * $avg_factor) : $this->ping_rtt_last;
        }

        // Only update loss if we transmitted a packet
        if ($response->transmitted) {
            $this->ping_loss_prev = $this->ping_loss_last ?: 100 * ($response->transmitted - $response->received) / $response->transmitted;
            $this->ping_loss_last = 100 * ($response->transmitted - $response->received) / $response->transmitted;
            // Average is calculated as the exponential weighted moving average
            $this->ping_loss_avg = $this->ping_loss_avg ? $this->ping_loss_avg + (($this->ping_loss_last - $this->ping_loss_avg) * $avg_factor) : $this->ping_loss_last;
        }
    }
}
