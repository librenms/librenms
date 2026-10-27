<?php

namespace App\Models;

use App\Casts\EncryptedSecret;
use App\Casts\SecretTypeCast;
use App\Models\Traits\HasStoredType;
use App\Observers\SecretObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;

#[ObservedBy([SecretObserver::class])]
class Secret extends BaseModel
{
    /** @use HasFactory<\Database\Factories\SecretFactory> */
    use HasFactory;

    use HasStoredType;

    protected $fillable = [
        'description',
        'secret_type',
        'data',
    ];

    /**
     * Never serialize decrypted secret data.
     */
    protected $hidden = [
        'data',
    ];

    public $casts = [
        'secret_type' => SecretTypeCast::class,
        'data' => EncryptedSecret::class,
    ];

    /**
     * Resolve an existing Secret by ID and verify it is one of the expected secret types.
     *
     * @param  SecretType[]  $types
     * @param  string  $errorKey  the validation error key for a secret of another type
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function resolveForTypes(int $id, array $types, ?User $user = null, string $errorKey = 'secret_id'): self
    {
        $query = static::query();
        $user ??= auth()->user();
        if ($user) {
            $query->hasAccess($user);
        }

        $secret = $query->findOrFail($id);

        if (! in_array($secret->secret_type, $types, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $errorKey => __('poller.credential_type_mismatch'),
            ]);
        }

        return $secret;
    }

    /**
     * A unique description for a new secret for a device's polling method.
     */
    public static function defaultDescription(PollingMethodType $type, string $hostname): string
    {
        $base = strtoupper($type->value) . ' ' . $hostname;
        $description = $base;
        for ($i = 2; static::where('description', $description)->exists(); $i++) {
            $description = "$base ($i)";
        }

        return $description;
    }

    protected function storedTypeColumn(): string
    {
        return 'secret_type';
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
