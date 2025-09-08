<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

$db = db();
$input = read_json_input();
$visitId = isset($input['visit_id']) ? (int)$input['visit_id'] : 0;
$text = isset($input['text']) ? trim((string)$input['text']) : '';
if ($visitId <= 0 || $text === '') {
    json_response(['error' => 'visit_id and text required'], 400);
}

$exists = $db->querySingle('SELECT COUNT(1) FROM visits WHERE id = ' . $visitId);
if ((int)$exists === 0) {
    json_response(['error' => 'visit not found'], 404);
}

$stmt = $db->prepare('INSERT INTO histories (visit_id, text, created_at) VALUES (:vid, :text, :ts)');
$stmt->bindValue(':vid', $visitId, SQLITE3_INTEGER);
$stmt->bindValue(':text', $text, SQLITE3_TEXT);
$stmt->bindValue(':ts', now(), SQLITE3_INTEGER);
$stmt->execute();

json_response(['ok' => true, 'id' => (int)$db->lastInsertRowID()]);

