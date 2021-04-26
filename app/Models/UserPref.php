<?php
/**
 * UserPref.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

class UserPref extends BaseModel
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'users_prefs';
    /** @var array */
    protected $primaryKey = ['user_id', 'pref'];
    protected $fillable = ['user_id', 'pref', 'value'];

    // ---- Helper Functions ----
    public static function getPref(User $user, $pref)
    {
        return $user->preferences()->where('pref', $pref)->value('value');
    }

    public static function setPref(User $user, $pref, $value)
    {
        return UserPref::updateOrCreate(['user_id' => $user->user_id, 'pref' => $pref], ['value' => $value]);
    }

    public static function forgetPref(User $user, $pref)
    {
        return $user->preferences()->where('pref', $pref)->delete();
    }

    // ---- Accessors/Mutators ----

    public function getValueAttribute($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    // ---- Query Scopes ----

    public function scopePref($query, $pref)
    {
        return $query->where('pref', $pref);
    }

    // ---- Define Relationships ----

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Set the keys for a save update query. (no primary key)
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        /** @var array */
        $keys = $this->getKeyName();
        if (! is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query. (no primary key)
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
