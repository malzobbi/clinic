<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

$db = db();
$input = read_json_input();
$patientId = isset($input['patient_id']) ? (int)$input['patient_id'] : 0;
if ($patientId <= 0) {
    json_response(['error' => 'patient_id required'], 400);
}

// Stop any currently active visit globally
$now = now();
$db->exec('UPDATE visits SET ended_at = ' . $now . ' WHERE ended_at IS NULL');

// If the patient already has an active visit (rare after previous line), keep it; otherwise start a new one
$active = $db->querySingle('SELECT id, patient_id, started_at, ended_at FROM visits WHERE patient_id = ' . $patientId . ' AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1', true);
if (!$active) {
    $stmt = $db->prepare('INSERT INTO visits (patient_id, started_at) VALUES (:pid, :start)');
    $stmt->bindValue(':pid', $patientId, SQLITE3_INTEGER);
    $stmt->bindValue(':start', $now, SQLITE3_INTEGER);
    $stmt->execute();
    $visitId = (int)$db->lastInsertRowID();
    $active = $db->querySingle('SELECT id, patient_id, started_at, ended_at FROM visits WHERE id = ' . $visitId, true);
}

json_response(['active_visit' => $active]);

