<?php

namespace LibreNMS\Exceptions;

/**
 * A file to copy data from can't be read, librrd checks them locally even when using rrdcached
 */
class RrdSourceException extends RrdException
{
}
