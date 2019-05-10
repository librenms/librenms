# Discover & poll one or multiple devices without waiting for the cron.
# Argument supports wildcards.
#
# Some variables
id=`whoami`
# Fail if the user isn't "librenms"
if [[ $(whoami) != "librenms" ]]; then
    echo "Warning: script must be run as librenms or it would mess up file permissions!"
    exit 1
fi
/opt/librenms/discovery.php -h $1 ; ./asd ; /opt/librenms/poller.php -h $1
exit 0;
