<?php

/**
 * Topdesk.php
 *
 * LibreNMS TOPdesk alerting transport
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Rudy Broersma - CTNET B.V.
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */
/*
 * TODO
 * - saveIncident and updateIncident are almost identical besides TRUE/FALSE in toJson() and PUT/POST. Do better.
 * - Fix camelcase/snakecase/whatevercase
 */

namespace LibreNMS\Alert\Transport;

use Log;
use LibreNMS\Enum\AlertState;
use LibreNMS\Alert\Transport;
use App\Models\AlertTransport;
use LibreNMS\Util\Http;
use App\Models\AlertLog;

abstract class TicketAction {

    const TICKET_CLOSE = 0;
    const TICKET_OPEN = 1;
}

class Topdesk extends Transport {

    protected static $TOPDESK_INCIDENT_URL = "tas/api/incidents";
    protected static $TOPDESK_ASSET_URL = "tas/api/assetmgmt/assets";
    protected static $TOPDESK_ASSET_SUFFIX = "/assignments";
    protected string $name = 'TOPdesk';
    protected string $auth = "";

    public function __construct(?AlertTransport $transport = null) {
        parent::__construct($transport);
        $this->auth = base64_encode($this->config['api-user'] . ":" . $this->config['api-pass']);
    }

    public function deliverAlert(array $alert_data): bool {
        $reopen = (integer) $this->config['ticket-reopen'] ?? 24;
        $recent_uuid = $this->getRecentIncident($alert_data['device_id'], $alert_data['rule_id']);
        switch ($alert_data['state']) {
            case AlertState::ACTIVE:
                if ($this->config['ticket-reopen'] == 0 || $recent_uuid === FALSE || !preg_match('/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/', $recent_uuid)) {
                    // No previous recent incident. Create new.
                    $incident = $this->createTopdeskIncident($alert_data);
                    $incidentReturn = $this->saveIncident($incident);
                    if ($incidentReturn !== FALSE) {
                        $incident = $incidentReturn;
                        \Log::channel('single')->alert("TOPdesk: TopDesk UUID " . $incident->getID() . " (" . $incident->getNumber() . ") created for LibreNMS incident " . $alert_data['alert_id'] . " and UID " . $alert_data['uid']);
                        $this->addUuidToAlertLog($alert_data['uid'], $incident->getID());
                    }
                } else {
                    $incident = $this->getTopdeskIncident($recent_uuid);
                    $this->addAction("LibreNMS reported this issue again within " . $reopen . " hours. Reopening...", $incident, true);
                    $this->updateIncident($incident, TicketAction::TICKET_OPEN);
                    \Log::channel('single')->alert("TOPdesk: Reopening incident " . $incident->getNumber());
                }
                break;
            case AlertState::CLEAR:
                \Log::channel('single')->alert("TOPdesk: LibreNMS Alert " . $alert_data['alert_id'] . " recovered. Closing..");
                if ($recent_uuid !== FALSE) {
                    $incident = $this->getTopdeskIncident($recent_uuid);
                    \Log::channel('single')->alert("TOPdesk: TopDesk Incident " . $recent_uuid . " (" . $incident->getNumber() . ") will be closed (Libre ID: " . $alert_data['alert_id'] . ")");
                    if ($incident === FALSE) {
                        \Log::channel('single')->alert("TOPdesk: Unable to retrieve TopDesk UUID " . $recent_uuid . ". Unable to close incident...");
                    } else {
                        $this->addAction("LibreNMS reported the incident as resolved. Closing incident..", $incident, true);
                        $closed = $this->updateIncident($incident, TicketAction::TICKET_CLOSE);
                    }
                } else {
                    \Log::channel('single')->alert("TOPdesk: No matching TopDesk incident in database for LibreNMS Alert " . $alert_data['alert_id'] . ". Ignoring..");
                }
                break;
            case AlertState::ACKNOWLEDGED:
                if ($recent_uuid !== FALSE) {
                    $incident = $this->getTopdeskIncident($recent_uuid);
                    $this->addAction("LibreNMS Acknowledgement: " . $alert_data['alert_notes'], $incident, true);
                    \Log::channel('single')->alert("TOPdesk: Adding acknowledgement action to " . $incident->getNumber());
                } else {
                    \Log::channel('single')->alert("TOPdesk: Received ACK but can't find TOPdesk Incident");
                }
                break;
        }

        return true;
    }

