<?php

namespace LibreNMS\Polling\Providers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\OS;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\PerDeviceMethodResults;

/**
 * The providers of one capability for a device, for a module to pick from.
 * A module chooses the order with prefer() and how to combine methods:
 * preferred() or firstAvailable() for data from one method, all() to merge data from every method.
 * Methods are only checked when their provider is requested.
 *
 * @template T of object
 */
final readonly class CapabilityProviders
{
    /**
     * @param  class-string<T>  $capability
     * @param  list<PollingMethodType>  $preference
     */
    public function __construct(
        private PerDeviceMethodResults $results,
        private OS $os,
        private string $capability,
        private array $preference = [],
    ) {
    }

    /**
     * Try these methods first, in this order. Other methods that provide the capability follow in the default order.
     *
     * @return self<T>
     */
    public function prefer(PollingMethodType ...$types): self
    {
        return new self($this->results, $this->os, $this->capability, array_values($types));
    }

    /**
     * The enabled methods that can provide the capability, in order of preference. Does not check the methods.
     *
     * @return list<PollingMethodType>
     */
    public function methods(): array
    {
        $registry = app(PollingMethodRegistry::class);
        $device = $this->results->device;

        $ordered = array_merge($this->preference, array_udiff(PollingMethodType::cases(), $this->preference, fn (PollingMethodType $a, PollingMethodType $b): int => strcmp($a->value, $b->value)));

        return array_values(array_filter(
            $ordered,
            fn (PollingMethodType $type): bool => (bool) $device->pollingMethod($type)?->enabled && $registry->get($type)->supports($this->capability),
        ));
    }

    /**
     * The provider of one method, null when it is not available. For data that is linked to a method.
     *
     * @return T|null
     */
    public function for(PollingMethodType $type): ?object
    {
        return $this->results->provider($type, $this->capability, $this->os);
    }

    /**
     * The provider of the most preferred method, null when that method is not available.
     * Does not fall back to other methods, so data does not move between methods when the preferred one is down.
     *
     * @return MethodProvider<T>|null
     */
    public function preferred(): ?MethodProvider
    {
        $type = $this->methods()[0] ?? null;

        return $type === null ? null : $this->wrap($type);
    }

    /**
     * The provider of the most preferred method that is available, checking methods in order until one is.
     *
     * @return MethodProvider<T>|null
     */
    public function firstAvailable(): ?MethodProvider
    {
        foreach ($this->methods() as $type) {
            $provider = $this->wrap($type);
            if ($provider !== null) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * The providers of all available methods, in order of preference, to merge their data. Checks every method.
     *
     * @return list<MethodProvider<T>>
     */
    public function all(): array
    {
        return array_values(array_filter(array_map($this->wrap(...), $this->methods())));
    }

    /**
     * Split models linked to methods by their polling_method attribute, with the provider of each method.
     * Models of methods that are not available are left out.
     *
     * @template TModel of Model
     *
     * @param  EloquentCollection<int, TModel>  $models
     * @return list<array{MethodProvider<T>, EloquentCollection<int, TModel>}>
     */
    public function forModels(EloquentCollection $models): array
    {
        $groups = [];

        foreach (PollingMethodType::cases() as $type) {
            $methodModels = $models->where('polling_method', $type)->values();
            if ($methodModels->isEmpty()) {
                continue;
            }

            $provider = $this->wrap($type);
            if ($provider !== null) {
                $groups[] = [$provider, $methodModels];
            }
        }

        return $groups;
    }

    /**
     * @return MethodProvider<T>|null
     */
    private function wrap(PollingMethodType $type): ?MethodProvider
    {
        $provider = $this->for($type);

        return $provider === null ? null : new MethodProvider($type, $provider);
    }
}
