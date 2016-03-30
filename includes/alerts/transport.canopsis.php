require_once "/usr/share/php/PhpAmqpLib/autoload.php";
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Configurations
$host = $opts["host"];
$port = 5672;
$user = $opts["user"];
$pass = $opts["passwd"];
$vhost = $opts["vhost"];
$exchange = "canopsis.events";

// Connection
$conn = new AMQPConnection($host, $port, $user, $pass, $vhost);
$ch = $conn->channel();

// Declare exchange (if not exist)
// exchange_declare($exchange, $type, $passive=false, $durable=false, $auto_delete=true, $internal=false, $nowait=false, $arguments=null, $ticket=null)
$ch->exchange_declare($exchange, 'topic', false, true, false);

// Create Canopsis event, see: https://github.com/capensis/canopsis/wiki/Event-specification
switch ($obj['severity'])
{
        case "ok": $state = 0;
        break;
        case "warning": $state = 1;
        break;
        case "critical": $state = 2;
        break;
        default: $state = 3;
}
$msg_body = array(
    "timestamp"     => time(),
    "connector"     => "librenms",
    "connector_name"    => "LibreNMS1",
    "event_type"        => "check",
    "source_type"       => "resource",
    "component"     => $obj['hostname'],
    "resource"      => $obj['device_id'],
    "state"         => $state,
    "state_type"        => 1,
    "output"        => $obj['msg'],
    "display_name"      =>"librenms_test"
);
$msg_raw = json_encode($msg_body);

// Build routing key
if ($msg_body['source_type'] == "resource")
    $msg_rk = $msg_rk . "." . $msg_body['resource'];
else
    $msg_rk = $msg_body['connector'].".".$msg_body['connector_name'].".".$msg_body['event_type'].".".$msg_body['source_type'].".".$msg_body['component'];

// Publish Event
$msg = new AMQPMessage($msg_raw, array('content_type' => 'application/json', 'delivery_mode' => 2));
$ch->basic_publish($msg, $exchange, $msg_rk);

// Close connection
$ch->close();
$conn->close();
return true;
