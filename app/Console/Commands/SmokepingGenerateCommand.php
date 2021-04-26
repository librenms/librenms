<?php
/**
 * SmokepingGenerateCommand.php
 *
 * CLI command to generate a smokeping configuration.
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
 * @copyright  2020 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use LibreNMS\Config;
use Symfony\Component\Console\Input\InputOption;

class SmokepingGenerateCommand extends LnmsCommand
{
    protected $name = 'smokeping:generate';
    protected $dnsLookup = true;

    private $ip4count = 0;
    private $ip6count = 0;
    private $warnings = [];

    const IP4PROBE = 'lnmsFPing-';
    const IP6PROBE = 'lnmsFPing6-';

    // These entries are solely used to appease the smokeping config parser and serve no function
    const DEFAULTIP4PROBE = 'FPing';
    const DEFAULTIP6PROBE = 'FPing6';
    const DEFAULTPROBE = self::DEFAULTIP4PROBE;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.smokeping:generate.description'));

        $this->addOption('probes', null, InputOption::VALUE_NONE);
        $this->addOption('targets', null, InputOption::VALUE_NONE);
        $this->addOption('no-header', null, InputOption::VALUE_NONE);
        $this->addOption('single-process', null, InputOption::VALUE_NONE);
        $this->addOption('no-dns', null, InputOption::VALUE_NONE);
        $this->addOption('compat', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->validateOptions()) {
            return 1;
        }

        $devices = Device::isNotDisabled()->orderBy('type')->orderBy('hostname')->get();

        if (sizeof($devices) < 1) {
            $this->error(__('commands.smokeping:generate.no-devices'));

            return 3;
        }

        if ($this->option('probes')) {
            return $this->buildProbesConfiguration();
        } elseif ($this->option('targets')) {
            return $this->buildTargetsConfiguration($devices);
        }

        return 2;
    }

    /**
     * Disable DNS lookups by the configuration builder
     *
     * @return bool
     */
    public function disableDNSLookup()
    {
        return $this->dnsLookup = false;
    }

    /**
     * Build and output the probe configuration
     *
     * @return int
     */
    public function buildProbesConfiguration()
    {
        $probes = $this->assembleProbes(Config::get('smokeping.probes'));
        $header = $this->buildHeader($this->option('no-header'), $this->option('compat'));

        return $this->render($header, $probes);
    }

    /**
     * Build and output the target configuration
     *
     * @return int
     */
    public function buildTargetsConfiguration($devices)
    {
        // Take the devices array and build it into a hierarchical list
        $smokelist = [];
        foreach ($devices as $device) {
            $smokelist[$device->type][$device->hostname] = ['transport' => $device->transport];
        }

        $targets = $this->buildTargets($smokelist, Config::get('smokeping.probes'), $this->option('single-process'));
        $header = $this->buildHeader($this->option('no-header'), $this->option('compat'));

        return $this->render($header, $targets);
    }

    /**
     * Set a warning to be emitted
     *
     * @return void
     */
    public function setWarning($warning)
    {
        $this->warnings[] = sprintf('# %s', $warning);
    }

    /**
     * Bring together the probe lists
     *
     * @param int   $probeCount Number of processes to create
     *
     * @return array
     */
    public function assembleProbes($probeCount)
    {
        if ($probeCount < 1) {
            return [];
        }

        return array_merge(
            $this->buildProbes('FPing', self::DEFAULTIP4PROBE, self::IP4PROBE, Config::get('fping'), $probeCount),
            $this->buildProbes('FPing6', self::DEFAULTIP6PROBE, self::IP6PROBE, Config::get('fping6'), $probeCount)
        );
    }

    /**
     * Determine if a list of probes is needed, and write one if so
     *
     * @param string $module The smokeping module to use for this probe (FPing or FPing6, typically)
     * @param string $defaultProbe A default probe, needed by the smokeping configuration parser
     * @param string $probe The first part of the probe name, e.g. 'lnmsFPing' or 'lnmsFPing6'
     * @param string $binary Path to the relevant probe binary (i.e. the output of `which fping` or `which fping6`)
     * @param int    $probeCount Number of processes to create
     *
     * @return array
     */
    public function buildProbes($module, $defaultProbe, $probe, $binary, $probeCount)
    {
        $lines = [];

        $lines[] = sprintf('+ %s', $module);
        $lines[] = sprintf('  binary = %s', $binary);
        $lines[] = '  blazemode = true';
        $lines[] = sprintf('++ %s', $defaultProbe);

        for ($i = 0; $i < $probeCount; $i++) {
            $lines[] = sprintf('++ %s%s', $probe, $i);
        }

        $lines[] = '';

        return $lines;
    }

    /**
     * Generate a header to append to the smokeping configuration file
     *
     * @return array
     */
    public function buildHeader($noHeader, $compat)
    {
        $lines = [];

        if ($compat) {
            $lines[] = '';
            $lines[] = 'menu = Top';
            $lines[] = 'title = Network Latency Grapher';
            $lines[] = '';
        }

        if (! $noHeader) {
            $lines[] = sprintf('# %s', __('commands.smokeping:generate.header-first'));
            $lines[] = sprintf('# %s', __('commands.smokeping:generate.header-second'));
            $lines[] = sprintf('# %s', __('commands.smokeping:generate.header-third'));

            return array_merge($lines, $this->warnings, ['']);
        }

        return $lines;
    }

    /**
     * Determine if a list of targets is needed, and write one if so
     *
     * @param array $smokelist A list of devices to create a a config block for
     *
     * @return array
     */
    public function buildTargets($smokelist, $probeCount, $singleProcess)
    {
        $lines = [];

        foreach ($smokelist as $type => $devices) {
            if (empty($type)) {
                $type = 'Ungrouped';
            }

            $lines[] = sprintf('+ %s', $this->buildMenuEntry($type));
            $lines[] = sprintf('  menu = %s', $type);
            $lines[] = sprintf('  title = %s', $type);

            $lines[] = '';

            $lines = array_merge($lines, $this->buildDevices($devices, $probeCount, $singleProcess));
        }

        return $lines;
    }

    /**
     * Check arguments passed are sensible
     *
     * @return bool
     */
    private function validateOptions()
    {
        if (! Config::has('smokeping.probes') ||
            ! Config::has('fping') ||
            ! Config::has('fping6')
        ) {
            $this->error(__('commands.smokeping:generate.config-insufficient'));

            return false;
        }

        if (! ($this->option('probes') xor $this->option('targets'))) {
            $this->error(__('commands.smokeping:generate.args-nonsense'));

            return false;
        }

        if (Config::get('smokeping.probes') < 1) {
            $this->error(__('commands.smokeping:generate.no-probes'));

            return false;
        }

        if ($this->option('compat') && ! $this->option('targets')) {
            $this->error(__('commands.smokeping:generate.args-nonsense'));

            return false;
        }

        if ($this->option('no-dns')) {
            $this->disableDNSLookup();
        }

        return true;
    }

    /**
     * Take config lines and output them to stdout
     *
     * @param array ...$blocks Blocks of smokeping configuration arranged in arrays of strings
     *
     * @return int
     */
    private function render(...$blocks)
    {
        foreach (array_merge(...$blocks) as $line) {
            $this->line($line);
        }

        return 0;
    }

    /**
     * Build the configuration for a set of devices inside a type block
     *
     * @param array $devices A list of devices to create a a config block for
     *
     * @return array
     */
    private function buildDevices($devices, $probeCount, $singleProcess)
    {
        $lines = [];

        foreach ($devices as $hostname => $config) {
            if (! $this->dnsLookup || $this->deviceIsResolvable($hostname)) {
                $lines[] = sprintf('++ %s', $this->buildMenuEntry($hostname));
                $lines[] = sprintf('   menu = %s', $hostname);
                $lines[] = sprintf('   title = %s', $hostname);

                if (! $singleProcess) {
                    $lines[] = sprintf('   probe = %s', $this->balanceProbes($config['transport'], $probeCount));
                }

                $lines[] = sprintf('   host = %s', $hostname);
                $lines[] = '';
            }
        }

        return $lines;
    }

    /**
     * Smokeping refuses to load if it has an unresolvable host, so check for this
     *
     * @param string $hostname Hostname to be checked
     *
     * @return bool
     */
    private function deviceIsResolvable($hostname)
    {
        // First we check for IP literals, then for a dns entry, finally for a hosts entry due to a PHP/libc limitation
        // We look for the hosts entry last (and separately) as this only works for v4 - v6 host entries won't be found
        if (filter_var($hostname, FILTER_VALIDATE_IP) || checkdnsrr($hostname, 'ANY') || is_array(gethostbynamel($hostname))) {
            return true;
        }

        $this->setWarning(sprintf('"%s" %s', $hostname, __('commands.smokeping:generate.dns-fail')));

        return false;
    }

    /**
     * Rewrite menu entries to a format that smokeping finds acceptable
     *
     * @param string $entry The LibreNMS device hostname to rewrite
     *
     * @return string
     */
    private function buildMenuEntry($entry)
    {
        return str_replace(['.', ' '], '_', $entry);
    }

    /**
     * Select a probe to use deterministically.
     *
     * @param string $transport The transport (udp or udp6) as per the device database entry
     *
     * @return string
     */
    private function balanceProbes($transport, $probeCount)
    {
        if ($transport === 'udp') {
            if ($probeCount === $this->ip4count) {
                $this->ip4count = 0;
            }

            return sprintf('%s%s', self::IP4PROBE, $this->ip4count++);
        }

        if ($probeCount === $this->ip6count) {
            $this->ip6count = 0;
        }

        return sprintf('%s%s', self::IP6PROBE, $this->ip6count++);
    }
}