    private function getTopdeskAssetUuid(string $needle): string|bool {
        $response = Http::client()->accept('application/json')
                ->withHeaders(['Authorization' => 'Basic ' . $this->auth])
                ->get($this->config['topdesk-url'] . self::$TOPDESK_ASSET_URL . "?searchTerm=" . $needle);

        if ($response->successful()) {
            $jsonResponse = json_decode($response->body());
            $assetUUID = $jsonResponse->dataSet[0]->id;
            if ($assetUUID == null) {
                return false;
            } else {
                return $assetUUID;
            }
        } else {
            return false;
        }
    }
    
    private function getTopdeskAssetName(string $needle): string|bool {
        $assetUUID = $this->getTopdeskAssetUuid($needle);
        if ($assetUUID == false) { return false; }
        
        $response = Http::client()->accept('application/json')
                ->withHeaders(['Authorization' => 'Basic ' . $this->auth])
                ->get($this->config['topdesk-url'] . self::$TOPDESK_ASSET_URL . "/" . $assetUUID);

        if ($response->successful()) {
            $jsonResponse = json_decode($response->body());
            $name = $jsonResponse->data->name;
            return ($name == null) ? false : $name;
        } else {
            return false;
        }
    }
    
    private function getTopdeskAssetBranch(string $needle): string|bool {
        $assetUUID = $this->getTopdeskAssetUuid($needle);
        if ($assetUUID == false) { return false; }
        
        $response = Http::client()->accept('application/json')
                ->withHeaders(['Authorization' => 'Basic ' . $this->auth])
                ->get($this->config['topdesk-url'] . self::$TOPDESK_ASSET_URL . "/" . $assetUUID . self::$TOPDESK_ASSET_SUFFIX);

        if ($response->successful()) {
            $jsonResponse = json_decode($response->body());
            $branchID = $jsonResponse->locations[0]->branch->id;
            return ($branchID == null) ? false : $branchID;
        } else {
            return false;
        }
    }

    private function createTopdeskIncident(array $alert_data): TopDeskIncident {
        $incident = new TopDeskIncident();
        $incident->setBriefDescription($alert_data['title'], FALSE);
        $incident->setRequest(str_replace("\n", "", $alert_data['msg']), FALSE);
        $incident->setCallType($this->config['calltype'], FALSE);
        $incident->setCategory($this->config['category'], FALSE);
        $incident->setStatus($this->config['entryline'], FALSE);
        $incident->setImpact($this->config['planning-impact'], FALSE);
        $incident->setUrgency($this->config['planning-urgency'], FALSE);
        $incident->setPriority($this->config['planning-priority'], FALSE);
        $incident->setOperator($this->config['ticket-operator'], FALSE);
        $incident->setOperatorGroup($this->config['ticket-operatorgroup'], FALSE);
        $incident->setSLA($this->config['planning-service'], FALSE);
        $incident->setEntryType($this->config['entrytype'], FALSE);
        $incident->setProcessingStatus($this->config['status-new'], FALSE);
        $incident->setSubcategory($this->config['subcategory'], FALSE);

        if ($this->config['ticket-assetlookup'] == "on" && $alert_data['serial'] != null) {
            $caller = $this->getTopdeskAssetBranch($alert_data['serial']);
            $name = $this->getTopdeskAssetName($alert_data['serial']);
            if ($caller != false) {
                $incident->setCaller(array(
                    "id" => null,
                    "dynamicName" => "LibreNMS",
                    "branch" => array("id" => $caller)), FALSE);
                $incident->setObject($name, FALSE);
            } else {
                $incident->setCallerLookup($this->config['ticket-defaultcaller'], FALSE);
            }
        } else {
            $incident->setCallerLookup($this->config['ticket-defaultcaller'], FALSE);
        }

        return $incident;
    }

