<?php

namespace App\Http\Controllers;

use App\Models\AlertOperationTransportMap;
use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\Device;
use App\Models\TransportGroupTransport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Exceptions;

class AlertTransportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AlertTransport::class);
        $this->validate($request, ['tab' => Rule::in(['transports', 'groups'])]);

        $types = Transport::list();
        asort($types);

        return view('alert-transports.index', [
            'tab' => $request->string('tab', 'transports'),
            'transports' => AlertTransport::orderBy('transport_name')->get(),
            'groups' => AlertTransportGroup::with(['transports' => fn ($query) => $query->orderBy('transport_name')])->orderBy('transport_group_name')->get(),
            'types' => $types,
            'templates' => array_map(fn (string $type) => Transport::getClass($type)::configTemplate()['config'], array_combine(array_keys($types), array_keys($types))),
        ]);
    }

    public function show(AlertTransport $transport): JsonResponse
    {
        abort_unless(Gate::any(['view', 'update'], $transport), 403);

        return response()->json([
            'id' => $transport->transport_id,
            'name' => $transport->transport_name,
            'type' => $transport->transport_type,
            'is_default' => $transport->is_default,
            'config' => (object) ($transport->transport_config ?? []),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AlertTransport::class);

        $transport = new AlertTransport;
        $this->save($request, $transport);

        return response()->json(['message' => __('Alert transport :name created', ['name' => $transport->transport_name])], 201);
    }

    public function update(Request $request, AlertTransport $transport): JsonResponse
    {
        $this->authorize('update', $transport);

        $this->save($request, $transport);

        return response()->json(['message' => __('Alert transport :name updated', ['name' => $transport->transport_name])]);
    }

    public function test(Request $request, AlertTransport $transport): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $transport);

        /** @var Device $device */
        $device = Device::with('location')->first();
        $alert_data = AlertData::testData($device);

        try {
            $result = $transport->instance()->deliverAlert($alert_data);

            if ($result === true) {
                return response()->json(['status' => 'ok']);
            }
        } catch (AlertTransportDeliveryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => strip_tags($e->getMessage()),
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            $result = basename($e->getFile(), '.php') . ':' . $e->getLine() . ' ' . $e->getMessage();

            // if non-fatal error, return ok
            if ($e instanceof \ErrorException && ! Exceptions::isFatalError($e)) {
                return response()->json([
                    'status' => 'ok',
                    'message' => strip_tags($result),
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => strip_tags($result),
        ]);
    }

    public function destroy(AlertTransport $transport): JsonResponse
    {
        $this->authorize('delete', $transport);

        DB::transaction(function () use ($transport): void {
            AlertOperationTransportMap::where('transport_or_group_id', $transport->transport_id)
                ->where('target_type', 'single')
                ->delete();
            TransportGroupTransport::where('transport_id', $transport->transport_id)->delete();
            $transport->delete();
        });

        return response()->json([
            'status' => 'ok',
            'message' => __('Alert transport has been deleted'),
        ]);
    }

    private function save(Request $request, AlertTransport $transport): void
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => ['required', Rule::in(array_keys(Transport::list()))],
            'is_default' => 'boolean',
            'config' => 'array',
        ]);

        $template = Transport::getClass($validated['type'])::configTemplate();
        $input = $request->input('config', []);
        if ($validated['type'] === 'mail' && ($input['mail-contact'] ?? null) !== 'role' && in_array($input['role'] ?? null, [null, ''], true)) {
            unset($input['role']);
        }
        Validator::make($input, $template['validation'] ?? [])->validate();

        $config = [];
        foreach ($template['config'] as $field) {
            $config[$field['name']] = $field['type'] === 'checkbox'
                ? (filter_var($input[$field['name']] ?? false, FILTER_VALIDATE_BOOL) ? 'on' : null) // transports check for 'on'
                : ($input[$field['name']] ?? null);
        }

        $transport->transport_name = strip_tags($validated['name']);
        $transport->transport_type = $validated['type'];
        $transport->is_default = $validated['is_default'] ?? false;
        $transport->transport_config = $config;
        $transport->save();
    }
}
