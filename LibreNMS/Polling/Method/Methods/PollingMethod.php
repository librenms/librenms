<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Validation\ValidationException;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\UnknownSecretTypeException;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Data\SecretData;
use UnexpectedValueException;

/**
 * @template-covariant TConfig of PollingMethodConfig
 */
abstract class PollingMethod
{
    /**
     * The types of secret this method can use, empty when it uses no secret. A method uses one secret at a time.
     *
     * @return list<SecretType>
     */
    public function secretTypes(): array
    {
        return [];
    }

    public function usesSecretType(SecretType $secretType): bool
    {
        return in_array($secretType, $this->secretTypes(), true);
    }

    /**
     * The secret type chosen by input, or the only type this method uses when none was chosen.
     * Null when the type is not one this method uses, or none was chosen from several.
     */
    public function secretTypeFrom(mixed $input): ?SecretType
    {
        $secretTypes = $this->secretTypes();
        $type = $input instanceof SecretType ? $input : SecretType::tryFrom((string) $input);

        if ($type === null) {
            return count($secretTypes) === 1 ? $secretTypes[0] : null;
        }

        return $this->usesSecretType($type) ? $type : null;
    }

    /**
     * Check a method is consistent before it is saved, however it was built.
     *
     * @throws ValidationException
     */
    public function validate(DevicePollingMethod $deviceMethod): void
    {
        $secretType = $deviceMethod->secret?->secret_type;

        if ($secretType !== null && ! $this->usesSecretType($secretType)) {
            throw ValidationException::withMessages(['secret_id' => __('poller.credential_type_mismatch')]);
        }
    }

    /**
     * The typed data of a secret for this method, null without a secret.
     *
     * @throws UnknownSecretTypeException when the secret is of a type this version does not know
     * @throws UnexpectedValueException when this method does not use the type of the secret, validate() prevents saving one
     */
    protected function secretData(?Secret $secret): ?SecretData
    {
        if ($secret === null) {
            return null;
        }

        if ($secret->secret_type === SecretType::Unknown) {
            throw UnknownSecretTypeException::forSecret($secret);
        }

        if (! $this->usesSecretType($secret->secret_type)) {
            throw new UnexpectedValueException("Secret $secret->id is a {$secret->secret_type->value} secret, " . static::class . ' does not use it');
        }

        return $secret->secret_type->data($secret->data ?? []);
    }

    /**
     * Check that the method works with this config.
     * Each method chooses how: a quick check, or fetching the data its modules use, which they then read from the result instead of sending the request again.
     * Must avoid side effects on the target where possible.
     * Returns a failure rather than throwing for an unreachable target.
     * Called at most once per method per poll or discovery, see PerDeviceMethodResults.
     */
    abstract public function probe(Device $device, PollingMethodConfig $config): ProbeResult;

    /**
     * Check a candidate polling method for a device, with the secret and settings it has.
     * Which candidates are tried is up to the caller, see BuildPollingMethodCandidates.
     */
    public function discover(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        return $this->probe($device, $this->config($device, $deviceMethod));
    }

    /**
     * Identify a newly added device (sysName, os, etc.) after this method checked successfully.
     * Only methods that can query the device for its identity implement this.
     */
    public function enrichDeviceMetadata(Device $device): void
    {
    }

    /**
     * The settings of this method: form fields, validation rules and casts.
     */
    abstract public function definition(): PollingMethodDefinition;

    /**
     * Whether a newly added method of this type affects device availability.
     */
    abstract public function defaultAffectsAvailability(): bool;

    /**
     * The value of each setting when it is not set, keyed like the definition fields.
     *
     * @return array<string, mixed>
     */
    abstract public function defaults(?Device $device = null): array;

    /**
     * The config for the device from its stored method, or the defaults if the device does not have this method.
     *
     * @return TConfig
     */
    abstract public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): PollingMethodConfig;
}
