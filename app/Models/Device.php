<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
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
    protected $table = 'devices';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'device_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hostname', 'ip', 'status', 'status_reason'];

    /**
     * Initialize this class
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Device $device) {
            // delete related data
            $device->ports()->delete();
            $device->syslogs()->delete();
            $device->eventlogs()->delete();
        });
    }

    // ---- Helper Functions ----

    /**
     * @return string
     */
    public function logo()
    {
        $base_name = pathinfo($this->icon, PATHINFO_FILENAME);
        $options = [
            "images/logos/$base_name.svg",
            "images/logos/$base_name.png",
            "images/os/$base_name.svg",
            "images/os/$base_name.png",
        ];

        foreach ($options as $file) {
            if (is_file(public_path()."/$file")) {
                return asset($file);
            }
        }

        return asset('images/os/generic.svg');
    }

    /**
     * @return string
     */
    public function statusColour()
    {
        $status = $this->status;
        $ignore = $this->ignore;
        $disabled = $this->disabled;
        if ($disabled == 1) {
            return 'teal';
        } elseif ($ignore == 1) {
            return 'yellow';
        } elseif ($status == 0) {
            return 'danger';
        } else {
            return 'success';
        }
    }

    // ---- Accessors/Mutators ----
    public function getIconAttribute($icon)
    {
        if (isset($icon)) {
            return asset("images/os/$icon");
        }
        return asset('images/os/generic.svg');
    }
    public function getIpAttribute($ip)
    {
        if (empty($ip)) {
            return null;
        }
        // @ suppresses warning, inet_ntop() returns false if it fails
        return @inet_ntop($ip) ?: null;
    }

    public function setIpAttribute($ip)
    {
        $this->attributes['ip'] = inet_pton($ip);
    }

    // ---- Query scopes ----

    public function scopeIsUp($query)
    {
        return $query->where([
            ['status', '=', 1],
            ['ignore', '=', 0],
            ['disabled', '=', 0]
        ]);
    }

    public function scopeIsDown($query)
    {
        return $query->where([
            ['status', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0]
        ]);
    }

    public function scopeIsIgnored($query)
    {
        return $query->where([
            ['ignore', '=', 1],
            ['disabled', '=', 0]
        ]);
    }

    public function scopeNotIgnored($query)
    {
        return $query->where([
            ['ignore', '=', 0]
        ]);
    }

    public function scopeIsDisabled($query)
    {
        return $query->where([
            ['disabled', '=', 1]
        ]);
    }

    // ---- Define Relationships ----

    /**
     * Relationship to App\Models\Alerting\Alert
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function alerts()
    {
        return $this->hasMany('App\Models\Alerting\Alert', 'device_id');
    }

    public function applications()
    {
        return $this->hasMany('App\Models\Application', 'device_id');
    }

    public function bgppeers()
    {
        return $this->hasMany('App\Models\BgpPeer', 'device_id');
    }

    public function cefSwitching()
    {
        return $this->hasMany('App\Models\CefSwitching', 'device_id');
    }

    public function components()
    {
        return $this->hasMany('App\Models\Component', 'device_id');
    }

    /**
     * Relationship to App\Models\General\Eventlog
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventlogs()
    {
        return $this->hasMany('App\Models\General\Eventlog', 'host', 'device_id');
    }

    /**
     * Relationship to App\Models\DeviceGroup
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('App\Models\DeviceGroup', 'device_group_device', 'device_id', 'device_group_id');
    }

    public function ospfInstances()
    {
        return $this->hasMany('App\Models\OspfInstance', 'device_id');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\Package', 'device_id', 'device_id');
    }

    /**
     * Relationship to App\Models\Port
     * Returns a list of the ports this device has.
     */
    public function ports()
    {
        return $this->hasMany('App\Models\Port', 'device_id', 'device_id');
    }

    /**
     * Relationship to App\Models\Processor
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function processors()
    {
        return $this->hasMany('App\Models\Processor', 'device_id');
    }

    /**
     * Relationship to App\Models\Alerting\Rule
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function rules()
    {
        return $this->hasMany('App\Models\Alerting\Rule', 'device_id');
    }

    /**
     * Relationship to App\Models\Sensor
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sensors()
    {
        return $this->hasMany('App\Models\Sensor', 'device_id');
    }

    /**
     * Relationship to App\Models\Service
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service', 'device_id');
    }

    /**
     * Relationship to App\Models\Storage
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function storage()
    {
        return $this->hasMany('App\Models\Storage', 'device_id');
    }

    /**
     * Relationship to App\Models\General\Syslog
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syslogs()
    {
        return $this->hasMany('App\Models\General\Syslog', 'device_id', 'device_id');
    }

    /**
     * Relationship to App\Models\User
     * Does not include users with global permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'devices_perms', 'device_id', 'user_id');
    }

    public function vrfs()
    {
        return $this->hasMany('App\Models\Vrf', 'device_id');
    }
}
