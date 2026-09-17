<?php

namespace App\Models;

use App\Casts\EncryptedArray;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\SecretType;

class Secret extends BaseModel
{
    /** @use HasFactory<\Database\Factories\SecretFactory> */
    use HasFactory;

    protected $fillable = [
        'description',
        'secret_type',
        'data',
    ];

    public $casts = [
        'secret_type' => SecretType::class,
        'data' => EncryptedArray::class,
    ];

    public function toSecretData(): \LibreNMS\Polling\Secrets\Data\SnmpSecretData|\LibreNMS\Polling\Secrets\Data\IpmiSecretData
    {
        $type = $this->secret_type ?? SecretType::Snmp;

        return $type->createData($this->data ?? []);
    }

    /**
     * Resolve an existing Secret by ID and verify its type matches the polling method type.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function resolveForType(int $id, \LibreNMS\Enum\PollingMethodType $type, ?User $user = null): self
    {
        $query = static::query();
        $user ??= auth()->user();
        if ($user) {
            $query->hasAccess($user);
        }

        $secret = $query->findOrFail($id);

        if ($secret->secret_type->value !== $type->value) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'secret_id' => __('poller.credential_type_mismatch'),
            ]);
        }

        return $secret;
    }

    // ---- Query Scopes ----

    /**
     * @param  Builder<Secret>  $query
     * @return Builder<Secret>
     */
    protected function scopeHasAccess(Builder $query, User $user): Builder
    {
        if (Gate::forUser($user)->allows('viewAll', Secret::class) || Gate::forUser($user)->allows('viewAll', Device::class)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $defaultSecretIds = \App\Facades\LibrenmsConfig::get('snmp.default_credentials', []);
            $query->whereHas('devices', function (Builder $query) use ($user): void {
                $query->whereIntegerInRaw('devices.device_id', \Permissions::devicesForUser($user));
            });

            if (! empty($defaultSecretIds)) {
                $query->orWhereIntegerInRaw('secrets.id', $defaultSecretIds);
            }
        });
    }

    // ---- Define Relationships ----

    /**
     * @return BelongsToMany<Device, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'device_polling_methods', 'secret_id', 'device_id')
            ->withPivot('method_type');
    }

    public function isInUse(): bool
    {
        return $this->devices()->exists();
    }
}
