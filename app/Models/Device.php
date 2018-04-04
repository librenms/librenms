<?php

namespace App\Models;

class Device extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'device_id';
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

    public function scopeIsActive($query)
    {
        return $query->where([
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

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function alerts()
    {
        return $this->hasMany('App\Models\Alert', 'device_id');
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

    public function eventlogs()
    {
        return $this->hasMany('App\Models\General\Eventlog', 'host', 'device_id');
    }

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

    public function ports()
    {
        return $this->hasMany('App\Models\Port', 'device_id', 'device_id');
    }

    public function processors()
    {
        return $this->hasMany('App\Models\Processor', 'device_id');
    }

    public function rules()
    {
        return $this->belongsToMany('App\Models\AlertRule', 'alert_device_map', 'device_id', 'rule_id');
    }

    public function sensors()
    {
        return $this->hasMany('App\Models\Sensor', 'device_id');
    }

    public function services()
    {
        return $this->hasMany('App\Models\Service', 'device_id');
    }

    public function storage()
    {
        return $this->hasMany('App\Models\Storage', 'device_id');
    }

    public function syslogs()
    {
        return $this->hasMany('App\Models\General\Syslog', 'device_id', 'device_id');
    }

    public function users()
    {
        // FIXME does not include global read
        return $this->belongsToMany('App\Models\User', 'devices_perms', 'device_id', 'user_id');
    }

    public function vrfs()
    {
        return $this->hasMany('App\Models\Vrf', 'device_id');
    }
}
