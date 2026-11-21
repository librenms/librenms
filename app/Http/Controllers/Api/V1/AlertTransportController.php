<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlertOperationTransportMap;
use App\Models\AlertTransport;
use App\Models\TransportGroupTransport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LibreNMS\Alert\Transport;

/**
 * Alert transports over the v1 API, so an integration can register its own
 * endpoint (for example an API/webhook transport) without the web UI.
 */
class AlertTransportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AlertTransport::class);
        $withConfig = $request->user()->can('update', AlertTransport::class);

        $transports = AlertTransport::query()->orderBy('transport_id')->get()
            ->map(fn (AlertTransport $transport) => $this->serialize($transport, $withConfig));

        return response()->json(['data' => $transports->values()->all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AlertTransport::class);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:64|regex:/^[a-z0-9]+$/i',
            'is_default' => 'sometimes|boolean',
            'config' => 'present|array',
        ]);

        $type = Str::lower($validated['type']);
        $class = Transport::getClass($type);
        if (! class_exists($class) || ! method_exists($class, 'configTemplate')) {
            return $this->error(422, 'Unprocessable Content', "Unknown transport type '$type'.");
        }

        $template = $class::configTemplate();
        $validator = Validator::make($validated['config'], $template['validation'] ?? []);
        if ($validator->fails()) {
            return $this->error(422, 'Unprocessable Content', implode(' ', $validator->errors()->all()));
        }

        // Keep only the fields the transport defines, like the web form does.
        $config = [];
        foreach ($template['config'] ?? [] as $field) {
            if (isset($field['name']) && ($field['type'] ?? '') !== 'hidden') {
                $config[$field['name']] = $validated['config'][$field['name']] ?? ($field['default'] ?? null);
            }
        }

        $transport = new AlertTransport;
        $transport->transport_name = $validated['name'];
        $transport->transport_type = $type;
        $transport->is_default = (bool) ($validated['is_default'] ?? false);
        $transport->transport_config = $config;
        $transport->save();

        return response()->json(['data' => $this->serialize($transport, true)], 201);
    }

    /** The v1 route group does not substitute bindings, so the id is resolved here. */
    public function destroy(int $transport): JsonResponse
    {
        $this->authorize('delete', AlertTransport::class);
        $transport = AlertTransport::query()->findOrFail($transport);

        DB::transaction(function () use ($transport): void {
            AlertOperationTransportMap::query()
                ->where('target_type', 'single')
                ->where('transport_or_group_id', $transport->transport_id)
                ->delete();
            TransportGroupTransport::query()->where('transport_id', $transport->transport_id)->delete();
            $transport->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * The configuration holds credentials (passwords, webhook URLs, API keys),
     * so it is only returned to users who may edit transports.
     *
     * @return array<string, mixed>
     */
    private function serialize(AlertTransport $transport, bool $withConfig): array
    {
        $data = [
            'id' => (int) $transport->transport_id,
            'name' => $transport->transport_name,
            'type' => $transport->transport_type,
            'is_default' => (bool) $transport->is_default,
        ];
        if ($withConfig) {
            $data['config'] = (array) $transport->transport_config;
        }

        return $data;
    }

    private function error(int $status, string $title, string $detail): JsonResponse
    {
        return response()->json(['errors' => [['status' => (string) $status, 'title' => $title, 'detail' => $detail]]], $status);
    }
}
