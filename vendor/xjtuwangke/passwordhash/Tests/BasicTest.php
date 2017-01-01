<?php

// namespace Hautelook\Phpass\Tests;

use Hautelook\Phpass\PasswordHash;

/**
 * 
 */
class BasicTest extends \PHPUnit_Framework_TestCase
{
	const PORTABLE_HASH = '$P$9IQRaTwmfeRo7ud9Fh4E2PdI0S3r.L0';
 	public function testCorrectHash()
    {
    	$hasher  = new PasswordHash(8,false);
    	$correct = 'test12345';
		$hash 	 = $hasher->HashPassword($correct);
		
		$this->assertTrue($hasher->CheckPassword($correct, $hash));
    }

    public function testIncorrectHash()
    {
    	$hasher  = new PasswordHash(8,false);
    	$correct = 'test12345';
		$hash 	 = $hasher->HashPassword($correct);
    	$wrong   = 'test12346';

		$this->assertFalse($hasher->CheckPassword($wrong, $hash));
    }

    public function testWeakHashes()
    {
    	$hasher  = new PasswordHash(8, true);
    	$correct = 'test12345';
		$hash 	 = $hasher->HashPassword($correct);
    	$wrong   = 'test12346';

    	$this->assertTrue($hasher->CheckPassword($correct, $hash));
		$this->assertFalse($hasher->CheckPassword($wrong, $hash));
    }

    public function testPortableHashes()
    {
    	$hasher  = new PasswordHash(8, true);
    	$correct = 'test12345';
    	$wrong   = 'test12346';

    	$this->assertTrue($hasher->CheckPassword($correct, self::PORTABLE_HASH));
		$this->assertFalse($hasher->CheckPassword($wrong, self::PORTABLE_HASH));
    }
}