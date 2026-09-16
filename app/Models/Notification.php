<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property int $notifications_id
 * @property string $title
 * @property string $body
 * @property int|null $severity
 * @property string $source
 * @property string $checksum
 * @property string $datetime
 * @property int|null $user_id
 * @property string|null $sticky_username
 */
class Notification extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'notifications_id';
    protected $fillable = [
        'title',
        'body',
        'severity',
        'source',
        'checksum',
        'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        // delete attribs for this notification
        static::deleting(function (Notification $notification): void {
            $notification->attribs()->delete();
        });
    }

    // ---- Helper Functions ----

    /**
     * Mark this notification as read or unread
     *
     * @param  bool  $enabled
     * @return bool
     */
    public function markRead(bool $enabled = true): bool
    {
        return $this->setAttrib('read', $enabled);
    }

    /**
     * Mark this notification as sticky or unsticky
     */
    public function markSticky(bool $enabled = true): bool
    {
        return $this->setAttrib('sticky', $enabled);
    }

    /**
     * @param  string  $name
     * @param  bool  $enabled
     * @return bool
     */
    private function setAttrib($name, bool $enabled): bool
    {
        if ($enabled === true) {
            $this->attribs()->firstOrCreate([
                'user_id' => Auth::id(),
                'key' => $name,
            ], [
                'value' => '1',
            ]);

            return true;
        } else {
            return (bool) $this->attribs()->where('user_id', Auth::id())->where('key', $name)->delete();
        }
    }

    // ---- Query Scopes ----

    /**
     * @param  Builder<Notification>  $query
     * @param  User  $user
     * @return mixed
     */
    protected function scopeIsUnread(Builder $query, User $user)
    {
        return $query->whereNotExists(function ($query) use ($user): void {
            $query->select(DB::raw(1))
            ->from('notifications_attribs')
            ->whereRaw('notifications.notifications_id = notifications_attribs.notifications_id')
            ->where('notifications_attribs.user_id', $user->user_id);
        });
    }

    /**
     * Get all sticky notifications
     *
     * @param  Builder<Notification>  $query
     */
    protected function scopeIsSticky(Builder $query): void
    {
        $query->whereExists(function ($query): void {
            $query->select(DB::raw(1))
                ->from('notifications_attribs')
                ->whereColumn('notifications.notifications_id', 'notifications_attribs.notifications_id')
                ->where('notifications_attribs.key', 'sticky')
                ->where('notifications_attribs.value', 1);
        });
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\NotificationAttrib, $this>
     */
    public function attribs(): HasMany
    {
        return $this->hasMany(NotificationAttrib::class, 'notifications_id', 'notifications_id');
    }
}
