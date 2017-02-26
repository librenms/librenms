<?php
namespace Amenadiel\JpGraph\Util;

class JpGraphException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    // custom string representation of object
    public function _toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} at " . basename($this->getFile()) . ":" . $this->getLine() . "\n" . $this->getTraceAsString() . "\n";
    }

    // custom representation of error as an image
    public function Stroke()
    {
        if (JpGraphError::GetImageFlag()) {
            $errobj = new JpGraphErrObjectImg();
            $errobj->SetTitle(JpGraphError::GetTitle());
        } else {
            $errobj = new JpGraphErrObject();
            $errobj->SetTitle(JpGraphError::GetTitle());
            $errobj->SetStrokeDest(JpGraphError::GetLogFile());
        }
        $errobj->Raise($this->getMessage());
    }

    public static function defaultHandler(\Exception $exception)
    {
        global $__jpg_OldHandler;
        if ($exception instanceof JpGraphException) {
            $exception->Stroke();
        } else {
            // Restore old handler
            if ($__jpg_OldHandler !== null) {
                set_exception_handler($__jpg_OldHandler);
            }
            throw $exception;
        }
    }
}