    private function getTopdeskIncident(string $topdesk_uuid): TopDeskIncident|bool {
        $response = Http::client()->accept('application/json')->withHeaders(['Authorization' => 'Basic ' . $this->auth])->get($this->config['topdesk-url'] . self::$TOPDESK_INCIDENT_URL . "/id/" . $topdesk_uuid);

        if ($response->successful()) { // HTTP OK
            $incident = new TopDeskIncident();
            $objectData = json_decode($response->body());
            $incident->setID($objectData->id, FALSE);
            $incident->setNumber($objectData->number, FALSE);
            $incident->setBriefDescription($objectData->briefDescription, FALSE);
            $incident->setCallType($objectData->callType, FALSE);
            $incident->setCallerLookup($objectData->caller, FALSE);
            $incident->setCategory($objectData->category, FALSE);
            $incident->setClosed($objectData->closed, FALSE);
            $incident->setCompleted($objectData->completed, FALSE);
            $incident->setEntryType($objectData->entryType, FALSE);
            $incident->setObject($objectData->object, FALSE);
            $incident->setImpact($objectData->impact, FALSE);
            $incident->setOperator($objectData->operator, FALSE);
            $incident->setOperatorGroup($objectData->operatorGroup, FALSE);
            $incident->setPriority($objectData->priority, FALSE);
            $incident->setProcessingStatus($objectData->processingStatus, FALSE);
            $incident->setRequest($objectData->request, FALSE);
            $incident->setSLA($objectData->sla, FALSE);
            $incident->setStatus($objectData->status, FALSE);
            $incident->setSubcategory($objectData->subcategory, FALSE);
            $incident->setUrgency($objectData->urgency, FALSE);
            return $incident;
        } else {
            return FALSE;
        }
    }

