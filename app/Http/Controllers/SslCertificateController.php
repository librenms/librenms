<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SslCertificate;
use Illuminate\Http\Request;

class SslCertificateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SslCertificate::class, 'ssl_certificate');
    }

    /**
     * Display a listing of SSL certificates.
     */
    public function index()
    {
        return view('ssl-certificates.index');
    }

    /**
     * Show the form for adding a new certificate.
     */
    public function create()
    {
        $devices = Device::hasAccess(request()->user())->orderBy('hostname')->get(['device_id', 'hostname']);

        return view('ssl-certificates.create', ['devices' => $devices]);
    }

    /**
     * Store a newly added certificate (fetch from host and save).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'device_id' => 'nullable|exists:devices,device_id',
        ]);

        $host = $validated['host'];
        $port = (int) ($validated['port'] ?? 443);
        $deviceId = $validated['device_id'] ?? null;

        if ($deviceId !== null) {
            $hasAccess = Device::hasAccess($request->user())
                ->where('device_id', $deviceId)
                ->exists();
            if (! $hasAccess) {
                return redirect()->route('ssl-certificates.create')
                    ->withInput()
                    ->withErrors([
                        'device_id' => __('You are not allowed to use the selected device.'),
                    ]);
            }
        }

        try {
            $cert = SslCertificate::fetchAndParse($host, $port);
        } catch (\Throwable $e) {
            return redirect()->route('ssl-certificates.create')
                ->withInput()
                ->withErrors(['host' => __('Failed to fetch certificate: :error', ['error' => $e->getMessage()])]);
        }

        $cert['device_id'] = $deviceId;
        $cert['host'] = $host;
        $cert['port'] = $port;
        $cert['last_checked_at'] = now();
        $cert['disabled'] = false;

        SslCertificate::create($cert);

        return redirect()->route('ssl-certificates.index')
            ->with('success', __('SSL certificate added for :host.', ['host' => $host . ':' . $port]));
    }

    /**
     * Display the specified SSL certificate.
     */
    public function show(SslCertificate $ssl_certificate)
    {
        $ssl_certificate->load('device');

        return view('ssl-certificates.show', ['certificate' => $ssl_certificate]);
    }

    /**
     * Update the specified SSL certificate (e.g. toggle disabled).
     */
    public function update(Request $request, SslCertificate $ssl_certificate)
    {
        $request->validate([
            'disabled' => 'sometimes|boolean',
        ]);

        if ($request->has('disabled')) {
            $ssl_certificate->disabled = (bool) $request->input('disabled');
            $ssl_certificate->save();
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('ssl-certificates.show', $ssl_certificate)
            ->with('success', __('Certificate updated.'));
    }

    /**
     * Soft delete the specified SSL certificate.
     */
    public function destroy(Request $request, SslCertificate $ssl_certificate)
    {
        $ssl_certificate->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('ssl-certificates.index')
            ->with('success', __('SSL certificate deleted.'));
    }
}
