<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    // ---- Helper Functions ----

    /**
     * Mark this notification as read or unread
     *
     * @param bool $enabled
     * @return bool
     */
    public function markRead($enabled = true)
    {
        return $this->setAttrib('read', $enabled);
    }

    /**
     * Mark this notification as sticky or unsticky
     *
     * @var bool $enabled
     * @return bool
     */
    public function markSticky($enabled = true)
    {
        return $this->setAttrib('sticky', $enabled);
    }

    /**
     * @param $name
     * @param $enabled
     * @return bool
     */
    private function setAttrib($name, $enabled)
    {
        if ($enabled === true) {
            $read = new NotificationAttrib;
            $read->user_id = \Auth::user()->user_id;
            $read->key = $name;
            $read->value = 1;
            $this->attribs()->save($read);
            return true;
        } else {
            return $this->attribs()->where('key', $name)->delete();
        }
    }

    // ---- Query Scopes ----

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeIsUnread(Builder $query, User $user)
    {
        return $query->whereNotExists(function ($query) use ($user) {
            $query->select(DB::raw(1))
            ->from('notifications_attribs')
            ->whereRaw('notifications.notifications_id = notifications_attribs.notifications_id')
            ->where('notifications_attribs.user_id', $user->user_id);
        });
    }

    /**
     * Get all sticky notifications
     *
     * @param Builder $query
     */
    public function scopeIsSticky(Builder $query)
    {
        $query->leftJoin('notifications_attribs', 'notifications_attribs.notifications_id', 'notifications.notifications_id')
            ->where(['notifications_attribs.key' => 'sticky', 'notifications_attribs.value' => 1]);
    }

    /**
     * @param Builder $query
     * @param User $user
     * @return mixed
     */
    public function scopeIsArchived(Builder $query, User $user)
    {
        return $query->leftJoin('notifications_attribs', 'notifications.notifications_id', '=', 'notifications_attribs.notifications_id')
            ->source()
            ->where('notifications_attribs.user_id', $user->user_id)
            ->where(['key' => 'read', 'value' => 1])
            ->limit();
    }

    /**
     * @param Builder $query
     * @return $this
     */
    public function scopeLimit(Builder $query)
    {
        return $query->select('notifications.*', 'key', 'users.username');
    }

    /**
     * @param Builder $query
     * @return Builder|static
     */
    public function scopeSource(Builder $query)
    {
        return $query->leftJoin('users', 'notifications.source', '=', 'users.user_id');
    }

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attribs()
    {
        return $this->hasMany('App\Models\NotificationAttrib', 'notifications_id', 'notifications_id');
    }
}