    public static function configTemplate(): array {
        return [
            'config' => [
                [
                    'title' => 'API Username',
                    'name' => 'api-user',
                    'descr' => 'TopDesk API Username',
                    'type' => 'text',
                ],
                [
                    'title' => 'API Password',
                    'name' => 'api-pass',
                    'descr' => 'TopDesk API Password',
                    'type' => 'password',
                ],
                [
                    'title' => 'URL',
                    'name' => 'topdesk-url',
                    'descr' => 'TopDesk base URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Reopen window',
                    'name' => 'ticket-reopen',
                    'descr' => 'Timewindow in which previous ticket will be reopened if issue returns (0 = disable)',
                    'type' => 'text',
                    'default' => '24'
                ],
                [
                    'title' => 'Lookup caller in assets',
                    'name' => 'ticket-assetlookup',
                    'descr' => 'Enabled means LibreNMS will try to find the caller from the TOPdesk Asset database',
                    'type' => 'checkbox',
                    'default' => true
                ],
                [
                    'title' => 'Default caller',
                    'name' => 'ticket-defaultcaller',
                    'descr' => 'Default caller UUID if caller lookup is disabled or cant be found',
                    'type' => 'text',
                    'default' => 'f9b40703-5fb2-409d-ab62-2eec893a04c4'
                ],
                [
                    'title' => 'Operator',
                    'name' => 'ticket-operator',
                    'descr' => 'Operator for new tickets (name or UUID)',
                    'type' => 'text',
                    'default' => '1b69d3e7-287c-45ae-8e24-d15df29836c0'
                ],
                [
                    'title' => 'Operator group',
                    'name' => 'ticket-operatorgroup',
                    'descr' => 'Operator group for new tickets (name or UUID)',
                    'type' => 'text',
                    'default' => '1b69d3e7-287c-45ae-8e24-d15df29836c0'
                ],
                [
                    'title' => 'Processing status name for new tickets',
                    'name' => 'status-new',
                    'descr' => 'Status name for new tickets. Must match your TOPdesk setup',
                    'type' => 'text',
                    'default' => 'Not Started',
                ],
                [
                    'title' => 'Processing status name for reopened tickets',
                    'name' => 'status-open',
                    'descr' => 'Status name for open tickets. Must match your TOPdesk setup',
                    'type' => 'text',
                    'default' => 'Started',
                ],
                [
                    'title' => 'Processing status name for closed tickets',
                    'name' => 'status-closed',
                    'descr' => 'Status name for closed tickets. Must match your TOPdesk setup',
                    'type' => 'text',
                    'default' => 'Resolved',
                ],
                [
                    'title' => 'New ticket line',
                    'name' => 'entryline',
                    'descr' => 'Entry line name for new tickets',
                    'type' => 'text',
                    'default' => 'firstLine',
                ],
                [
                    'title' => 'Entry type name',
                    'name' => 'entrytype',
                    'descr' => 'Entry type name for new tickets',
                    'type' => 'text',
                    'default' => 'Monitoring',
                ],
                [
                    'title' => 'Entry call type',
                    'name' => 'calltype',
                    'descr' => 'Call type name for new tickets',
                    'type' => 'text',
                    'default' => 'Outage',
                ],
                [
                    'title' => 'New ticket category',
                    'name' => 'category',
                    'descr' => 'Category name for new tickets',
                    'type' => 'text',
                    'default' => 'Network',
                ],
                [
                    'title' => 'New ticket subcategory',
                    'name' => 'subcategory',
                    'descr' => 'Subcategory name for new tickets',
                    'type' => 'text',
                    'default' => 'Monitor alert',
                ],
                [
                    'title' => 'New ticket SLA UUID',
                    'name' => 'planning-service',
                    'descr' => 'SLA UUID for new tickets',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'title' => 'New ticket planning->impact',
                    'name' => 'planning-impact',
                    'descr' => 'Impact for new tickets',
                    'type' => 'text',
                    'default' => 'Individual',
                ],
                [
                    'title' => 'New ticket planning->urgency',
                    'name' => 'planning-urgency',
                    'descr' => 'Urgency for new tickets',
                    'type' => 'text',
                    'default' => 'Low',
                ],
                [
                    'title' => 'New ticket planning->priority',
                    'name' => 'planning-priority',
                    'descr' => 'Priority for new tickets',
                    'type' => 'text',
                    'default' => 'P4',
                ],
            ],
            'validation' => [
                'api-user' => 'required|string',
                'api-pass' => 'required|string',
                'topdesk-url' => 'required|string',
                'ticket-reopen' => 'required|integer',
                'status-new' => 'required|string',
                'status-open' => 'required|string',
                'status-closed' => 'required|string',
                'entrytype' => 'required|string',
                'entryline' => 'required|string',
                'category' => 'required|string',
                'subcategory' => 'required|string',
                'planning-service' => 'required|string',
                'planning-impact' => 'required|string',
                'planning-urgency' => 'required|string',
                'planning-priority' => 'required|string',
                'ticket-assetlookup' => 'required|string',
                'ticket-defaultcaller' => 'required|string',
                'ticket-operator' => 'required|string',
                'ticket-operatorgroup' => 'required|string',
                'calltype' => 'required|string',
            ],
        ];
    }

    private function addUuidToAlertLog($alertlog_id, $uuid): void {
        $alert = AlertLog::where('id', '=', $alertlog_id)->first();
        $alert->transport_note = ['topdesk_uuid' => $uuid];
        $alert->save();
    }

    private function updateIncident(TopDeskIncident $incident, int $action): bool {
        $incident->setCallerLookup(NULL);
        $incident->setRequest(NULL); // We have to empty the original request, otherwise it gets duplicated
        switch ($action):
            case TicketAction::TICKET_OPEN:
                $incident->setProcessingStatus($this->config['status-open']);
                $incident->setClosed(false);
                $incident->setCompleted(false);
                break;
            case TicketAction::TICKET_CLOSE:
                $incident->setProcessingStatus($this->config['status-closed']);
                $incident->setClosed(true);
                $incident->setCompleted(true);
                break;
        endswitch;

        $response = Http::client()->accept('application/json')->withHeaders(['Authorization' => 'Basic ' . $this->auth])->withBody($incident->_toJson(TRUE))->put($this->config['topdesk-url'] . self::$TOPDESK_INCIDENT_URL . "/id/" . $incident->getID());
        return ($response->successful() ? true : false);
    }

