<?php
namespace Amenadiel\JpGraph\Util;

class JpGraphExceptionL extends JpGraphException
{
    // Redefine the exception so message isn't optional
    public function __construct($errcode, $a1 = null, $a2 = null, $a3 = null, $a4 = null, $a5 = null)
    {
        // make sure everything is assigned properly
        $errtxt = new ErrMsgText();
        JpGraphError::SetTitle('JpGraph Error: ' . $errcode);
        parent::__construct($errtxt->Get($errcode, $a1, $a2, $a3, $a4, $a5), 0);
    }
}
