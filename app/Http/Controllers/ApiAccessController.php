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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

        return view('user.api-access', [
            'tokens' => $tokens,
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

    private function tokenOwnedByUser(Request $request, int $id): ApiToken
    {
        return ApiToken::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->user_id)
            ->firstOrFail();
    }
}
