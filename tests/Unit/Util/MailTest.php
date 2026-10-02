<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Mail;
use PHPUnit\Framework\Attributes\DataProvider;

final class MailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('email_user', 'LibreNMS');
    }

    /**
     * @param  array<string, string>  $expected
     */
    #[DataProvider('emailProvider')]
    public function testParseEmails(string $input, array $expected): void
    {
        $this->assertSame($expected, Mail::parseEmails($input));
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function emailProvider(): array
    {
        return [
            'plain' => [
                'user@example.com',
                ['user@example.com' => 'LibreNMS'],
            ],
            'double quoted name' => [
                '"John Doe" <john@example.com>',
                ['john@example.com' => 'John Doe'],
            ],
            'single quoted name' => [
                "'John Doe' <john@example.com>",
                ['john@example.com' => 'John Doe'],
            ],
            'unquoted name' => [
                'John<john@example.com>',
                ['john@example.com' => 'John'],
            ],
            'comma and semicolon separated' => [
                'a@example.com, "B" <b@example.com>;c@example.com',
                [
                    'a@example.com' => 'LibreNMS',
                    'b@example.com' => 'B',
                    'c@example.com' => 'LibreNMS',
                ],
            ],
            'plain after named' => [
                '"B" <b@example.com>, c@example.com',
                [
                    'b@example.com' => 'B',
                    'c@example.com' => 'LibreNMS',
                ],
            ],
            'invalid addresses are skipped' => [
                'not-an-email, "Bad" <bad@>, good@example.com',
                ['good@example.com' => 'LibreNMS'],
            ],
            'unquoted name with space' => [
                'John Doe <john@example.com>',
                ['john@example.com' => 'John Doe'],
            ],
            'comma in quoted name' => [
                '"Doe, John" <john@example.com>, a@example.com',
                [
                    'john@example.com' => 'Doe, John',
                    'a@example.com' => 'LibreNMS',
                ],
            ],
            'semicolon in quoted name' => [
                'a@example.com; "Doe; John" <john@example.com>',
                [
                    'a@example.com' => 'LibreNMS',
                    'john@example.com' => 'Doe; John',
                ],
            ],
            'apostrophes in names' => [
                "O'Brien <ob@example.com>, D'Arcy <da@example.com>",
                [
                    'ob@example.com' => "O'Brien",
                    'da@example.com' => "D'Arcy",
                ],
            ],
            'trailing separator' => [
                'a@example.com,',
                ['a@example.com' => 'LibreNMS'],
            ],
            'empty' => [
                '',
                [],
            ],
        ];
    }
}
