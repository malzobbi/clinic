Patient Monitor (PHP + SQLite)
================================

A minimal PHP app to monitor patient info, manage visits, add diagnoses, prescriptions (Rx), histories (Hx), list previous visits, and track a per-visit timer that starts when selecting a patient and auto-stops when switching to the next patient.

Requirements
-----------
- PHP 8+ with SQLite3 extension

Run (dev)
---------
```bash
php -S 0.0.0.0:8080 -t public
```

Then open `http://localhost:8080/`.

Structure
---------
- `bootstrap.php`: DB connection, schema, seed, helpers
- `public/index.php`: UI
- `public/app.js`: Client logic, calls APIs
- `public/style.css`: Styling
- `public/api/*.php`: JSON endpoints
- `data/app.db`: SQLite database file

Notes
-----
- Selecting a patient starts a new visit and stops any other active visit automatically.
- Add Diagnosis, Rx, and Hx to the current active visit via forms.
- Previous visits list shows duration and summary of entries.

