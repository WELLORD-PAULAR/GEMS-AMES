
(function () {
    'use strict';

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
        const query  = document.getElementById('searchInput').value.trim().toLowerCase();
        const grade  = document.getElementById('filterGrade').value;
        const sex    = document.getElementById('filterSex').value;
        const sy     = document.getElementById('filterSY').value;

        return ALL_ENROLLMENTS.filter(e => {
            if (activeStatus && normalizedStatus(e.verification) !== activeStatus) return false;
            if (grade && (e.ed_grade_level || '') !== grade) return false;
            if (sex && (e.pi_sex || '').toUpperCase() !== sex) return false;
            if (sy && (e.ed_school_year || '') !== sy) return false;
            if (query) {
                const haystack = (
                    fullName(e) + ' ' +
                    (e.ed_lrn || '') + ' ' +
                    (e.pi_last_name || '') + ' ' +
                    (e.pi_first_name || '')
                ).toLowerCase();
                if (!haystack.includes(query)) return false;
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
            if (av > bv) return sortDir === 'asc' ? 1  : -1;
            return 0;
        });
    }

    let currentSelectedId = null;

    function renderTable() {
        const filtered = getSorted(getFiltered());
        const tbody    = document.getElementById('tableBody');
        const noRes    = document.getElementById('noResults');
        const countEl  = document.getElementById('resultsCount');

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
            const id      = e.fk_full_name_bd;
            const name    = fullName(e) || '—';
            const grade   = e.ed_grade_level ? 'Grade ' + e.ed_grade_level : '—';
            const sy      = e.ed_school_year || '—';
            const sex     = e.pi_sex ? (e.pi_sex.charAt(0) + e.pi_sex.slice(1).toLowerCase()) : '—';
            const sClass  = statusClass(e.verification);
            const sLabel  = statusLabel(e.verification);
            const sel     = id === currentSelectedId ? ' selected-row' : '';

            return `<tr class="enrollment-row${sel}" data-id="${escHtml(id)}">
                <td class="fw-semibold">${escHtml(name)}</td>
                <td>${escHtml(grade)}</td>
                <td>${escHtml(sy)}</td>
                <td>${escHtml(sex)}</td>
                <td><span class="status-badge ${sClass}">${escHtml(sLabel)}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-load-row">
                        Edit
                    </button>
                    <a href="../../dashboard/teacher_dashboard/download_enrollment_pdf.php?id=${encodeURIComponent(id)}"
                       class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation()">
                        PDF
                    </a>
                </td>
            </tr>`;
        }).join('');
    }

    function escHtml(s) {
        return String(s ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function updateSortHeaders() {
        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
            if (th.dataset.sort === sortCol) {
                th.classList.add(sortDir === 'asc' ? 'sorted-asc' : 'sorted-desc');
            }
        });
    }

    function loadEnrollmentData(enrollmentId) {
        if (!enrollmentId) {
            hideForm();
            return;
        }
        currentSelectedId = enrollmentId;
        renderTable();

        const spinner = document.getElementById('loadingSpinner');
        const details = document.getElementById('enrollmentDetails');

        spinner.style.display = 'block';
        details.classList.remove('show');

        document.getElementById('enrollmentDetailsWrapper')
            .scrollIntoView({ behavior: 'smooth', block: 'start' });

        fetch('./get_enrollment.php?id=' + encodeURIComponent(enrollmentId))
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    populateForm(data.data);
                    document.getElementById('enrollmentId').value = enrollmentId;
                    details.classList.add('show');
                } else {
                    alert('Error loading enrollment: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error loading enrollment data.');
            })
            .finally(() => {
                spinner.style.display = 'none';
            });
    }

    function hideForm() {
        document.getElementById('enrollmentDetails').classList.remove('show');
        document.getElementById('loadingSpinner').style.display = 'none';
        currentSelectedId = null;
        renderTable();
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderTable();
        updateSortHeaders();

        let searchTimer;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { renderTable(); }, 200);
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

        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                activeStatus = this.dataset.status;
                updateStatusTabs();
                renderTable();
            });
        });

        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.addEventListener('click', function () {
                const col = this.dataset.sort;
                if (sortCol === col) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortCol = col;
                    sortDir = 'asc';
                }
                updateSortHeaders();
                renderTable();
            });
        });

        document.getElementById('tableBody').addEventListener('click', function (e) {
            const btnLoad = e.target.closest('.btn-load-row');
            const row     = e.target.closest('tr.enrollment-row');
            if (!row) return;
            if (e.target.closest('a')) return;
            loadEnrollmentData(row.dataset.id);
        });

        document.getElementById('verifyForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('./process_verify.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = './index.php?success=1';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error saving enrollment.');
                });
        });

        const params = new URLSearchParams(window.location.search);
        const preSelected = params.get('selected');
        if (preSelected) {
            loadEnrollmentData(preSelected);
        }
    });

    function updateStatusTabs() {
        const classMap = {
            ''         : 'active-all',
            'PENDING'  : 'active-pending',
            'VERIFIED' : 'active-verified',
            'REJECTED' : 'active-rejected',
        };
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.className = 'status-tab';
            if (tab.dataset.status === activeStatus) {
                tab.classList.add(classMap[activeStatus] || 'active-all');
            }
        });
    }

    function populateForm(data) {
        if (data.enrollment) {
            const lookupKeys = ['pi_mother_tongue_id','pi_religion_id','ac_indigenous_group_id'];
            Object.keys(data.enrollment).forEach(key => {
                // Handle li_learning_modality as checkboxes
                if (key === 'li_learning_modality') {
                    const vals = (data.enrollment[key] || '').split(',').map(v => v.trim()).filter(Boolean);
                    document.querySelectorAll('input[name="li_learning_modality[]"]').forEach(cb => {
                        cb.checked = vals.includes(cb.value);
                    });
                    return;
                }

                const element = document.getElementById(key);
                if (element) {
                    const value = data.enrollment[key] || '';
                    element.value = value;
                    if (lookupKeys.includes(key)) {
                        try {
                            const searchInput = document.getElementById(key + '_search');
                            const optsJson = element.dataset.options;
                            if (searchInput && optsJson) {
                                const opts = JSON.parse(optsJson || '[]');
                                const match = opts.find(o => (o.id || o.value || '') == value);
                                if (match) {
                                    searchInput.value = match.name || match.label || '';
                                } else {
                                    const byName = opts.find(o =>
                                        (o.name||o.label||'').toLowerCase() === (value||'').toString().toLowerCase());
                                    if (byName) searchInput.value = byName.name || byName.label || '';
                                }
                            }
                        } catch (err) {
                            console.warn('Lookup populate failed for', key, err);
                        }
                    }
                }
            });

            const statusSelect = document.getElementById('verificationStatus');
            if (statusSelect) {
                statusSelect.value = data.enrollment.verification || statusSelect.value;
            }

            if (Object.values(data.enrollment).some(v => v)) {
                document.getElementById('section-enrollment').style.display = 'block';
                const hasPersonal = Object.keys(data.enrollment).some(k => k.startsWith('pi_') && data.enrollment[k]);
                if (hasPersonal) {
                    document.getElementById('section-personal').style.display = 'block';
                }
            }
        }

        if (data.address && Object.keys(data.address).length > 0) {
            Object.keys(data.address).forEach(key => {
                const el = document.getElementById(key);
                if (el) el.value = data.address[key] || '';
            });
            if (Object.values(data.address).some(v => v)) {
                document.getElementById('section-address').style.display = 'block';
            }
        }

        if (data.medical && Object.keys(data.medical).length > 0) {
            const setFields = ['mf_o_medical_conditions','mf_mc_conditions'];
            Object.keys(data.medical).forEach(key => {
                const value = data.medical[key];
                if (setFields.includes(key) && Array.isArray(value)) {
                    value.forEach(val => {
                        const cbId = key === 'mf_o_medical_conditions'
                            ? 'mf_oc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_')
                            : 'mf_mc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_');
                        const cb = document.getElementById(cbId);
                        if (cb) cb.checked = true;
                    });
                } else {
                    const el = document.getElementById(key);
                    if (el) el.value = value || '';
                }
            });
            if (Object.values(data.medical).some(v => v)) {
                document.getElementById('section-medical').style.display = 'block';
            }
        }

        if (data.parents && Object.keys(data.parents).length > 0) {
            Object.keys(data.parents).forEach(key => {
                const el = document.getElementById(key);
                if (el) el.value = data.parents[key] || '';
            });
            if (Object.values(data.parents).some(v => v)) {
                document.getElementById('section-parents').style.display = 'block';
            }
        }

        if (data.specialNeeds && Object.keys(data.specialNeeds).length > 0) {
            const keyMap = { 'pwd': 'snep_pwd_id', 'pwd_id': 'snep_pwd_id', 'snep_pwd': 'snep_pwd_id' };
            const setFields = ['snep_a1_diagnosis','snep_a2_manifestations'];
            Object.keys(data.specialNeeds).forEach(key => {
                const value = data.specialNeeds[key];
                if (setFields.includes(key) && Array.isArray(value)) {
                    value.forEach(val => {
                        const cbId = key === 'snep_a1_diagnosis'
                            ? 'snep_diag_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_')
                            : 'snep_manif_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_');
                        const cb = document.getElementById(cbId);
                        if (cb) cb.checked = true;
                    });
                } else {
                    const mappedKey = keyMap[key] || key;
                    const el = document.getElementById(mappedKey);
                    if (el) {
                        el.value = value ?? '';
                        if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        const candidate = document.querySelector(`[id$="${key}"]`);
                        if (candidate) candidate.value = value ?? '';
                    }
                }
            });
            if (Object.values(data.specialNeeds).some(v => v)) {
                document.getElementById('section-special-needs').style.display = 'block';
            }
        }
    }
    window.resetForm = function () {
        document.getElementById('verifyForm').reset();
        hideForm();
        ['section-enrollment','section-personal','section-address',
         'section-medical','section-parents','section-special-needs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    };

})();