<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\PeeringdbIx;
use App\Models\PeeringdbIxPeer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Util\AutonomousSystem;
use LibreNMS\Util\Http;

class MaintenanceCachePeeringdb extends LnmsCommand
{
    protected $name = 'maintenance:cache-peeringdb';

    private const API_URL = 'https://peeringdb.com/api';

    /** Collected data is kept for 71 hours */
    private const CACHE_SECONDS = 255600;

    /** ASNs PeeringDB holds no data for are skipped for a week */
    private const NO_DATA_SECONDS = 604800;

    private ?string $apiKey = null;

    private bool $incomplete = false;

    /** @var int[] ASNs we already hold exchanges for */
    private array $knownAsns = [];

    public function handle(): int
    {
        if (LibrenmsConfig::get('peeringdb.enabled') !== true) {
            $this->line('Peering DB integration disabled');

            return 0;
        }

        if ($this->hasFreshData()) {
            $this->line('Cached PeeringDB data found.....');

            return 0;
        }

        // Without an API key we only look up which exchanges our own ASNs are on. Listing
        // every other network at those exchanges is what actually loads the PeeringDB API,
        // so that part stays opt in. See #14598
        $this->apiKey = LibrenmsConfig::get('peeringdb.api_key');

        // If an ASN we already have exchanges for suddenly returns nothing it is more
        // likely a glitch than a removal, so retry it next run rather than for a week.
        $this->knownAsns = PeeringdbIx::query()->distinct()->pluck('asn')->map(intval(...))->all();

        $ixKeep = [];
        $peerKeep = [];

        foreach ($this->localAsns() as $asn) {
            if (Cache::has("peeringdb.no_data.$asn")) {
                $this->line("PeeringDB has no data for AS$asn, skipping");
                continue;
            }

            $exchanges = $this->fetchExchanges($asn);
            if ($exchanges === null) {
                continue;
            }

            foreach ($exchanges as $exchange) {
                $ixId = $exchange->{'ix_id'};

                $ix = PeeringdbIx::updateOrCreate(
                    ['ix_id' => $ixId, 'asn' => $asn],
                    ['name' => $exchange->{'name'}, 'timestamp' => time()],
                );
                $ixKeep[] = $ix->pdb_ix_id;

                // Listing every network at an exchange is the expensive call, key holders only
                if (empty($this->apiKey)) {
                    continue;
                }

                foreach ($this->fetchPeers($ixId) as $peer) {
                    $peerKeep[] = PeeringdbIxPeer::updateOrCreate(
                        ['ix_id' => $ixId, 'peer_id' => $peer->{'id'}],
                        [
                            'remote_asn' => $peer->{'asn'},
                            'remote_ipaddr4' => $peer->{'ipaddr4'},
                            'remote_ipaddr6' => $peer->{'ipaddr6'},
                            'name' => AutonomousSystem::get($peer->{'asn'})->name(),
                            'timestamp' => time(),
                        ],
                    )->pdb_ix_peers_id;
                }
            }
        }

        // A partial run doesn't know which rows are really gone, so leave the existing data
        // alone and let the next run try again.
        if ($this->incomplete) {
            $this->line('PeeringDB sync incomplete, keeping existing data');

            return 1;
        }

        PeeringdbIxPeer::query()->when($peerKeep, fn ($q) => $q->whereNotIn('pdb_ix_peers_id', $peerKeep))->delete();
        PeeringdbIx::query()->when($ixKeep, fn ($q) => $q->whereNotIn('pdb_ix_id', $ixKeep))->delete();

        Cache::put('peeringdb.last_sync', time(), self::CACHE_SECONDS);

        return 0;
    }

    /**
     * Track the sync itself rather than the contents of pdb_ix. Rows are only written for
     * ASNs that have exchanges, so installs whose ASNs return nothing would otherwise
     * re-query PeeringDB on every single run.
     */
    private function hasFreshData(): bool
    {
        if (Cache::has('peeringdb.last_sync')) {
            return true;
        }

        // Adopt the timestamps of data collected before the sync time was tracked,
        // so upgrading does not trigger a needless re-crawl.
        $previous = (int) PeeringdbIx::query()->max('timestamp');
        $age = time() - $previous;

        if ($previous > 0 && $age < self::CACHE_SECONDS) {
            Cache::put('peeringdb.last_sync', $previous, self::CACHE_SECONDS - $age);

            return true;
        }

        return false;
    }

    /**
     * Local ASNs worth asking PeeringDB about, excluding reserved, private and
     * documentation ranges: 23456 (AS_TRANS, RFC6793), 64496-64511 (Documentation,
     * RFC5398), 64512-65534 (Private, RFC6996), 65535 (Well Known, RFC7300),
     * 65536-65551 (Documentation, RFC5398), 65552-131071 (IANA Reserved),
     * 4200000000-4294967294 (Private, RFC6996) and 4294967295 (Reserved, RFC7300).
     *
     * @return int[]
     */
    private function localAsns(): array
    {
        return Device::query()
            ->where('disabled', 0)
            ->where('ignore', 0)
            ->where('bgpLocalAs', '>', 0)
            ->where('bgpLocalAs', '!=', 23456)
            ->whereNotBetween('bgpLocalAs', [64496, 131071])
            ->where('bgpLocalAs', '<', 4200000000)
            ->distinct()
            ->pluck('bgpLocalAs')
            ->map(intval(...))
            ->all();
    }

    /**
     * The exchanges an ASN is present at, or null if there are none to process.
     *
     * @return \stdClass[]|null
     */
    private function fetchExchanges(int $asn): ?array
    {
        $response = $this->fetch(self::API_URL . "/net?depth=2&asn=$asn");

        if ($response->notFound()) {
            $this->line("AS$asn is not in PeeringDB");
            $this->rememberNoData($asn);

            return null;
        }

        if (! $response->successful()) {
            $this->error("PeeringDB lookup for AS$asn failed with status {$response->status()}");
            $this->incomplete = true;

            return null;
        }

        $exchanges = json_decode($response->body())->{'data'}[0]->{'netixlan_set'} ?? [];

        if (empty($exchanges)) {
            $this->line("AS$asn has no exchanges in PeeringDB");
            $this->rememberNoData($asn);

            return null;
        }

        return $exchanges;
    }

    /**
     * Every network present at an exchange.
     *
     * @return \stdClass[]
     */
    private function fetchPeers(int $ixId): array
    {
        $response = $this->fetch(self::API_URL . "/netixlan?ix_id=$ixId");

        if (! $response->successful()) {
            $this->error("PeeringDB peer lookup for IX $ixId failed with status {$response->status()}");
            $this->incomplete = true;

            return [];
        }

        return json_decode($response->body())->{'data'} ?? [];
    }

    private function rememberNoData(int $asn): void
    {
        if (! in_array($asn, $this->knownAsns)) {
            Cache::put("peeringdb.no_data.$asn", true, self::NO_DATA_SECONDS);
        }
    }

    /**
     * Requests are spaced out so we don't send the whole run at PeeringDB in one burst.
     * The start time of the run itself is randomised by the scheduler.
     */
    private function fetch(string $url): Response
    {
        $delay = random_int(3, 30);
        $this->line("Sleeping for $delay seconds before querying PeeringDB");
        sleep($delay);

        $client = Http::client();

        return ($this->apiKey ? $client->withToken($this->apiKey, 'Api-Key') : $client)->get($url);
    }
}
