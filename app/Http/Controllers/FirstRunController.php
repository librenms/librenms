<?php

/**
 * FirstRunController.php
 *
 * Help a new user set up the default SNMP credentials and scan their network for devices.
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
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Actions\Device\AddFoundDeviceResult;
use App\Events\NetworkAddressScanned;
use App\Facades\LibrenmsConfig;
use App\Jobs\ScanNetworks;
use App\Models\Device;
use App\Models\Secret;
use App\Models\User;
use App\View\FieldSchema\FieldDefinition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;
use LibreNMS\Polling\Secrets\Definitions\SnmpV3SecretDefinition;
use LibreNMS\Scan\LocalNetworks;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Util\Binaries;
use LibreNMS\Util\IP;
use LibreNMS\Util\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FirstRunController extends Controller
{
    /** a larger scan takes too long to watch, lnms scan:network can scan it */
    public const MAX_ADDRESSES = 4096;

    /**
     * Send a user that can set up LibreNMS to the first run page until it is finished or there are devices.
     */
    public static function shouldShow(User $user): bool
    {
        return ! LibrenmsConfig::get('first_run_complete')
            && self::canSetup($user)
            && ! Device::query()->exists();
    }

    public function index(Request $request, LocalNetworks $localNetworks): View
    {
        $this->authorizeSetup();

        $v3Definition = SecretType::SnmpV3->definition();
        $levels = $v3Definition->fields()['authlevel']->options ?? [];

        $nets = (array) LibrenmsConfig::get('nets', []);
        $suggested = $nets === [] ? $localNetworks->find() : [];

        return view('first-run.index', [
            'config' => [
                'credentials' => $this->credentialList($request->user()),
                'v3_defaults' => $v3Definition->schemaDefaults(),
                'v3_levels' => $levels,
                'networks' => $nets ?: $suggested,
                'networks_suggested' => $suggested !== [],
                'max_addresses' => self::MAX_ADDRESSES,
                'urls' => [
                    'credentials' => route('first-run.credentials'),
                    'networks' => route('first-run.networks'),
                    'scan' => route('first-run.scan'),
                ],
            ],
            'v3Schema' => $v3Definition->schema(),
        ]);
    }

    /**
     * Save the communities and SNMPv3 credentials as the default SNMP credentials, in the order given.
     * Credentials that were removed are no longer defaults, the secrets are kept.
     */
    public function credentials(Request $request): JsonResponse
    {
        $this->authorizeSetup();

        $request->validate([
            'credentials' => ['required', 'array', 'max:30'],
            'credentials.*' => ['array'],
            'credentials.*.id' => ['nullable', 'integer'],
            'credentials.*.type' => ['required_without:credentials.*.id', 'in:community,v3'],
            'credentials.*.community' => ['required_if:credentials.*.type,community', 'string', 'max:255'],
        ], [
            'credentials.required' => __('first_run.credentials.required'),
        ]);
        // the fields of new SNMPv3 credentials are validated below, validated() would drop them
        /** @var array<int, array<string, mixed>> $credentials */
        $credentials = array_values((array) $request->input('credentials'));

        // check every credential before any is saved
        $checked = [];
        foreach ($credentials as $index => $credential) {
            if (isset($credential['id'])) {
                $checked[] = Secret::resolveForTypes((int) $credential['id'], [SecretType::SnmpCommunity, SecretType::SnmpV3], $request->user(), "credentials.$index");
            } elseif ($credential['type'] === 'community') {
                $checked[] = trim((string) $credential['community']);
            } else {
                $checked[] = $this->validateV3($credential, "credentials.$index");
            }
        }

        $ids = [];
        foreach ($checked as $credential) {
            if ($credential instanceof Secret) {
                $ids[] = $credential->id;
            } elseif (is_string($credential)) {
                $ids[] = $this->communitySecretId($credential, $request->user());
            } else {
                $ids[] = $this->v3SecretId($credential);
            }
        }

        LibrenmsConfig::persist('snmp.default_credentials', array_values(array_unique($ids)));

        return response()->json([
            'message' => __('first_run.credentials.saved'),
            'credentials' => $this->credentialList($request->user()),
        ]);
    }

    /**
     * Save the networks to scan to the nets setting.
     */
    public function networks(Request $request, NetworkScanner $scanner): JsonResponse
    {
        $this->authorizeSetup();

        $validated = $request->validate([
            'networks' => ['required', 'array', 'min:1', 'max:32'],
            'networks.*' => ['required', 'string', 'distinct', function (string $attribute, mixed $value, \Closure $fail): void {
                if (IP::parse((string) $value, true) === null || ! str_contains((string) $value, '/')) {
                    $fail(__('first_run.networks.invalid', ['network' => $value]));
                }
            }],
        ]);

        $this->checkScanSize($scanner, $validated['networks']);

        LibrenmsConfig::persist('nets', array_values($validated['networks']));

        return response()->json(['message' => __('first_run.networks.saved')]);
    }

    /**
     * Scan the networks of the nets setting, each result is streamed as a line of JSON as the scan goes.
     */
    public function scan(Request $request, NetworkScanner $scanner, Binaries $binaries): StreamedResponse
    {
        $this->authorizeSetup();
        $request->validate(['ping_fallback' => ['boolean']]);

        $nets = (array) LibrenmsConfig::get('nets', []);
        if ($nets === []) {
            throw ValidationException::withMessages(['networks' => __('first_run.networks.required')]);
        }
        $total = $this->checkScanSize($scanner, $nets);

        $options = ScanOptions::fromConfig(pingFallback: $request->boolean('ping_fallback'));
        $missing = array_keys(array_filter([
            'fping' => $options->icmpEnabled,
            'snmpget' => $options->snmpConfigs !== [],
        ], fn (bool $required, string $binary) => $required && $binaries->find($binary) === null, ARRAY_FILTER_USE_BOTH));
        if ($missing !== []) {
            throw ValidationException::withMessages(['networks' => __('first_run.scan.missing_binary', ['binary' => implode(', ', $missing)])]);
        }

        $job = new ScanNetworks(
            scanId: (string) Str::uuid(),
            pollerGroup: (int) LibrenmsConfig::get('default_poller_group', 0),
            pingFallback: $request->boolean('ping_fallback'),
        );

        $icmp = $options->icmpEnabled;

        return new StreamedResponse(function () use ($job, $total, $icmp): void {
            set_time_limit(0);
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $start = microtime(true);
            // with ping, a host that is not added replied to ping but not SNMP
            $this->send(['type' => 'start', 'total' => $total, 'icmp' => $icmp]);

            // the scan id is new, so no other scan is reported here
            Event::listen(function (NetworkAddressScanned $event) use ($job): void {
                if ($event->scanId === $job->scanId) {
                    $this->send($this->resultLine($event->ip, $event->result));
                }
            });

            try {
                // runs in this process until there are workers, then the page will read the results the job stored
                Bus::dispatchSync($job);
                $this->send(['type' => 'done', 'seconds' => round(microtime(true) - $start, 1)]);
            } catch (Throwable $e) {
                Log::error('First run scan failed: ' . $e->getMessage(), ['exception' => $e]);
                $this->send(['type' => 'error', 'message' => $e->getMessage()]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * The default SNMP credentials in the order they are tried, the SNMPv3 passwords are not sent.
     * Like the secrets pages, a community is only shown to a user that can unmask secrets, the others see its description.
     *
     * @return list<array{id: int, type: 'community', community: string, label: string}|array{id: int, type: 'v3', authname: string, authlevel: string, authalgo: string|null, cryptoalgo: string|null}>
     */
    private function credentialList(User $user): array
    {
        $ids = $this->defaultCredentialIds();
        $unmask = Gate::forUser($user)->allows('unmask', Secret::class);

        return Secret::hasAccess($user)->whereIn('id', $ids)->whereIn('secret_type', [SecretType::SnmpCommunity, SecretType::SnmpV3])->get()
            ->sortBy(fn (Secret $secret) => array_search($secret->id, $ids))
            ->map(fn (Secret $secret) => $secret->secret_type === SecretType::SnmpCommunity ? [
                'id' => $secret->id,
                'type' => 'community',
                'community' => $unmask ? (string) ($secret->data['community'] ?? '') : SecretDefinition::MASK,
                'label' => $unmask ? (string) ($secret->data['community'] ?? '') : $secret->description,
            ] : [
                'id' => $secret->id,
                'type' => 'v3',
                'authname' => (string) ($secret->data['authname'] ?? ''),
                'authlevel' => (string) ($secret->data['authlevel'] ?? SnmpV3SecretDefinition::DEFAULT_AUTHLEVEL),
                'authalgo' => $secret->data['authalgo'] ?? null,
                'cryptoalgo' => $secret->data['cryptoalgo'] ?? null,
            ])->values()->all();
    }

    /**
     * @param  string[]  $networks
     * @return int|float the number of addresses to scan
     *
     * @throws ValidationException when there are too many to watch
     */
    private function checkScanSize(NetworkScanner $scanner, array $networks): int|float
    {
        $parsed = $scanner->withoutOverlaps(array_filter(array_map(fn (string $network) => IP::parse($network, true), $networks)));
        $total = array_sum(array_map(fn (IP $network): int|float => $network->addressCount(), $parsed));

        if ($total > self::MAX_ADDRESSES) {
            throw ValidationException::withMessages(['networks' => __('first_run.networks.too_large', [
                'count' => number_format($total),
                'max' => number_format(self::MAX_ADDRESSES),
            ])]);
        }

        return $total;
    }

    /**
     * Do not show the first run page again.
     */
    public function finish(Request $request): RedirectResponse
    {
        $this->authorizeSetup();

        LibrenmsConfig::persist('first_run_complete', true);

        return $request->input('to') === 'devices' ? redirect()->route('devices') : redirect()->route('home');
    }

    /**
     * Saving the credentials creates secrets, without that permission a user would be stuck on the first step.
     */
    private static function canSetup(User $user): bool
    {
        return $user->can('settings.update') && $user->can('create', Device::class) && $user->can('create', Secret::class);
    }

    private function authorizeSetup(): void
    {
        $this->authorize('settings.update');
        $this->authorize('create', Device::class);
        $this->authorize('create', Secret::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function resultLine(string $ip, AddFoundDeviceResult $result): array
    {
        $line = ['type' => 'result', 'ip' => $ip, 'outcome' => $result->outcome->name];

        // the probe got a reply but adding failed (the reverse DNS name did not reach the host, or a second check failed),
        // the page would count it as replying to ping but not SNMP, so it is reported as an error with the reasons
        if ($result->outcome === DeviceAddOutcome::Unreachable && $result->message !== '') {
            $line['outcome'] = DeviceAddOutcome::Error->name;
            $line['message'] = implode(' ', array_filter(array_map(trim(...), explode(PHP_EOL, $result->message))));
        } elseif (in_array($result->outcome, [DeviceAddOutcome::Added, DeviceAddOutcome::Known, DeviceAddOutcome::Error], true)) {
            // only the hosts that were found are listed, the others are counted
            $line['message'] = strtok($result->message, PHP_EOL) ?: $result->outcome->description();
        }

        $device = $result->device;
        if ($device !== null) {
            $line['device'] = [
                'name' => $device->display,
                'sysName' => $device->sysName,
                'os' => LibrenmsConfig::getOsSetting($device->os, 'text', $device->os),
                'hardware' => $device->hardware,
                'logo' => $device->logo(),
                'url' => Url::deviceUrl($device),
            ];
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function send(array $line): void
    {
        echo json_encode($line, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
        flush();
    }

    /**
     * @return int[]
     */
    private function defaultCredentialIds(): array
    {
        return array_map(intval(...), (array) LibrenmsConfig::get('snmp.default_credentials', []));
    }

    /**
     * The secret of the community, an existing one is reused.
     */
    private function communitySecretId(string $community, User $user): int
    {
        $existing = Secret::hasAccess($user)->where('secret_type', SecretType::SnmpCommunity)->get()
            ->first(fn (Secret $secret) => ($secret->data['community'] ?? null) === $community);

        return $existing->id ?? Secret::create([
            'description' => Secret::uniqueDescription(__('first_run.credentials.community_description')),
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => $community],
        ])->id;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  string  $errorKey  the prefix of the validation error keys
     * @return array<string, mixed>
     */
    private function validateV3(array $data, string $errorKey): array
    {
        $definition = SecretType::SnmpV3->definition();
        $labels = collect($definition->fields())->map(fn (FieldDefinition $field) => $field->label ?? $field->key)->all();
        $rules = $definition->rules();
        // net-snmp refuses shorter passphrases
        $rules['authpass'] = [...(array) $rules['authpass'], 'min:8'];
        $rules['cryptopass'] = [...(array) $rules['cryptopass'], 'min:8'];

        $validator = Validator::make($data, $rules, attributes: $labels);
        if ($validator->fails()) {
            throw ValidationException::withMessages(collect($validator->errors()->messages())->mapWithKeys(fn ($messages, $field) => ["$errorKey.$field" => $messages])->all());
        }

        return $validator->validated();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function v3SecretId(array $data): int
    {
        return Secret::create([
            'description' => Secret::uniqueDescription(__('first_run.credentials.v3_description', ['name' => $data['authname']])),
            'secret_type' => SecretType::SnmpV3,
            'data' => array_merge([
                'authlevel' => SnmpV3SecretDefinition::DEFAULT_AUTHLEVEL,
                'authalgo' => SnmpV3SecretDefinition::DEFAULT_AUTHALGO,
                'cryptoalgo' => SnmpV3SecretDefinition::DEFAULT_CRYPTOALGO,
            ], array_filter($data, fn ($value) => $value !== null && $value !== '')),
        ])->id;
    }
}
