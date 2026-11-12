<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Rrd\CheckRrdcachedConnectivity;
use PHPUnit\Framework\Attributes\DataProvider;

final class CheckRrdcachedConnectivityTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function unreachable(): array
    {
        return [
            'unix prefix' => ['unix:/nonexistent/rrdcached.sock', '/nonexistent/rrdcached.sock does not exist'],
            'bare unix path' => ['/nonexistent/rrdcached.sock', '/nonexistent/rrdcached.sock does not exist'],
            'host and port' => ['127.0.0.1:1', 'server 127.0.0.1 on port 1'],
            'bracketed ipv6 and port' => ['[::1]:1', 'server ::1 on port 1'],
        ];
    }

    #[DataProvider('unreachable')]
    public function testUnreachable(string $address, string $message): void
    {
        LibrenmsConfig::set('rrdcached', $address);

        $result = (new CheckRrdcachedConnectivity)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString($message, $result->getMessage());
    }

    public function testConnects(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        LibrenmsConfig::set('rrdcached', (string) stream_socket_get_name($server, false));

        $this->assertSame(ValidationResult::SUCCESS, (new CheckRrdcachedConnectivity)->validate()->getStatus());
        fclose($server);
    }
}
