<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
// Ensure database is initialized
db();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patient Monitor</title>
    <link rel="stylesheet" href="/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script defer src="/app.js"></script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Patients</h1>
                <input id="patient-search" class="search" type="search" placeholder="Search patients..." />
            </div>
            <ul id="patient-list" class="patient-list"></ul>
        </aside>
        <main class="main">
            <div id="selected-summary" class="selected-summary">
                <div>
                    <h2 id="patient-name">Select a patient</h2>
                    <div id="patient-pii" class="pii"></div>
                </div>
                <div class="timer">
                    <div class="timer-label">Active timer</div>
                    <div id="timer-display" class="timer-display">00:00:00</div>
                </div>
            </div>

            <section class="forms" id="forms" style="display:none;">
                <div class="card">
                    <h3>Add Diagnosis</h3>
                    <form id="diagnosis-form">
                        <input type="text" id="diagnosis-text" placeholder="e.g., Acute bronchitis" required />
                        <button type="submit">Add</button>
                    </form>
                    <ul id="diagnosis-list" class="items"></ul>
                </div>
                <div class="card">
                    <h3>Add Rx</h3>
                    <form id="rx-form">
                        <input type="text" id="rx-text" placeholder="e.g., Amoxicillin 500mg TID x7d" required />
                        <button type="submit">Add</button>
                    </form>
                    <ul id="rx-list" class="items"></ul>
                </div>
                <div class="card">
                    <h3>Add Hx</h3>
                    <form id="hx-form">
                        <input type="text" id="hx-text" placeholder="e.g., PMH: asthma; NKDA" required />
                        <button type="submit">Add</button>
                    </form>
                    <ul id="hx-list" class="items"></ul>
                </div>
            </section>

            <section class="previous-visits" id="previous-visits" style="display:none;">
                <h3>Previous Visits</h3>
                <div id="previous-visits-container" class="visits"></div>
            </section>
        </main>
    </div>
    <template id="patient-item-template">
        <li class="patient-item">
            <div class="info">
                <div class="name"></div>
                <div class="meta"></div>
            </div>
            <button class="select-btn">See Patient</button>
        </li>
    </template>
</body>
</html>