    private function saveIncident(TopDeskIncident $incident): bool|TopDeskIncident {
        $response = Http::client()->accept('application/json')->withHeaders(['Authorization' => 'Basic ' . $this->auth])->withBody($incident->_toJson(FALSE))->post($this->config['topdesk-url'] . self::$TOPDESK_INCIDENT_URL);
        $body = json_decode($response->body());
        $incident->setID($body->id, FALSE);
        $incident->setNumber($body->number, FALSE);
        if ($response->successful()) {
            return $incident;
        } else {
            \Log::channel('single')->alert("TOPdesk: Creating incident failed: " . $response->body());
            return false;
        }
    }

    private function addAction(string $message, TopDeskIncident $incident, bool $invisible = true): bool {
        $response = Http::client()->accept('application/json')->withHeaders(['Authorization' => 'Basic ' . $this->auth])->patch($this->config['topdesk-url'] . self::$TOPDESK_INCIDENT_URL . "/id/" . $incident->getID(), ['action' => $message, 'actionInvisibleForCaller' => $invisible]);
        return ($response->successful() ? true : false);
    }

    private function getRecentIncident(string $device_id, string $alertrule_id): bool|string {
        $reopen = (integer) $this->config['ticket-reopen'] ?? 24;
        $previous_alert = optional(AlertLog::where('device_id', '=', $device_id)
                        ->select('transport_note->topdesk_uuid AS topdesk_uuid')
                        ->where('rule_id', '=', $alertrule_id)
                        ->whereNotNull('transport_note')
                        ->where('state', '=', 1)
                        ->where('time_logged', '>', 'NOW() - INTERVAL ' . $reopen . ' HOUR')
                        ->orderBy('id', 'DESC')
                        ->first())->toArray();

        if ($previous_alert != NULL) {
            return $previous_alert["topdesk_uuid"];
        } else {
            return FALSE;
        }
    }
}

class TopDeskIncident {

    private $id, $request, $status, $entryType, $processingStatus;
    private $briefDescription, $category, $subcategory, $callType;
    private $impact, $urgency, $priority, $operator, $operatorGroup;
    private $number, $caller, $asset, $object;
    private $closed = false;
    private $completed = false;
    private $updatedProperties = array("updatedProperties");
    public $callerLookup;

    const UUID_REGEX = "/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/";

    public function __construct() {
        
    }

    // We cannot set number manually, so this is never an updatedProperty
    public function setNumber($number, bool $update = TRUE) {
        $this->number = $number;
    }

    public function getNumber() {
        return $this->number;
    }

    private function getProperties() {
        return get_object_vars($this);
    }

    public function _toJson(bool $update = TRUE) {
        $properties = $this->getProperties();
        $object = new \StdClass();
        foreach ($properties as $name => $value) {
            if ($update === TRUE) {
                // Filter names from objects. A TOPdesk object usually consists of a name and an ID,
                // but we can only use either of them. Not both. Prefer ID over name
                if (is_object($value) && property_exists($value, "id")) {
                    unset($value->name);
                }

                if (in_array($name, $this->updatedProperties)) {
                    $object->$name = $value;
                }
            } else {
                if ($value != null) {
                  $object->$name = $value;
                }
            }
        }

        if ($update === TRUE) {
            unset($object->status);
        }
        unset($object->updatedProperties);
        return json_encode($object);
    }

    public function setID($id, bool $update = TRUE) {
        $this->id = $id;
        $this->updatedProperties[] = "id";
    }

    public function setRequest($request, bool $update = TRUE) {
        $this->request = addslashes($request);
        if ($update == FALSE) {
            $this->updatedProperties[] = "request";
        }
    }

    public function setClosed(bool $closed = TRUE, bool $update = TRUE) {
        $this->closed = $closed;
        if ($update == FALSE) {
            $this->updatedProperties[] = "closed";
        }
    }

    public function setCompleted(bool $completed, bool $update = TRUE) {
        $this->completed = $completed;
        if ($update == FALSE) {
            $this->updatedProperties[] = "completed";
        }
    }

