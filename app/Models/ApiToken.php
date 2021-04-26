<?php
/**
 * ApiToken.php
 *
 * api_tokens simple tokens for api
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends BaseModel
{
    public $timestamps = false;
    protected $table = 'api_tokens';

    // ---- Helper Functions ----

    /**
     * Check if the given token is valid
     *
     * @param string $token
     * @return bool
     */
    public static function isValid($token, $user_id = null)
    {
        $query = self::query()->isEnabled()->where('token_hash', $token);

        if (! is_null($user_id)) {
            $query->where('user_id', $user_id);
        }

        return $query->exists();
    }

    /**
     * Get User model based on the given API token (or null if invalid)
     *
     * @param string $token
     * @return User|null
     */
    public static function userFromToken($token)
    {
        return User::find(self::idFromToken($token));
    }

    public static function generateToken(User $user, $description = '')
    {
        $token = new static;
        $token->user_id = $user->user_id;
        $token->token_hash = $bytes = bin2hex(random_bytes(16));
        $token->description = $description;
        $token->disabled = false;
        $token->save();

        return $token;
    }

    /**
     * Get the user_id for the given token.
     *
     * @param string $token
     * @return int
     */
    public static function idFromToken($token)
    {
        return self::query()->isEnabled()->where('token_hash', $token)->value('user_id');
    }

    // ---- Query scopes ----

    public function scopeIsEnabled($query)
    {
        return $query->where('disabled', 0);
    }

    // ---- Define Relationships ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
