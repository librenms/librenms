<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SslCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SslCertificateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SslCertificate::class);
    }

    /**
     * Display a listing of SSL certificates.
     */
    public function index()
    {
        $this->authorize('viewAny', SslCertificate::class);

        return view('ssl-certificates.index');
    }

    /**
     * Show the form for adding a new certificate.
     */
    public function create()
    {
        $this->authorize('create', SslCertificate::class);

        $devices = Device::hasAccess(Auth::user())
            ->orderBy('hostname')
            ->get(['device_id', 'hostname']);

        return view('ssl-certificates.create', ['devices' => $devices]);
    }

    /**
     * Store a newly added certificate (fetch from host and save).
     */
    public function store(Request $request)
    {
        $this->authorize('create', SslCertificate::class);
        $validated = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'device_id' => 'required|integer|exists:devices,device_id',
        ]);

        if (! Device::hasAccess($request->user())->whereKey($validated['device_id'])->exists()) {
            return redirect()->route('ssl-certificates.create')
                ->withInput()
                ->withErrors(['device_id' => __('You may only assign certificates to devices you can access.')]);
        }

        $host = $validated['host'];
        $port = (int) ($validated['port'] ?? 443);
        $deviceId = (int) $validated['device_id'];

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
        $this->authorize('viewAny', SslCertificate::class);
        $ssl_certificate->load('device');

        return view('ssl-certificates.show', ['certificate' => $ssl_certificate]);
    }

    /**
     * Update the specified SSL certificate (e.g. toggle disabled).
     */
    public function update(Request $request, SslCertificate $ssl_certificate)
    {
        $this->authorize('update', $ssl_certificate);
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
        $this->authorize('delete', $ssl_certificate);
        $ssl_certificate->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('ssl-certificates.index')
            ->with('success', __('SSL certificate deleted.'));
    }
}
