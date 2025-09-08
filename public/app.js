let state = {
    patients: [],
    currentActiveVisit: null,
    selectedPatientId: null,
    activeTimerIntervalId: null,
    activeTimerStartedAt: null
};

function formatDuration(seconds) {
    const s = Math.max(0, Math.floor(seconds));
    const h = Math.floor(s / 3600).toString().padStart(2, '0');
    const m = Math.floor((s % 3600) / 60).toString().padStart(2, '0');
    const sec = (s % 60).toString().padStart(2, '0');
    return `${h}:${m}:${sec}`;
}

async function fetchJSON(url, options = {}) {
    const res = await fetch(url, Object.assign({
        headers: { 'Content-Type': 'application/json' }
    }, options));
    if (!res.ok) throw new Error(`Request failed: ${res.status}`);
    return res.json();
}

function renderPatients(list) {
    const ul = document.getElementById('patient-list');
    const q = document.getElementById('patient-search').value.trim().toLowerCase();
    ul.innerHTML = '';
    const template = document.getElementById('patient-item-template');
    list
        .filter(p => `${p.first_name} ${p.last_name}`.toLowerCase().includes(q))
        .forEach(p => {
            const li = template.content.firstElementChild.cloneNode(true);
            li.querySelector('.name').textContent = `${p.first_name} ${p.last_name}`;
            const meta = [];
            if (p.dob) meta.push(`DOB ${p.dob}`);
            if (p.gender) meta.push(p.gender);
            if (p.active_visit_id) meta.push('Active');
            li.querySelector('.meta').textContent = meta.join(' · ');
            li.querySelector('.select-btn').addEventListener('click', async () => {
                await selectPatient(p.id);
            });
            ul.appendChild(li);
        });
}

async function loadPatients() {
    const data = await fetchJSON('/api/patients.php');
    state.patients = data.patients;
    state.currentActiveVisit = data.current_active || null;
    renderPatients(state.patients);
}

function updateSummary(patient, activeVisit) {
    const nameEl = document.getElementById('patient-name');
    const piiEl = document.getElementById('patient-pii');
    nameEl.textContent = `${patient.first_name} ${patient.last_name}`;
    const parts = [];
    if (patient.dob) parts.push(`DOB: ${patient.dob}`);
    if (patient.gender) parts.push(`Gender: ${patient.gender}`);
    if (patient.phone) parts.push(`Phone: ${patient.phone}`);
    if (patient.email) parts.push(`Email: ${patient.email}`);
    if (patient.address) parts.push(`Address: ${patient.address}`);
    piiEl.textContent = parts.join(' · ');

    document.getElementById('forms').style.display = 'block';
    document.getElementById('previous-visits').style.display = 'block';

    startTimer(activeVisit ? activeVisit.started_at : null);
}

function startTimer(startedAtEpoch) {
    if (state.activeTimerIntervalId) {
        clearInterval(state.activeTimerIntervalId);
        state.activeTimerIntervalId = null;
    }
    state.activeTimerStartedAt = startedAtEpoch;
    const display = document.getElementById('timer-display');
    if (!startedAtEpoch) {
        display.textContent = '00:00:00';
        return;
    }
    const tick = () => {
        const elapsed = Math.floor(Date.now() / 1000) - startedAtEpoch;
        display.textContent = formatDuration(elapsed);
    };
    tick();
    state.activeTimerIntervalId = setInterval(tick, 1000);
}

function bindForms(activeVisitId) {
    const dxForm = document.getElementById('diagnosis-form');
    dxForm.onsubmit = async (e) => {
        e.preventDefault();
        const text = document.getElementById('diagnosis-text').value.trim();
        if (!text) return;
        await fetchJSON('/api/diagnosis_add.php', { method: 'POST', body: JSON.stringify({ visit_id: activeVisitId, text }) });
        document.getElementById('diagnosis-text').value = '';
        await loadPatientDetails(state.selectedPatientId);
    };

    const rxForm = document.getElementById('rx-form');
    rxForm.onsubmit = async (e) => {
        e.preventDefault();
        const text = document.getElementById('rx-text').value.trim();
        if (!text) return;
        await fetchJSON('/api/prescription_add.php', { method: 'POST', body: JSON.stringify({ visit_id: activeVisitId, text }) });
        document.getElementById('rx-text').value = '';
        await loadPatientDetails(state.selectedPatientId);
    };

    const hxForm = document.getElementById('hx-form');
    hxForm.onsubmit = async (e) => {
        e.preventDefault();
        const text = document.getElementById('hx-text').value.trim();
        if (!text) return;
        await fetchJSON('/api/history_add.php', { method: 'POST', body: JSON.stringify({ visit_id: activeVisitId, text }) });
        document.getElementById('hx-text').value = '';
        await loadPatientDetails(state.selectedPatientId);
    };
}

function renderItems(listElId, items) {
    const ul = document.getElementById(listElId);
    ul.innerHTML = '';
    items.forEach(i => {
        const li = document.createElement('li');
        const when = new Date(i.created_at * 1000).toLocaleString();
        li.textContent = `${i.text} — ${when}`;
        ul.appendChild(li);
    });
}

function renderPreviousVisits(visits) {
    const container = document.getElementById('previous-visits-container');
    container.innerHTML = '';
    visits.forEach(v => {
        const div = document.createElement('div');
        div.className = 'visit';
        const started = new Date(v.started_at * 1000).toLocaleString();
        const ended = v.ended_at ? new Date(v.ended_at * 1000).toLocaleString() : '—';
        const duration = v.duration_seconds != null ? formatDuration(v.duration_seconds) : '—';
        div.innerHTML = `
            <div class="header">
                <strong>Visit #${v.id}</strong>
                <span class="meta">${started} → ${ended} · ${duration}</span>
            </div>
            <div class="content">
                <div><strong>Dx</strong>: ${(v.diagnoses || []).map(x => x.text).join('; ') || '—'}</div>
                <div><strong>Rx</strong>: ${(v.prescriptions || []).map(x => x.text).join('; ') || '—'}</div>
                <div><strong>Hx</strong>: ${(v.histories || []).map(x => x.text).join('; ') || '—'}</div>
            </div>
        `;
        container.appendChild(div);
    });
}

async function loadPatientDetails(patientId) {
    const data = await fetchJSON(`/api/patient.php?id=${encodeURIComponent(patientId)}`);
    state.selectedPatientId = patientId;
    updateSummary(data.patient, data.active_visit);
    const activeVisit = data.active_visit;
    if (activeVisit && activeVisit.id) {
        bindForms(activeVisit.id);
        renderItems('diagnosis-list', activeVisit.diagnoses || []);
        renderItems('rx-list', activeVisit.prescriptions || []);
        renderItems('hx-list', activeVisit.histories || []);
    } else {
        renderItems('diagnosis-list', []);
        renderItems('rx-list', []);
        renderItems('hx-list', []);
    }
    renderPreviousVisits(data.previous_visits || []);
}

async function selectPatient(patientId) {
    // Starting a visit will automatically stop any other active visit on the server
    await fetchJSON('/api/visit_start.php', { method: 'POST', body: JSON.stringify({ patient_id: patientId }) });
    await loadPatients();
    await loadPatientDetails(patientId);
}

function attachSearch() {
    const input = document.getElementById('patient-search');
    input.addEventListener('input', () => renderPatients(state.patients));
}

window.addEventListener('DOMContentLoaded', async () => {
    attachSearch();
    await loadPatients();
});

