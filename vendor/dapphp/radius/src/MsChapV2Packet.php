<?php

namespace Dapphp\Radius;


/**
 * Class for MSCHAP v2 packets encapsulated in EAP packets
 *
 */
class MsChapV2Packet
{
    const OPCODE_CHALLENGE  = 1;
    const OPCODE_RESPONSE   = 2;
    const OPCODE_SUCCESS    = 3;
    const OPCODE_FAILURE    = 4;
    const OPCODE_CHANGEPASS = 7;

    public $opcode;
    public $msChapId;
    public $msLength;
    public $valueSize;
    public $challenge;
    public $response;
    public $name;

    /**
     * Parse an MSCHAP v2 packet into a structure
     *
     * @param string $packet Raw MSCHAP v2 packet string
     * @return \Dapphp\Radius\MsChapV2Packet The parsed packet structure
     */
    public static function fromString($packet)
    {
        if (strlen($packet) < 5) {
            return false;
        }

        $p = new self();
        $p->opcode    = ord($packet[0]);
        $p->msChapId  = ord($packet[1]);
        $temp         = unpack('n', substr($packet, 2, 2));
        $p->msLength  = array_shift($temp);
        $p->valueSize = ord($packet[4]);

        switch($p->opcode) {
            case 1: // challenge
                $p->challenge = substr($packet, 5, 16);
                $p->name      = substr($packet, -($p->msLength + 5 - $p->valueSize - 10));
                break;

            case 2: // response
                break;

            case 3: // success
                break;

            case 4: // failure
                $p->response = substr($packet, 4);
                break;
        }

        return $p;
    }

    /**
     * Convert a packet structure to a byte string for sending over the wire
     * @return string  MSCHAP v2 packet string
     */
    public function __toString()
    {
        $packet = pack('C', $this->opcode) .
                  chr($this->msChapId) .
                  "\x00\x00"; // temp length

        switch($this->opcode) {
            case self::OPCODE_CHALLENGE: // challenge
                $packet .= chr(16);
                $packet .= $this->challenge;
                $packet .= $this->name;
                break;

            case self::OPCODE_RESPONSE: // response
                $packet .= chr(49);
                $packet .= $this->challenge;
                $packet .= str_repeat("\x00", 8); // reserved
                $packet .= $this->response;
                $packet .= chr(0); // reserved flags
                $packet .= $this->name;
                break;

            case self::OPCODE_SUCCESS: // success
                return chr(3);
        }

        $length = pack('n', strlen($packet));
        $packet[2] = $length[0];
        $packet[3] = $length[1];

        return $packet;
    }
}
