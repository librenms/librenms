<?php

/**
 * ApiAccessController.php
 *
 * User API token management (self-service only).
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
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAccessController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'deny-demo',
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tokens = $user->tokens()
            ->with('tokenable')
            ->orderBy('id')
            ->get();

        return view('user.api-access', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'expires_in' => 'nullable|integer|min:1',
        ]);

        $name = ! empty($validated['description']) ? $validated['description'] : 'api-token';
        $expiresAt = ! empty($validated['expires_in']) ? now()->addDays((int) $validated['expires_in']) : null;

        $token = $request->user()->createToken($name, ['*'], $expiresAt);

        return redirect()
            ->route('api-access.index')
            ->with('api_token_plain', $token->plainTextToken)
            ->with('api_token_message', __('New API token created. Copy it now; it will not be shown again.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'disabled' => 'sometimes|boolean',
            'expires_in' => 'sometimes|nullable|integer|min:0',
            'description' => 'sometimes|nullable|string|max:255',
            'name' => 'sometimes|nullable|string|max:255',
        ]);

        if (! array_key_exists('disabled', $validated)
            && ! array_key_exists('expires_in', $validated)
            && ! array_key_exists('description', $validated)
            && ! array_key_exists('name', $validated)
        ) {
            abort(422, 'No updatable fields provided.');
        }

        $token = $this->tokenOwnedByUser($request, $id);

        if (array_key_exists('disabled', $validated) && $validated['disabled']) {
            $token->expires_at = now()->subDay();
        } elseif (array_key_exists('expires_in', $validated)) {
            $token->expires_at = ! empty($validated['expires_in']) ? now()->addDays((int) $validated['expires_in']) : null;
        } elseif (array_key_exists('disabled', $validated) && ! $validated['disabled']) {
            $token->expires_at = null;
        }

        if (array_key_exists('description', $validated)) {
            $token->name = $validated['description'] ?? '';
        } elseif (array_key_exists('name', $validated)) {
            $token->name = $validated['name'] ?? '';
        }

        $token->save();

        $isExpired = ! is_null($token->expires_at) && $token->expires_at->isPast();
        $statusHuman = $isExpired
            ? __('Disabled')
            : ($token->expires_at ? __('Expires :time', ['time' => $token->expires_at->diffForHumans()]) : __('Active'));
        $statusLabel = $isExpired ? 'danger' : ($token->expires_at ? 'info' : 'success');

        return response()->json([
            'status' => 'ok',
            'description' => $token->name,
            'name' => $token->name,
            'disabled' => $isExpired,
            'expires_at' => $token->expires_at?->toIso8601String(),
            'expires_human' => $statusHuman,
            'status_label' => $statusLabel,
        ]);
    }

    public function reset(Request $request, int $id): RedirectResponse
    {
        $token = $this->tokenOwnedByUser($request, $id);
        $name = $token->name;
        $expiresAt = ! is_null($token->expires_at) && ! $token->expires_at->isPast() ? $token->expires_at : null;
        $token->delete();
        $newToken = $request->user()->createToken($name, ['*'], $expiresAt);

        return redirect()
            ->route('api-access.index')
            ->with('api_token_plain', $newToken->plainTextToken)
            ->with('api_token_message', __('Token reset. Copy the new token now; it will not be shown again.'));
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $this->tokenOwnedByUser($request, $id)->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('api-access.index')
            ->with('status', __('API token has been removed.'));
    }

    private function tokenOwnedByUser(Request $request, int $id): PersonalAccessToken
    {
        return PersonalAccessToken::query()
            ->where('id', $id)
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $request->user()->user_id)
            ->firstOrFail();
    }
}
