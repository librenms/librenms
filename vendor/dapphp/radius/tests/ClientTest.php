<?php

use Dapphp\Radius\Radius;
use Dapphp\Radius\EAPPacket;
use Dapphp\Radius\MsChapV2Packet;
use Dapphp\Radius\VendorId;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testAttributes()
    {
        $client = new Radius();

        // string value test
        $test   = 'this is a test';

        $client->setAttribute(80, $test);
        $attr   = $client->getAttributesToSend(80);
        $this->assertEquals($test, $attr);

        $client->removeAttribute(80);
        $attr   = $client->getAttributesToSend(80);
        $this->assertEquals(null, $attr);

        // integer value test
        $nasPort = 32768;

        $client->setAttribute(5, $nasPort);
        $attr   = $client->getAttributesToSend(5);
        $this->assertEquals($nasPort, $attr);

        $client->removeAttribute(5);
        $attr   = $client->getAttributesToSend(5);
        $this->assertEquals(null, $attr);
    }

    public function testGetAttributes()
    {
        $client   = new Radius();
        $username = 'LinusX2@arpa.net';
        $nasIp    = '192.168.88.1';
        $nasPort  = 64000;

        $expected = ''; // manually constructed hex string
        $expected .= chr(1); // username
        $expected .= chr(2 + strlen($username)); // length
        $expected .= $username;

        $expected .= chr(4); // nas ip
        $expected .= chr(6);
        $expected .= pack('N', ip2long($nasIp));

        $expected .= chr(5); // nas port
        $expected .= chr(6);
        $expected .= pack('N', $nasPort);


        $client->setUsername($username)
               ->setNasIPAddress($nasIp)
               ->setNasPort($nasPort);

        $actual = implode('', $client->getAttributesToSend());

        $this->assertEquals($expected, $actual);
        $this->assertEquals($username, $client->getAttributesToSend(1));
        $this->assertEquals($nasIp, $client->getAttributesToSend(4));
        $this->assertEquals($nasPort, $client->getAttributesToSend(5));
    }

    public function testEncryptedPassword()
    {
        $pass   = 'arctangent';
        $secret = 'xyzzy5461';
        $requestAuthenticator = "\x0f\x40\x3f\x94\x73\x97\x80\x57\xbd\x83\xd5\xcb\x98\xf4\x22\x7a";
        $client = new Radius();

        $expected  = "\x0d\xbe\x70\x8d\x93\xd4\x13\xce\x31\x96\xe4\x3f\x78\x2a\x0a\xee";
        $encrypted = $client->getEncryptedPassword($pass, $secret, $requestAuthenticator);

        $this->assertEquals($expected, $encrypted);
    }

    public function testEncryptedPassword2()
    {
        $pass   = 'm1cr0$ofT_W1nDoWz*';
        $secret = '%iM8WD3(9bSh4jXNyOH%4W6RE1s4bfQ#0h*n^lOz';
        $requestAuthenticator = "\x7d\x22\x56\x6c\x9d\x2d\x50\x26\x88\xc5\xb3\xf9\x33\x77\x14\x55";
        $client = new Radius();

        $expected = "\x44\xe0\xac\xdc\xed\x56\x39\x67\xb1\x41\x90\xef\x3e\x10\xca\x2c\xb5\xb0\x5f\xf6\x6c\x31\x87\xf0\x2a\x92\xcb\x65\xeb\x97\x31\x1f";
        $encrypted = $client->getEncryptedPassword($pass, $secret, $requestAuthenticator);

        $this->assertEquals($expected, $encrypted);
    }

    public function testAuthenticationPacket()
    {
        $user    = 'nemo';
        $pass    = 'arctangent';
        $secret  = 'xyzzy5461';
        $nas     = '192.168.1.16';
        $nasPort = 3;

        $client  = new Radius();

        $client->setRequestAuthenticator("\x0f\x40\x3f\x94\x73\x97\x80\x57\xbd\x83\xd5\xcb\x98\xf4\x22\x7a");

        $client->setPacketType(Radius::TYPE_ACCESS_REQUEST)
               ->setSecret($secret)
               ->setUsername($user)
               ->setPassword($pass)
               ->setNasIPAddress($nas)
               ->setNasPort($nasPort);

        $packet   = $client->generateRadiusPacket();
        $pwEnc    = "\x0d\xbe\x70\x8d\x93\xd4\x13\xce\x31\x96\xe4\x3f\x78\x2a\x0a\xee";
        $expected = "\x01\x00\x00\x38\x0f\x40\x3f\x94\x73\x97\x80\x57\xbd\x83"
                  . "\xd5\xcb\x98\xf4\x22\x7a\x01\x06\x6e\x65\x6d\x6f\x02\x12"
                  . $pwEnc
                  . "\x04\x06\xc0\xa8\x01\x10\x05\x06\x00\x00\x00\x03";

        $this->assertEquals($expected, $packet);
    }

    public function testFramedAuthPacket()
    {
        $user    = 'flopsy';
        $pass    = 'arctangent';
        $reqAuth = "\x2a\xee\x86\xf0\x8d\x0d\x55\x96\x9c\xa5\x97\x8e\x0d\x33\x67\xa2";
        $nas     = '192.168.1.16';
        $nasPort = 20;

        $expected = "\x01\x01\x00\x47\x2a\xee\x86\xf0\x8d\x0d\x55\x96\x9c\xa5"
                   ."\x97\x8e\x0d\x33\x67\xa2\x01\x08\x66\x6c\x6f\x70\x73\x79"
                   ."\x03\x13\x16\xe9\x75\x57\xc3\x16\x18\x58\x95\xf2\x93\xff"
                   ."\x63\x44\x07\x72\x75\x04\x06\xc0\xa8\x01\x10\x05\x06\x00"
                   ."\x00\x00\x14\x06\x06\x00\x00\x00\x02\x07\x06\x00\x00\x00\x01";

        $client = new Radius();
        $client->getNextIdentifier(); // increment to 1 for test
        $client->setChapId(22);
        $client->setRequestAuthenticator($reqAuth)
               ->setPacketType(Radius::TYPE_ACCESS_REQUEST)
               ->setUsername($user)
               ->setChapPassword($pass)
               ->setNasIPAddress($nas)
               ->setNasPort($nasPort)
               ->setAttribute(6, 2)  // service type (6) = framed (2)
               ->setAttribute(7, 1); // framed protocol (7) = ppp (1)

        $packet = $client->generateRadiusPacket();

        $this->assertEquals($expected, $packet);
    }

    public function testHmacMd5()
    {
        $str  = hex2bin('01870082093e4ad125399f8ac4ba6b00ab69a04001066e656d6f04067f0000010506000000145012000000000000000000000000000000001a10000001370b0a740c7921e45e91391a3a00000137013400010000000000000000000000000000000000000000000000004521bd46aebfd2ab3ec21dd6e6bbfa2e4ff325eab720fe37');
        $hash = hash_hmac('md5', $str, 'xyzzy5461', true);

        $expected = '48a3704ac91e8191497a1f3f213eb338';
        $actual   = bin2hex($hash);

        $this->assertEquals($expected, $actual);
    }

    public function testMsChapV1Packet()
    {
        $reqId   = 135;
        $user    = 'nemo';
        $pass    = 'arctangent123$';
        $secret  = 'xyzzy5461';
        $reqAuth = "\x09\x3e\x4a\xd1\x25\x39\x9f\x8a\xc4\xba\x6b\x00\xab\x69\xa0\x40";
        $nas     = '127.0.0.1';
        $nasPort = 20;
        $challenge = "\x74\x0c\x79\x21\xe4\x5e\x91\x39";

        $client = new Radius();
        $client->setPacketType(Radius::TYPE_ACCESS_REQUEST)
               ->setNextIdentifier($reqId)
               ->setRequestAuthenticator($reqAuth)
               ->setSecret($secret)
               ->setUsername($user)
               ->setNasIPAddress($nas)
               ->setNasPort($nasPort)
               ->setAttribute(80, str_repeat("\x00", 16))
               ->setMsChapPassword($pass, $challenge);

        $packet = $client->generateRadiusPacket();

        $packet   = bin2hex($packet);
        $expected = "01870082093e4ad125399f8ac4ba6b00ab69a04001066e656d6f04067f000001050600000014501248a3704ac91e8191497a1f3f213eb3381a10000001370b0a740c7921e45e91391a3a00000137013400010000000000000000000000000000000000000000000000004521bd46aebfd2ab3ec21dd6e6bbfa2e4ff325eab720fe37";

        $this->assertEquals($expected, $packet);
    }

    public function testEapPacketBasic()
    {
        $p       = new EAPPacket();
        $p->code = EAPPacket::CODE_REQUEST;
        $p->id   = 111;
        $p->type = EAPPacket::TYPE_IDENTITY;
        $p->data = 'here is some data';

        $expected = "016f0016016865726520697320736f6d652064617461";

        $this->assertEquals($expected, bin2hex($p->__toString()));

        $parsed = EAPPacket::fromString($p->__toString());

        $this->assertEquals(EAPPacket::CODE_REQUEST, $parsed->code);
        $this->assertEquals(111, $parsed->id);
        $this->assertEquals(EAPPacket::TYPE_IDENTITY, $parsed->type);
        $this->assertEquals($p->data, $parsed->data);

        $p2 = new EAPPacket();
        $p2->code = EAPPacket::CODE_RESPONSE;
        $p2->id   = 128;
        $p2->type = EAPPacket::TYPE_NOTIFICATION;
        $p2->data = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x99\x98\x97\x96\x95\x94\x93\x92\x91\x90";

        $p3 = EAPPacket::fromString($p2->__toString());

        $this->assertEquals(EAPPacket::CODE_RESPONSE, $p3->code);
        $this->assertEquals(128, $p3->id);
        $this->assertEquals(2, $p3->type);
        $this->assertEquals("\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x99\x98\x97\x96\x95\x94\x93\x92\x91\x90", $p3->data);
    }

    public function testEapMsChapV2()
    {
        $server = getenv('RADIUS_SERVER_ADDR');
        $user   = getenv('RADIUS_USER');
        $pass   = getenv('RADIUS_PASS');
        $secret = getenv('RADIUS_SECRET');

        if (!$server) {
            $this->markTestSkipped('RADIUS_SERVER_ADDR environment variable not set');
        } elseif (!$user) {
            $this->markTestSkipped('RADIUS_USER environment variable not set');
        } elseif (!$pass) {
            $this->markTestSkipped('RADIUS_PASS environment variable not set');
        } elseif (!$secret) {
            $this->markTestSkipped('RADIUS_SECRET environment variable not set');
        }

        $client = new Radius();
        $client->setServer($server)
               ->setSecret($secret);

        $success = $client->accessRequestEapMsChapV2($user, $pass);

        $this->assertTrue($success);
    }
}
