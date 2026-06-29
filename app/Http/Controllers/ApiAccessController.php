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

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;
use LibreNMS\Authentication\LegacyAuth;

class ApiAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('deny-demo');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tokens = ApiToken::query()
            ->with('user')
            ->where('user_id', $user->user_id)
            ->orderBy('id')
            ->get();

        $v1Tokens = PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $user->user_id)
            ->orderBy('id')
            ->get();

        return view('user.api-access', [
            'tokens' => $tokens,
            'v1_tokens' => $v1Tokens,
            'legacy_auth_type' => LegacyAuth::getType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        $token = ApiToken::generateToken(
            $request->user(),
            $validated['description'] ?? ''
        );

        return redirect()
            ->route('api-access.index')
            ->with('api_token_plain', $token->token_hash)
            ->with('api_token_message', __('New API token created. Copy it now; it will not be shown again.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'disabled' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string|max:255',
        ]);

        if (! array_key_exists('disabled', $validated) && ! array_key_exists('description', $validated)) {
            abort(422, 'No updatable fields provided.');
        }

        $token = $this->tokenOwnedByUser($request, $id);

        if (array_key_exists('disabled', $validated)) {
            $token->disabled = $validated['disabled'];
        }
        if (array_key_exists('description', $validated)) {
            $token->description = $validated['description'] ?? '';
        }

        $token->save();

        return response()->json([
            'status' => 'ok',
            'description' => $token->description,
            'disabled' => (bool) $token->disabled,
        ]);
    }

    public function reset(Request $request, int $id): RedirectResponse
    {
        $token = $this->tokenOwnedByUser($request, $id);
        $plain = $token->rotateTokenHash();

        return redirect()
            ->route('api-access.index')
            ->with('api_token_plain', $plain)
            ->with('api_token_message', __('Token reset. Copy the new token now; it will not be shown again.'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->tokenOwnedByUser($request, $id)->delete();

        return redirect()
            ->route('api-access.index')
            ->with('status', __('API token has been removed.'));
    }

    // ---- v1 (Sanctum) personal access tokens ----

    public function storeV1(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token_name' => 'required|string|max:255',
            'expires_in' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $expiresAt = empty($validated['expires_in'])
            ? null
            : now()->addDays((int) $validated['expires_in']);

        $token = $user->createToken($validated['token_name'], ['*'], $expiresAt);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_id' => $token->accessToken->id,
            'token_name' => $token->accessToken->name,
            'created_at' => __('Just now'),
            'expires_at' => $expiresAt ? $expiresAt->diffForHumans() : __('Never'),
        ], 201);
    }

    public function renewV1(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'extend_days' => 'required|integer|min:0',
        ]);

        $token = $this->v1TokenOwnedByUser($request, $id);

        // 0 days means the token never expires
        $token->expires_at = (int) $validated['extend_days'] === 0
            ? null
            : now()->addDays((int) $validated['extend_days']);
        $token->save();

        return response()->json([
            'expires_at' => $token->expires_at ? $token->expires_at->diffForHumans() : __('Never'),
        ]);
    }

    public function destroyV1(Request $request, int $id): JsonResponse
    {
        $this->v1TokenOwnedByUser($request, $id)->delete();

        return response()->json(['status' => 'ok']);
    }

    private function tokenOwnedByUser(Request $request, int $id): ApiToken
    {
        return ApiToken::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();
    }

    private function v1TokenOwnedByUser(Request $request, int $id): PersonalAccessToken
    {
        return PersonalAccessToken::query()
            ->where('id', $id)
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $request->user()->user_id)
            ->firstOrFail();
    }
}
