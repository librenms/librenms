<?php

/**
 * SecretController.php
 *
 * -Description-
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

use App\Http\Interfaces\ToastInterface;
use App\Models\Secret;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\SecretType;

class SecretController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Secret::class);

        return view('secrets.index', [
            'secrets' => Secret::hasAccess($request->user())->withCount('devices')->orderBy('description')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Secret::class);

        $type = $request->query('type', 'snmp');
        $secretType = SecretType::tryFrom($type) ?? SecretType::Snmp;
        $schema = $secretType->secretClass()::getUiSchema();

        return view('secrets.create', [
            'types' => SecretType::cases(),
            'currentType' => $secretType,
            'schema' => $schema,
        ]);
    }

    public function store(Request $request, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('create', Secret::class);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'secret_type' => 'required|string',
            'default' => 'boolean',
        ]);

        $secretType = SecretType::tryFrom($validated['secret_type']);
        if (! $secretType) {
            abort(400, 'Invalid secret type.');
        }

        $class = $secretType->secretClass();
        $rules = $class::rules();
        $data = $request->validate($rules);

        Secret::create([
            'description' => $validated['description'],
            'secret_type' => $secretType,
            'default' => $request->boolean('default'),
            'data' => $data,
        ]);

        $toast->success(__('Secret created'));

        return redirect()->route('secrets.index');
    }

    public function edit(Secret $secret): View
    {
        Gate::authorize('update', $secret);

        $secretType = $secret->secret_type;
        $schema = $secretType->secretClass()::getUiSchema();
        $data = Gate::allows('unmask', $secret)
            ? $secret->data
            : $this->maskPasswordFields($secret->data, $schema);

        return view('secrets.edit', [
            'secret' => $secret,
            'schema' => $schema,
            'data' => $data,
        ]);
    }

    public function update(Request $request, Secret $secret, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('update', $secret);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'default'     => 'boolean',
        ]);

        $secretType = $secret->secret_type;
        $class = $secretType->secretClass();
        $data = $request->validate($class::rules());

        if (! Gate::allows('unmask', $secret)) {
            $schema = $class::getUiSchema();
            $data = $this->restoreMaskedFields($data, $secret->data, $schema);
        }

        $secret->update([
            'description' => $validated['description'],
            'default'     => $request->boolean('default'),
            'data'        => $data,
        ]);

        $toast->success(__('Secret updated'));

        return redirect()->route('secrets.index');
    }

    public function destroy(Secret $secret, ToastInterface $toast): RedirectResponse
    {
        Gate::authorize('delete', $secret);

        $secret->delete();

        $toast->success(__('Secret deleted'));

        return redirect()->route('secrets.index');
    }

    private function maskPasswordFields(array $data, array $schema): array
    {
        foreach ($schema as $field => $config) {
            if (($config['type'] ?? null) === 'password' && ! empty($data[$field])) {
                $data[$field] = '__MASKED__';
            }
        }

        return $data;
    }

    private function restoreMaskedFields(array $newData, array $originalData, array $schema): array
    {
        foreach ($schema as $field => $config) {
            if (($config['type'] ?? null) === 'password') {
                if (($newData[$field] ?? null) === '__MASKED__') {
                    $newData[$field] = $originalData[$field] ?? null;
                }
            }
        }

        return $newData;
    }
}
