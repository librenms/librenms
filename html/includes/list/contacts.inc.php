<?php

if (!is_admin() && !is_read()) {
    return [];
}

$query = '';
$params = [];

if (!empty($_REQUEST['search'])) {
    $query .= ' WHERE `email` LIKE ? OR `contact_name` LIKE ?';
    $search = '%' . mres($_REQUEST['search']) . '%';
    $params[] = $search;
    $params[] = $search;
}

$total = dbFetchCell("SELECT COUNT(*) FROM `transport_email` $query", $params);
$more = false;

if (!empty($_REQUEST['limit'])) {
    $limit = (int) $_REQUEST['limit'];
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
    $offset = ($page - 1) * $limit;

    $query .= " LIMIT $offset, $limit";
} else {
    $offset = 0;
}

$sql = "SELECT `transport_id` AS `id`, `contact_name` AS `text` FROM `transport_email` $query";
$contacts = dbFetchRows($sql, $params);
$more = ($offset + count($contacts)) < $total;
$contacts = array_map(function ($contact) {
    $contact['id'] = 'e' . $contact['id'];
    return $contact;
}, $contacts);

$data = [['text' => 'Email', 'children' => $contacts]];
return [$data, $more];