    public function setOperator($operator, bool $update = TRUE) {
        $this->operator = $this->getFieldValue($operator);
        if ($update == FALSE) {
            $this->updatedProperties[] = "operator";
        }
    }

    public function setOperatorGroup($operatorgroup, bool $update = TRUE) {
        $this->operatorGroup = $this->getFieldValue($operatorgroup);
        if ($update == FALSE) {
            $this->updatedProperties[] = "operatorGroup";
        }
    }

    public function setCategory($category, bool $update = TRUE) {
        $this->category = $this->getFieldValue($category);
        if ($update == FALSE) {
            $this->updatedProperties[] = "category";
        }
    }

    public function setSLA($sla, bool $update = TRUE) {
        $this->sla = $this->getFieldValue($sla);
        if ($update == FALSE) {
            $this->updatedProperties[] = "sla";
        }
    }

    public function setSubcategory($subcategory, bool $update = TRUE) {
        $this->subcategory = $this->getFieldValue($subcategory);
        if ($update == FALSE) {
            $this->updatedProperties[] = "subcategory";
        }
    }

    private function getFieldValue($value): array|object|null {
        if ($value instanceof \stdClass || is_array($value)) {
            return $value;
        } elseif (preg_match(self::UUID_REGEX, $value)) {
            $returnArray = array("id" => $value);
        } elseif ($value != null) { // assume value is name if it's not an UUID
            $returnArray = array("name" => $value);
        } else {
            return null;
        }
        return $returnArray;
    }

    public function setCallType($callType, bool $update = TRUE) {
        $this->callType = $this->getFieldValue($callType);
        if ($update == FALSE) {
            $this->updatedProperties[] = "callType";
        }
    }

    public function setUrgency($urgency, bool $update = TRUE) {
        $this->urgency = $this->getFieldValue($urgency);
        if ($update == FALSE) {
            $this->updatedProperties[] = "urgency";
        }
    }

    public function setImpact($impact, bool $update = TRUE) {
        $this->impact = $this->getFieldValue($impact);
        if ($update == FALSE) {
            $this->updatedProperties[] = "impact";
        }
    }

    public function setPriority($priority, bool $update = TRUE) {
        $this->priority = $this->getFieldValue($priority);
        if ($update == FALSE) {
            $this->updatedProperties[] = "priority";
        }
    }

    public function setStatus(string $status, bool $update = TRUE) {
        $this->status = $status;
        if ($update == FALSE) {
            $this->updatedProperties[] = "status";
        }
    }

    public function getID() {
        return $this->id;
    }

    public function setEntryType($entryType, bool $update = TRUE) {
        $this->entryType = $this->getFieldValue($entryType);
        if ($update == FALSE) {
            $this->updatedProperties[] = "entryType";
        }
    }

    public function setProcessingStatus($processingStatus, bool $update = TRUE) {
        $this->processingStatus = $this->getFieldValue($processingStatus);
        if ($update == FALSE) {
            $this->updatedProperties[] = "processingStatus";
        }
    }

    public function setCaller($caller, bool $update = TRUE) {
        $this->caller = $this->getFieldValue($caller);
        if ($update == FALSE) {
            $this->updatedProperties[] = "caller";
        }
    }

    public function setObject($object, bool $update = TRUE) {
        $this->object = $this->getFieldValue($object);
        if ($update == FALSE) {
            $this->updatedProperties[] = "object";
        }
    }

    public function setAsset($asset, bool $update = TRUE) {
        $this->asset = $this->getFieldValue($asset);
        if ($update == FALSE) {
            $this->updatedProperties[] = "asset";
        }
    }
    
    public function setCallerLookup($callerLookup, bool $update = TRUE) {
        $this->callerLookup = $this->getFieldValue($callerLookup);
        if ($update == FALSE) {
            $this->updatedProperties[] = "callerLookup";
        }
    }

    public function setBriefDescription($briefDescription, bool $update = TRUE) {
        $this->briefDescription = $briefDescription;
        if ($update == FALSE) {
            $this->updatedProperties[] = "briefDescription";
        }
    }
}
