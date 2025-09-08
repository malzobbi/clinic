<?php
declare(strict_types=1);

// Simple bootstrap and database initialization for the Patient Monitoring app

const DATA_DIR = __DIR__ . '/data';
const DB_FILE = DATA_DIR . '/app.db';

function db(): SQLite3 {
    static $db = null;
    if ($db instanceof SQLite3) {
        return $db;
    }

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
    $db->exec('PRAGMA foreign_keys = ON;');

    initialize_database($db);
    return $db;
}

function initialize_database(SQLite3 $db): void {
    // patients
    $db->exec(
        'CREATE TABLE IF NOT EXISTS patients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            dob TEXT,
            gender TEXT,
            phone TEXT,
            email TEXT,
            address TEXT,
            created_at INTEGER DEFAULT (strftime("%s", "now"))
        )'
    );

    // visits: one active per patient at a time (ended_at IS NULL)
    $db->exec(
        'CREATE TABLE IF NOT EXISTS visits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            patient_id INTEGER NOT NULL,
            started_at INTEGER NOT NULL,
            ended_at INTEGER,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        )'
    );

    // free-text entries attached to a visit
    $db->exec(
        'CREATE TABLE IF NOT EXISTS diagnoses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visit_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS prescriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visit_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS histories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visit_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE
        )'
    );

    seed_initial_data($db);
}

function seed_initial_data(SQLite3 $db): void {
    $count = (int)$db->querySingle('SELECT COUNT(*) FROM patients');
    if ($count > 0) {
        return;
    }

    $examplePatients = [
        ['Ava', 'Johnson', '1985-03-12', 'Female', '555-1001', 'ava.j@example.com', '123 Maple St'],
        ['Liam', 'Williams', '1978-07-24', 'Male', '555-1002', 'liam.w@example.com', '456 Oak Ave'],
        ['Noah', 'Brown', '1990-11-05', 'Male', '555-1003', 'noah.b@example.com', '789 Pine Rd'],
        ['Emma', 'Davis', '1992-02-18', 'Female', '555-1004', 'emma.d@example.com', '321 Cedar Blvd'],
        ['Olivia', 'Miller', '1988-09-30', 'Female', '555-1005', 'olivia.m@example.com', '654 Birch Ln'],
    ];

    $stmt = $db->prepare('INSERT INTO patients (first_name, last_name, dob, gender, phone, email, address) VALUES (:fn, :ln, :dob, :gender, :phone, :email, :address)');
    foreach ($examplePatients as $p) {
        $stmt->bindValue(':fn', $p[0], SQLITE3_TEXT);
        $stmt->bindValue(':ln', $p[1], SQLITE3_TEXT);
        $stmt->bindValue(':dob', $p[2], SQLITE3_TEXT);
        $stmt->bindValue(':gender', $p[3], SQLITE3_TEXT);
        $stmt->bindValue(':phone', $p[4], SQLITE3_TEXT);
        $stmt->bindValue(':email', $p[5], SQLITE3_TEXT);
        $stmt->bindValue(':address', $p[6], SQLITE3_TEXT);
        $stmt->execute();
    }
}

function json_response($data, int $code = 200): void {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function read_json_input(): array {
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function now(): int {
    return time();
}

?>

