<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

$db = db();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    json_response(['error' => 'Missing id'], 400);
}

$patient = $db->querySingle('SELECT id, first_name, last_name, dob, gender, phone, email, address FROM patients WHERE id = ' . $id, true);
if (!$patient) {
    json_response(['error' => 'Not found'], 404);
}

// Active visit (if any)
$activeVisit = $db->querySingle('SELECT id, patient_id, started_at, ended_at FROM visits WHERE patient_id = ' . $id . ' AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1', true);
if ($activeVisit) {
    $activeVisit['diagnoses'] = fetch_items($db, 'diagnoses', (int)$activeVisit['id']);
    $activeVisit['prescriptions'] = fetch_items($db, 'prescriptions', (int)$activeVisit['id']);
    $activeVisit['histories'] = fetch_items($db, 'histories', (int)$activeVisit['id']);
}

// Previous visits (ended)
$previous = [];
$res = $db->query('SELECT id, patient_id, started_at, ended_at, (ended_at - started_at) AS duration_seconds FROM visits WHERE patient_id = ' . $id . ' AND ended_at IS NOT NULL ORDER BY started_at DESC LIMIT 20');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $row['diagnoses'] = fetch_items($db, 'diagnoses', (int)$row['id']);
    $row['prescriptions'] = fetch_items($db, 'prescriptions', (int)$row['id']);
    $row['histories'] = fetch_items($db, 'histories', (int)$row['id']);
    $previous[] = $row;
}

json_response([
    'patient' => $patient,
    'active_visit' => $activeVisit,
    'previous_visits' => $previous,
]);

function fetch_items(SQLite3 $db, string $table, int $visitId): array {
    $items = [];
    $stmt = $db->prepare("SELECT id, text, created_at FROM $table WHERE visit_id = :vid ORDER BY id DESC");
    $stmt->bindValue(':vid', $visitId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

