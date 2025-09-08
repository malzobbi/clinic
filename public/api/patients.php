<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

$db = db();

$patients = [];
$res = $db->query('SELECT 
        p.id, p.first_name, p.last_name, p.dob, p.gender, p.phone, p.email, p.address,
        (
            SELECT v.id FROM visits v 
            WHERE v.patient_id = p.id AND v.ended_at IS NULL 
            ORDER BY v.started_at DESC LIMIT 1
        ) AS active_visit_id,
        (
            SELECT v.started_at FROM visits v 
            WHERE v.patient_id = p.id AND v.ended_at IS NULL 
            ORDER BY v.started_at DESC LIMIT 1
        ) AS active_started_at
    FROM patients p
    ORDER BY p.last_name ASC, p.first_name ASC');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $patients[] = $row;
}

$currentActive = null;
$row = $db->querySingle('SELECT v.id, v.patient_id, v.started_at FROM visits v WHERE v.ended_at IS NULL ORDER BY v.started_at DESC LIMIT 1', true);
if (is_array($row) && !empty($row)) {
    $currentActive = $row;
}

json_response([
    'patients' => $patients,
    'current_active' => $currentActive,
]);

