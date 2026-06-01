(function () {
    'use strict';

    // ── Lookup widget state ──────────────────────────────────────
    // Keyed by field id, value is the loaded options array (or null if not yet loaded)
    const _lookupOptions = {
        pi_mother_tongue_id:  null,
        pi_religion_id:       null,
        ac_indigenous_group_id: null
    };

    const _lookupEndpoints = {
        pi_mother_tongue_id:    'mother-tongue',
        pi_religion_id:         'religions',
        ac_indigenous_group_id: 'indigenous-groups'
    };

    const _lookupLabels = {
        pi_mother_tongue_id:    'Mother Tongue',
        pi_religion_id:         'Religion',
        ac_indigenous_group_id: 'Indigenous Group'
    };

    // Resolve the search.php URL relative to the current page (verify/index.php)
    function searchUrl(endpoint) {
        return '../../search/search.php?type=' + endpoint + '&limit=10000';
    }

    async function fetchOptions(endpoint) {
        try {
            const r = await fetch(searchUrl(endpoint));
            const d = await r.json();
            if (d.success && Array.isArray(d.data)) return d.data;
        } catch (e) {
            console.error('Lookup fetch failed for', endpoint, e);
        }
        return [];
    }

    function buildSearchWidget(fieldId, options) {
        const selectEl = document.getElementById(fieldId);
        if (!selectEl || selectEl.tagName !== 'SELECT') return; // already replaced or missing

        const label = _lookupLabels[fieldId];

        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select-wrapper';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Type to search ' + label + '...';
        searchInput.className = 'form-control lookup-search-input';
        searchInput.id = fieldId + '_search';
        searchInput.autocomplete = 'off';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = fieldId;
        hiddenInput.id = fieldId;
        hiddenInput.value = '';
        hiddenInput.dataset.options = JSON.stringify(options);

        const dropdown = document.createElement('div');
        dropdown.className = 'lookup-suggestions';

        let current = [];
        let selectedIdx = -1;

        function showSuggestions(query) {
            dropdown.innerHTML = '';
            selectedIdx = -1;
            if (!query) { current = []; return; }
            const q = query.toLowerCase();
            current = options.filter(o => (o.name || o.label || '').toLowerCase().includes(q)).slice(0, 8);
            if (current.length === 0) {
                const none = document.createElement('div');
                none.className = 'lookup-suggestion';
                none.textContent = 'No matches found';
                none.style.cssText = 'color:#999;pointer-events:none';
                dropdown.appendChild(none);
                return;
            }
            current.forEach((item, idx) => {
                const el = document.createElement('div');
                el.className = 'lookup-suggestion';
                el.textContent = item.name || item.label;
                el.addEventListener('mousedown', e => { e.preventDefault(); pick(item); });
                dropdown.appendChild(el);
            });
        }

        function pick(item) {
            searchInput.value = item.name || item.label;
            hiddenInput.value = item.id || item.value;
            dropdown.innerHTML = '';
            current = [];
            selectedIdx = -1;
        }

        function highlight() {
            Array.from(dropdown.querySelectorAll('.lookup-suggestion')).forEach((el, i) => {
                el.classList.toggle('highlighted', i === selectedIdx);
            });
        }

        searchInput.addEventListener('input', e => showSuggestions(e.target.value));

        searchInput.addEventListener('keydown', e => {
            if (e.key === 'ArrowDown') { e.preventDefault(); selectedIdx = Math.min(selectedIdx + 1, current.length - 1); highlight(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); selectedIdx = Math.max(selectedIdx - 1, -1); highlight(); }
            else if (e.key === 'Enter') { e.preventDefault(); if (selectedIdx >= 0) pick(current[selectedIdx]); }
            else if (e.key === 'Escape') { dropdown.innerHTML = ''; current = []; selectedIdx = -1; }
        });

        searchInput.addEventListener('blur', () => {
            // Short delay so mousedown on suggestion fires first
            setTimeout(() => { dropdown.innerHTML = ''; current = []; }, 150);
        });

        selectEl.parentNode.insertBefore(wrapper, selectEl);
        wrapper.appendChild(searchInput);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(hiddenInput);
        selectEl.remove();

        _lookupOptions[fieldId] = options;
    }

    // Build all three widgets and return a promise that resolves when done
    let _lookupReady = null;
    function initLookupWidgets() {
        if (_lookupReady) return _lookupReady;
        _lookupReady = Promise.all(
            Object.keys(_lookupEndpoints).map(async fieldId => {
                const options = await fetchOptions(_lookupEndpoints[fieldId]);
                buildSearchWidget(fieldId, options);
            })
        );
        return _lookupReady;
    }

    // Populate a lookup widget by numeric ID value
    function setLookupValue(fieldId, value) {
        if (!value && value !== 0) return;
        const hiddenInput = document.getElementById(fieldId);
        const searchInput = document.getElementById(fieldId + '_search');
        if (!hiddenInput || !searchInput) return;
        hiddenInput.value = value;
        try {
            const opts = JSON.parse(hiddenInput.dataset.options || '[]');
            const match = opts.find(o => String(o.id || o.value) === String(value));
            searchInput.value = match ? (match.name || match.label) : String(value);
        } catch (e) {
            searchInput.value = String(value);
        }
    }

    // ── Table state ──────────────────────────────────────────────
    let activeStatus = '';
    let sortCol      = 'name';
    let sortDir      = 'asc';

    function gradeNum(g) {
        if (!g) return 999;
        if (/K|Kindergarten/i.test(g)) return 0;
        const m = g.match(/\d+/);
        return m ? parseInt(m[0], 10) : 999;
    }

    function statusClass(s) {
        const v = (s || '').toUpperCase().trim();
        if (v === 'VERIFIED') return 'badge-verified';
        if (v === 'REJECTED') return 'badge-rejected';
        return 'badge-pending';
    }

    function statusLabel(s) {
        const v = (s || '').toUpperCase().trim();
        if (v === 'VERIFIED') return 'Verified';
        if (v === 'REJECTED') return 'Rejected';
        return 'Pending';
    }

    function normalizedStatus(s) {
        const v = (s || '').toUpperCase().trim();
        if (v === 'VERIFIED') return 'VERIFIED';
        if (v === 'REJECTED') return 'REJECTED';
        return 'PENDING';
    }

    function fullName(e) {
        return [e.pi_first_name, e.pi_middle_name, e.pi_last_name]
            .filter(Boolean).join(' ').trim();
    }

    function getFiltered() {
        const query = document.getElementById('searchInput').value.trim().toLowerCase();
        const grade = document.getElementById('filterGrade').value;
        const sex   = document.getElementById('filterSex').value;
        const sy    = document.getElementById('filterSY').value;

        return ALL_ENROLLMENTS.filter(e => {
            if (activeStatus && normalizedStatus(e.verification) !== activeStatus) return false;
            if (grade && (e.ed_grade_level || '') !== grade) return false;
            if (sex && (e.pi_sex || '').toUpperCase() !== sex) return false;
            if (sy && (e.ed_school_year || '') !== sy) return false;
            if (query) {
                const hay = (fullName(e) + ' ' + (e.ed_lrn || '') + ' ' +
                    (e.pi_last_name || '') + ' ' + (e.pi_first_name || '')).toLowerCase();
                if (!hay.includes(query)) return false;
            }
            return true;
        });
    }

    function getSorted(rows) {
        return [...rows].sort((a, b) => {
            let av, bv;
            switch (sortCol) {
                case 'name':   av = fullName(a).toLowerCase();   bv = fullName(b).toLowerCase(); break;
                case 'grade':  av = gradeNum(a.ed_grade_level);  bv = gradeNum(b.ed_grade_level); break;
                case 'sy':     av = (a.ed_school_year || '');    bv = (b.ed_school_year || ''); break;
                case 'sex':    av = (a.pi_sex || '');            bv = (b.pi_sex || ''); break;
                case 'status': av = normalizedStatus(a.verification); bv = normalizedStatus(b.verification); break;
                default:       av = ''; bv = '';
            }
            if (av < bv) return sortDir === 'asc' ? -1 : 1;
            if (av > bv) return sortDir === 'asc' ?  1 : -1;
            return 0;
        });
    }

    let currentSelectedId = null;

    function renderTable() {
        const filtered = getSorted(getFiltered());
        const tbody   = document.getElementById('tableBody');
        const noRes   = document.getElementById('noResults');
        const countEl = document.getElementById('resultsCount');

        countEl.textContent = filtered.length === 1
            ? '1 enrollment found'
            : filtered.length + ' enrollments found';

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            noRes.style.display = 'block';
            return;
        }
        noRes.style.display = 'none';

        tbody.innerHTML = filtered.map(e => {
            const id     = e.fk_full_name_bd;
            const name   = fullName(e) || '—';
            const grade  = e.ed_grade_level ? 'Grade ' + e.ed_grade_level : '—';
            const sy     = e.ed_school_year || '—';
            const sex    = e.pi_sex ? (e.pi_sex.charAt(0) + e.pi_sex.slice(1).toLowerCase()) : '—';
            const sClass = statusClass(e.verification);
            const sLabel = statusLabel(e.verification);
            const sel    = id === currentSelectedId ? ' selected-row' : '';

            return `<tr class="enrollment-row${sel}" data-id="${esc(id)}">
                <td class="fw-semibold">${esc(name)}</td>
                <td>${esc(grade)}</td>
                <td>${esc(sy)}</td>
                <td>${esc(sex)}</td>
                <td><span class="status-badge ${sClass}">${esc(sLabel)}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-load-row">Edit</button>
                    <a href="../../dashboard/teacher_dashboard/download_enrollment_pdf.php?id=${encodeURIComponent(id)}"
                       class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation()">PDF</a>
                </td>
            </tr>`;
        }).join('');
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function updateSortHeaders() {
        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.classList.remove('sorted-asc','sorted-desc');
            if (th.dataset.sort === sortCol)
                th.classList.add(sortDir === 'asc' ? 'sorted-asc' : 'sorted-desc');
        });
    }

    // ── Load enrollment into form ────────────────────────────────
    async function loadEnrollmentData(enrollmentId) {
        if (!enrollmentId) { hideForm(); return; }
        currentSelectedId = enrollmentId;
        renderTable();

        const spinner = document.getElementById('loadingSpinner');
        const details = document.getElementById('enrollmentDetails');

        spinner.style.display = 'block';
        details.classList.remove('show');
        document.getElementById('enrollmentDetailsWrapper')
            .scrollIntoView({ behavior: 'smooth', block: 'start' });

        try {
            // Run both in parallel: fetch enrollment data + ensure lookup widgets are ready
            const [response] = await Promise.all([
                fetch('./get_enrollment.php?id=' + encodeURIComponent(enrollmentId)).then(r => r.json()),
                initLookupWidgets()
            ]);

            if (response.success) {
                populateForm(response.data);
                document.getElementById('enrollmentId').value = enrollmentId;
                details.classList.add('show');
            } else {
                alert('Error loading enrollment: ' + response.message);
            }
        } catch (err) {
            console.error(err);
            alert('Error loading enrollment data.');
        } finally {
            spinner.style.display = 'none';
        }
    }

    function hideForm() {
        document.getElementById('enrollmentDetails').classList.remove('show');
        document.getElementById('loadingSpinner').style.display = 'none';
        currentSelectedId = null;
        renderTable();
    }

    // ── Populate form with fetched data ──────────────────────────
    function populateForm(data) {
        const lookupFields = new Set(['pi_mother_tongue_id','pi_religion_id','ac_indigenous_group_id']);

        // ── Enrollment ───────────────────────────────────────────
        if (data.enrollment) {
            Object.entries(data.enrollment).forEach(([key, value]) => {

                // Checkbox group
                if (key === 'li_learning_modality') {
                    const vals = (value || '').split(',').map(v => v.trim()).filter(Boolean);
                    document.querySelectorAll('input[name="li_learning_modality[]"]')
                        .forEach(cb => { cb.checked = vals.includes(cb.value); });
                    return;
                }

                // Lookup search widget
                if (lookupFields.has(key)) {
                    setLookupValue(key, value);
                    return;
                }

                const el = document.getElementById(key);
                if (!el) return;

                // Skip number inputs when DB value is null/empty (avoids showing "0")
                if (el.type === 'number' && (value === null || value === '' || value === undefined)) return;

                el.value = value ?? '';
            });

            // Verification status
            const statusSel = document.getElementById('verificationStatus');
            if (statusSel && data.enrollment.verification)
                statusSel.value = data.enrollment.verification;

            // Show sections
            document.getElementById('section-enrollment').style.display = 'block';
            if (Object.keys(data.enrollment).some(k => k.startsWith('pi_') && data.enrollment[k]))
                document.getElementById('section-personal').style.display = 'block';
        }

        // ── Address ──────────────────────────────────────────────
        if (data.address && Object.values(data.address).some(v => v)) {
            Object.entries(data.address).forEach(([key, value]) => {
                const el = document.getElementById(key);
                if (el) el.value = value || '';
            });
            document.getElementById('section-address').style.display = 'block';
        }

        // ── Medical ──────────────────────────────────────────────
        if (data.medical && Object.values(data.medical).some(v => v || (Array.isArray(v) && v.length))) {
            Object.entries(data.medical).forEach(([key, value]) => {
                if (key === 'mf_o_medical_conditions' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('mf_oc_' + val.toLowerCase().replace(/[^a-z0-9]/g,'_'));
                        if (cb) cb.checked = true;
                    });
                } else if (key === 'mf_mc_conditions' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('mf_mc_' + val.toLowerCase().replace(/[^a-z0-9]/g,'_'));
                        if (cb) cb.checked = true;
                    });
                } else {
                    const el = document.getElementById(key);
                    if (el) el.value = value || '';
                }
            });
            document.getElementById('section-medical').style.display = 'block';
        }

        // ── Parents ──────────────────────────────────────────────
        if (data.parents && Object.values(data.parents).some(v => v)) {
            Object.entries(data.parents).forEach(([key, value]) => {
                const el = document.getElementById(key);
                if (el) el.value = value || '';
            });
            document.getElementById('section-parents').style.display = 'block';
        }

        // ── Special Needs ────────────────────────────────────────
        if (data.specialNeeds && Object.values(data.specialNeeds).some(v => v || (Array.isArray(v) && v.length))) {
            Object.entries(data.specialNeeds).forEach(([key, value]) => {
                if (key === 'snep_a1_diagnosis' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('snep_diag_' + val.toLowerCase().replace(/[^a-z0-9]/g,'_'));
                        if (cb) cb.checked = true;
                    });
                } else if (key === 'snep_a2_manifestations' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('snep_manif_' + val.toLowerCase().replace(/[^a-z0-9]/g,'_'));
                        if (cb) cb.checked = true;
                    });
                } else {
                    const el = document.getElementById(key);
                    if (el) {
                        el.value = value ?? '';
                        if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
            document.getElementById('section-special-needs').style.display = 'block';
        }
    }

    // ── DOMContentLoaded ─────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Kick off lookup widget init immediately so it's ready by the time a row is clicked
        initLookupWidgets();

        renderTable();
        updateSortHeaders();

        // Search & filters
        let searchTimer;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(renderTable, 200);
        });
        document.getElementById('clearSearch').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            renderTable();
        });
        ['filterGrade','filterSex','filterSY'].forEach(id => {
            document.getElementById(id).addEventListener('change', renderTable);
        });
        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterGrade').value = '';
            document.getElementById('filterSex').value   = '';
            document.getElementById('filterSY').value    = '';
            activeStatus = '';
            updateStatusTabs();
            renderTable();
        });

        // Status tabs
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                activeStatus = this.dataset.status;
                updateStatusTabs();
                renderTable();
            });
        });

        // Sort headers
        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.addEventListener('click', function () {
                const col = this.dataset.sort;
                if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                else { sortCol = col; sortDir = 'asc'; }
                updateSortHeaders();
                renderTable();
            });
        });

        // Row click → load
        document.getElementById('tableBody').addEventListener('click', function (e) {
            const row = e.target.closest('tr.enrollment-row');
            if (!row || e.target.closest('a')) return;
            loadEnrollmentData(row.dataset.id);
        });

        // Form submit
        document.getElementById('verifyForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('./process_verify.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    if (d.success) window.location.href = './index.php?success=1';
                    else alert('Error: ' + d.message);
                })
                .catch(err => { console.error(err); alert('Error saving enrollment.'); });
        });

        // Pre-select from URL param
        const preSelected = new URLSearchParams(window.location.search).get('selected');
        if (preSelected) loadEnrollmentData(preSelected);
    });

    function updateStatusTabs() {
        const classMap = { '':'active-all','PENDING':'active-pending','VERIFIED':'active-verified','REJECTED':'active-rejected' };
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.className = 'status-tab';
            if (tab.dataset.status === activeStatus)
                tab.classList.add(classMap[activeStatus] || 'active-all');
        });
    }

    window.resetForm = function () {
        document.getElementById('verifyForm').reset();
        // Clear lookup search inputs too
        document.querySelectorAll('.lookup-search-input').forEach(el => el.value = '');
        document.querySelectorAll('input[type="hidden"][data-options]').forEach(el => el.value = '');
        hideForm();
        ['section-enrollment','section-personal','section-address',
         'section-medical','section-parents','section-special-needs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    };

})();
