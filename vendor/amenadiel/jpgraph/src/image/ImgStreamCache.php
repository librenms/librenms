<?php
namespace Amenadiel\JpGraph\Image;

use Amenadiel\JpGraph\Util;

//=======================================================================
// CLASS ImgStreamCache
// Description: Handle caching of graphs to files. All image output goes
//              through this class
//=======================================================================
class ImgStreamCache
{
    private $cache_dir, $timeout = 0; // Infinite timeout
    //---------------
    // CONSTRUCTOR
    public function __construct($aCacheDir = CACHE_DIR)
    {
        $this->cache_dir = $aCacheDir;
    }

    //---------------
    // PUBLIC METHODS

    // Specify a timeout (in minutes) for the file. If the file is older then the
    // timeout value it will be overwritten with a newer version.
    // If timeout is set to 0 this is the same as infinite large timeout and if
    // timeout is set to -1 this is the same as infinite small timeout
    public function SetTimeout($aTimeout)
    {
        $this->timeout = $aTimeout;
    }

    // Output image to browser and also write it to the cache
    public function PutAndStream($aImage, $aCacheFileName, $aInline, $aStrokeFileName)
    {

        // Check if we should always stroke the image to a file
        if (_FORCE_IMGTOFILE) {
            $aStrokeFileName = _FORCE_IMGDIR . GenImgName();
        }

        if ($aStrokeFileName != '') {

            if ($aStrokeFileName == 'auto') {
                $aStrokeFileName = GenImgName();
            }

            if (file_exists($aStrokeFileName)) {

                // Wait for lock (to make sure no readers are trying to access the image)
                $fd = fopen($aStrokeFileName, 'w');
                $lock = flock($fd, LOCK_EX);

                // Since the image write routines only accepts a filename which must not
                // exist we need to delete the old file first
                if (!@unlink($aStrokeFileName)) {
                    $lock = flock($fd, LOCK_UN);
                    Util\JpGraphError::RaiseL(25111, $aStrokeFileName);
                    //(" Can't delete cached image $aStrokeFileName. Permission problem?");
                }
                $aImage->Stream($aStrokeFileName);
                $lock = flock($fd, LOCK_UN);
                fclose($fd);

            } else {
                $aImage->Stream($aStrokeFileName);
            }

            return;
        }

        if ($aCacheFileName != '' && USE_CACHE) {

            $aCacheFileName = $this->cache_dir . $aCacheFileName;
            if (file_exists($aCacheFileName)) {
                if (!$aInline) {
                    // If we are generating image off-line (just writing to the cache)
                    // and the file exists and is still valid (no timeout)
                    // then do nothing, just return.
                    $diff = time() - filemtime($aCacheFileName);
                    if ($diff < 0) {
                        Util\JpGraphError::RaiseL(25112, $aCacheFileName);
                        //(" Cached imagefile ($aCacheFileName) has file date in the future!!");
                    }
                    if ($this->timeout > 0 && ($diff <= $this->timeout * 60)) {
                        return;
                    }

                }

                // Wait for lock (to make sure no readers are trying to access the image)
                $fd = fopen($aCacheFileName, 'w');
                $lock = flock($fd, LOCK_EX);

                if (!@unlink($aCacheFileName)) {
                    $lock = flock($fd, LOCK_UN);
                    Util\JpGraphError::RaiseL(25113, $aStrokeFileName);
                    //(" Can't delete cached image $aStrokeFileName. Permission problem?");
                }
                $aImage->Stream($aCacheFileName);
                $lock = flock($fd, LOCK_UN);
                fclose($fd);

            } else {
                $this->MakeDirs(dirname($aCacheFileName));
                if (!is_writeable(dirname($aCacheFileName))) {
                    Util\JpGraphError::RaiseL(25114, $aCacheFileName);
                    //('PHP has not enough permissions to write to the cache file '.$aCacheFileName.'. Please make sure that the user running PHP has write permission for this file if you wan to use the cache system with JpGraph.');
                }
                $aImage->Stream($aCacheFileName);
            }

            $res = true;
            // Set group to specified
            if (CACHE_FILE_GROUP != '') {
                $res = @chgrp($aCacheFileName, CACHE_FILE_GROUP);
            }
            if (CACHE_FILE_MOD != '') {
                $res = @chmod($aCacheFileName, CACHE_FILE_MOD);
            }
            if (!$res) {
                Util\JpGraphError::RaiseL(25115, $aStrokeFileName);
                //(" Can't set permission for cached image $aStrokeFileName. Permission problem?");
            }

            $aImage->Destroy();
            if ($aInline) {
                if ($fh = @fopen($aCacheFileName, "rb")) {
                    $aImage->Headers();
                    fpassthru($fh);
                    return;
                } else {
                    Util\JpGraphError::RaiseL(25116, $aFile); //(" Cant open file from cache [$aFile]");
                }
            }
        } elseif ($aInline) {
            $aImage->Headers();
            $aImage->Stream();
            return;
        }
    }

    public function IsValid($aCacheFileName)
    {
        $aCacheFileName = $this->cache_dir . $aCacheFileName;
        if (USE_CACHE && file_exists($aCacheFileName)) {
            $diff = time() - filemtime($aCacheFileName);
            if ($this->timeout > 0 && ($diff > $this->timeout * 60)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function StreamImgFile($aImage, $aCacheFileName)
    {
        $aCacheFileName = $this->cache_dir . $aCacheFileName;
        if ($fh = @fopen($aCacheFileName, 'rb')) {
            $lock = flock($fh, LOCK_SH);
            $aImage->Headers();
            fpassthru($fh);
            $lock = flock($fh, LOCK_UN);
            fclose($fh);
            return true;
        } else {
            Util\JpGraphError::RaiseL(25117, $aCacheFileName); //(" Can't open cached image \"$aCacheFileName\" for reading.");
        }
    }

    // Check if a given image is in cache and in that case
    // pass it directly on to web browser. Return false if the
    // image file doesn't exist or exists but is to old
    public function GetAndStream($aImage, $aCacheFileName)
    {
        if ($this->Isvalid($aCacheFileName)) {
            $this->StreamImgFile($aImage, $aCacheFileName);
        } else {
            return false;
        }
    }

    //---------------
    // PRIVATE METHODS
    // Create all necessary directories in a path
    public function MakeDirs($aFile)
    {
        $dirs = array();
        // In order to better work when open_basedir is enabled
        // we do not create directories in the root path
        while ($aFile != '/' && !(file_exists($aFile))) {
            $dirs[] = $aFile . '/';
            $aFile = dirname($aFile);
        }
        for ($i = sizeof($dirs) - 1; $i >= 0; $i--) {
            if (!@mkdir($dirs[$i], 0777)) {
                Util\JpGraphError::RaiseL(25118, $aFile); //(" Can't create directory $aFile. Make sure PHP has write permission to this directory.");
            }
            // We also specify mode here after we have changed group.
            // This is necessary if Apache user doesn't belong the
            // default group and hence can't specify group permission
            // in the previous mkdir() call
            if (CACHE_FILE_GROUP != "") {
                $res = true;
                $res = @chgrp($dirs[$i], CACHE_FILE_GROUP);
                $res = @chmod($dirs[$i], 0777);
                if (!$res) {
                    Util\JpGraphError::RaiseL(25119, $aFile); //(" Can't set permissions for $aFile. Permission problems?");
                }
            }
        }
        return true;
    }
} // CLASS Cache
