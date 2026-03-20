<?php

namespace App\Http\Controllers\Table;

use App\Models\SslCertificate;
use Illuminate\Http\Request;

class SslCertificateController extends TableController
{
    protected $model = SslCertificate::class;

    public function searchFields(Request $request): array
    {
        return ['host', 'subject', 'issuer'];
    }

    protected function sortFields($request)
    {
        return [
            'id',
            'host',
            'port',
            'subject',
            'issuer',
            'valid_to',
            'days_until_expiry',
            'last_checked_at',
            'disabled',
        ];
    }

    public function baseQuery(Request $request)
    {
        return SslCertificate::hasAccess($request->user())->with('device:device_id,hostname');
    }

    /**
     * @param  SslCertificate  $model
     * @return array<string, mixed>
     */
    public function formatItem($model)
    {
        $sslCertificate = $model;
        $deviceLink = null;
        if ($sslCertificate->device) {
            $deviceLink = '<a href="' . url('device/' . $sslCertificate->device_id) . '">' . e($sslCertificate->device->hostname) . '</a>';
        }

        $validTo = $sslCertificate->valid_to !== null ? $sslCertificate->valid_to->format('Y-m-d H:i') : null;
        $status = '';
        if ($sslCertificate->disabled) {
            $status = '<span class="label label-default">' . __('Disabled') . '</span>';
        } elseif ($sslCertificate->isExpired()) {
            $status = '<span class="label label-danger">' . __('Expired') . '</span>';
        } elseif ($sslCertificate->expiresWithinDays(30)) {
            $status = '<span class="label label-warning">' . __('Expires soon') . '</span>';
        } else {
            $status = '<span class="label label-success">' . __('Valid') . '</span>';
        }

        $daysUntilExpiry = $sslCertificate->days_until_expiry;
        $daysDisplay = $daysUntilExpiry !== null
            ? (string) $daysUntilExpiry . ' ' . __('days')
            : '—';
        if ($daysUntilExpiry !== null && $daysUntilExpiry < 0) {
            $daysDisplay = '<span class="text-danger">' . $daysUntilExpiry . ' ' . __('days') . '</span>';
        } elseif ($daysUntilExpiry !== null && $daysUntilExpiry <= 30) {
            $daysDisplay = '<span class="text-warning">' . $daysUntilExpiry . ' ' . __('days') . '</span>';
        }

        return [
            'id' => $sslCertificate->id,
            'host' => e($sslCertificate->host),
            'port' => e($sslCertificate->port),
            'subject' => e($sslCertificate->subject),
            'issuer' => e($sslCertificate->issuer),
            'valid_to' => e($validTo),
            'days_until_expiry' => e($daysDisplay),
            'last_checked_at' => $sslCertificate->last_checked_at !== null ? e($sslCertificate->last_checked_at->format('Y-m-d H:i')) : null,
            'device_id' => $sslCertificate->device_id,
            'device' => $deviceLink,
            'status' => $status,
            'disabled' => $sslCertificate->disabled,
        ];
    }
}
