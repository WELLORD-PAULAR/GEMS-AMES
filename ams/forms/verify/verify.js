(function () {
    'use strict';

    // ────────────────────────────────────────────────────────────
    // LOOKUP WIDGETS (Mother Tongue, Religion, Indigenous Group)
    // ────────────────────────────────────────────────────────────

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

    function searchUrl(endpoint) {
        return '../../search/search.php?type=' + endpoint + '&limit=10000';
    }

    async function fetchOptions(endpoint) {
        try {
            const response = await fetch(searchUrl(endpoint));
            const data = await response.json();
            if (data.success && Array.isArray(data.data)) return data.data;
        } catch (e) {
            console.error('Lookup fetch failed:', endpoint, e);
        }
        return [];
    }

    function buildSearchWidget(selectElement, options, label) {
        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select-wrapper';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = `Type to search ${label}...`;
        searchInput.className = 'form-control lookup-search-input';
        searchInput.id = selectElement.id + '_search';
        searchInput.autocomplete = 'off';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = selectElement.name;
        hiddenInput.id = selectElement.id;
        hiddenInput.value = '';
        hiddenInput.dataset.options = JSON.stringify(options);

        const dropdown = document.createElement('div');
        dropdown.className = 'lookup-suggestions';

        let currentSuggestions = [];
        let selectedIndex = -1;

        function updateSuggestions(query) {
            dropdown.innerHTML = '';
            selectedIndex = -1;

            if (!query || query.length < 1) {
                currentSuggestions = [];
                return;
            }

            const queryLower = query.toLowerCase();
            currentSuggestions = options.filter(item =>
                (item.name || item.label || '').toLowerCase().includes(queryLower)
            ).slice(0, 8);

            if (currentSuggestions.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'lookup-suggestion';
                noResults.textContent = 'No matches found';
                noResults.style.color = '#999';
                noResults.style.pointerEvents = 'none';
                dropdown.appendChild(noResults);
                return;
            }

            currentSuggestions.forEach((item) => {
                const suggestionEl = document.createElement('div');
                suggestionEl.className = 'lookup-suggestion';
                suggestionEl.textContent = item.name || item.label;
                suggestionEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectItem(item);
                });
                dropdown.appendChild(suggestionEl);
            });
        }

        function selectItem(item) {
            searchInput.value = item.name || item.label;
            hiddenInput.value = item.id || item.value;
            dropdown.innerHTML = '';
            currentSuggestions = [];
            selectedIndex = -1;
        }

        function highlight() {
            Array.from(dropdown.querySelectorAll('.lookup-suggestion')).forEach((el, i) => {
                el.classList.toggle('highlighted', i === selectedIndex);
            });
        }

        searchInput.addEventListener('input', (e) => updateSuggestions(e.target.value));

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
                highlight();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                highlight();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0) selectItem(currentSuggestions[selectedIndex]);
            } else if (e.key === 'Escape') {
                dropdown.innerHTML = '';
                currentSuggestions = [];
                selectedIndex = -1;
            }
        });

        searchInput.addEventListener('blur', () => {
            setTimeout(() => { dropdown.innerHTML = ''; currentSuggestions = []; }, 150);
        });

        selectElement.parentNode.insertBefore(wrapper, selectElement);
        wrapper.appendChild(searchInput);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(hiddenInput);
        selectElement.remove();

        _lookupOptions[selectElement.id] = options;
    }

    let _lookupReady = null;
    function initLookupWidgets() {
        if (_lookupReady) return _lookupReady;
        _lookupReady = Promise.all(
            Object.keys(_lookupEndpoints).map(async (fieldId) => {
                const options = await fetchOptions(_lookupEndpoints[fieldId]);
                const selectEl = document.getElementById(fieldId);
                if (selectEl) buildSearchWidget(selectEl, options, _lookupLabels[fieldId]);
            })
        );
        return _lookupReady;
    }

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

    // ────────────────────────────────────────────────────────────
    // TABLE STATE & FILTERING
    // ────────────────────────────────────────────────────────────

    let activeStatus = '';
    let sortColumn = 'name';
    let sortDirection = 'asc';
    let currentSelectedId = null;

    function normalizeGradeForSort(grade) {
        if (!grade) return 999;
        if (/K|Kindergarten/i.test(grade)) return 0;
        const match = grade.match(/\d+/);
        return match ? parseInt(match[0], 10) : 999;
    }

    function normalizeStatus(status) {
        const raw = (status || '').toString().toUpperCase().trim();
        const normalized = raw
            .replace(/[\s\-]+/g, '_')
            .replace(/[^A-Z0-9_]/g, '');

        const statusMap = {
            'VERIFIED': 'VERIFIED',
            'REJECTED': 'REJECTED',
            'PROCESSING': 'PROCESSING',
            'WITHDRAWN': 'WITHDRAWN',
            'TRANSFERRED_IN': 'TRANSFERRED_IN',
            'TRANSFERREDOUT': 'TRANSFERRED_OUT',
            'TRANSFERRED_OUT': 'TRANSFERRED_OUT',
            'TRANSFERREDIN': 'TRANSFERRED_IN',
            'TRANSFERRED_IN': 'TRANSFERRED_IN',
            'DROPPED': 'DROPPED',
            'PENDING': 'PENDING'
        };

        return statusMap[normalized] || 'PENDING';
    }

    function getStatusBadgeClass(status) {
        const s = normalizeStatus(status);
        const classMap = {
            'VERIFIED': 'badge-verified',
            'REJECTED': 'badge-rejected',
            'PROCESSING': 'badge-processing',
            'WITHDRAWN': 'badge-withdrawn',
            'TRANSFERRED_IN': 'badge-transferred-in',
            'TRANSFERRED_OUT': 'badge-transferred-out',
            'DROPPED': 'badge-dropped',
            'PENDING': 'badge-pending'
        };
        return classMap[s] || 'badge-pending';
    }

    function getStatusLabel(status) {
        const s = normalizeStatus(status);
        const labelMap = {
            'VERIFIED': 'Verified',
            'REJECTED': 'Rejected',
            'PROCESSING': 'Processing',
            'WITHDRAWN': 'Withdrawn',
            'TRANSFERRED_IN': 'Transferred In',
            'TRANSFERRED_OUT': 'Transferred Out',
            'DROPPED': 'Dropped',
            'PENDING': 'Pending'
        };
        return labelMap[s] || 'Pending';
    }

    function buildFullName(enrollment) {
        return [enrollment.pi_first_name, enrollment.pi_middle_name, enrollment.pi_last_name]
            .filter(Boolean).join(' ').trim();
    }

    function getFilteredEnrollments() {
        const searchQuery = document.getElementById('searchInput').value.trim().toLowerCase();
        const gradeFilter = document.getElementById('filterGrade').value;
        const sexFilter = document.getElementById('filterSex').value;
        const syFilter = document.getElementById('filterSY').value;

        return window.ALL_ENROLLMENTS.filter(enrollment => {
            if (activeStatus && normalizeStatus(enrollment.verification) !== activeStatus) return false;
            if (gradeFilter && (enrollment.ed_grade_level || '') !== gradeFilter) return false;
            if (sexFilter && (enrollment.pi_sex || '').toUpperCase() !== sexFilter) return false;
            if (syFilter && (enrollment.ed_school_year || '') !== syFilter) return false;

            if (searchQuery) {
                const searchableText = (
                    buildFullName(enrollment) + ' ' +
                    (enrollment.ed_lrn || '') + ' ' +
                    (enrollment.pi_last_name || '') + ' ' +
                    (enrollment.pi_first_name || '')
                ).toLowerCase();
                if (!searchableText.includes(searchQuery)) return false;
            }

            return true;
        });
    }

    function getSortedEnrollments(enrollments) {
        return [...enrollments].sort((a, b) => {
            let valueA, valueB;

            switch (sortColumn) {
                case 'name':
                    valueA = buildFullName(a).toLowerCase();
                    valueB = buildFullName(b).toLowerCase();
                    break;
                case 'grade':
                    valueA = normalizeGradeForSort(a.ed_grade_level);
                    valueB = normalizeGradeForSort(b.ed_grade_level);
                    break;
                case 'sy':
                    valueA = (a.ed_school_year || '');
                    valueB = (b.ed_school_year || '');
                    break;
                case 'sex':
                    valueA = (a.pi_sex || '');
                    valueB = (b.pi_sex || '');
                    break;
                case 'status':
                    valueA = normalizeStatus(a.verification);
                    valueB = normalizeStatus(b.verification);
                    break;
                default:
                    valueA = '';
                    valueB = '';
            }

            if (valueA < valueB) return sortDirection === 'asc' ? -1 : 1;
            if (valueA > valueB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    function htmlEscape(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderTable() {
        const filtered = getSortedEnrollments(getFilteredEnrollments());
        const tbody = document.getElementById('tableBody');
        const noResults = document.getElementById('noResults');
        const resultsCount = document.getElementById('resultsCount');

        resultsCount.textContent = filtered.length === 1
            ? '1 enrollment found'
            : `${filtered.length} enrollments found`;

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';

        tbody.innerHTML = filtered.map(enrollment => {
            const id = enrollment.fk_full_name_bd;
            const name = buildFullName(enrollment) || '—';
            const grade = enrollment.ed_grade_level ? `Grade ${enrollment.ed_grade_level}` : '—';
            const schoolYear = enrollment.ed_school_year || '—';
            const sex = enrollment.pi_sex ? (enrollment.pi_sex.charAt(0) + enrollment.pi_sex.slice(1).toLowerCase()) : '—';
            const badgeClass = getStatusBadgeClass(enrollment.verification);
            const statusLabel = getStatusLabel(enrollment.verification);
            const isSelected = id === currentSelectedId ? ' selected-row' : '';

            return `<tr class="enrollment-row${isSelected}" data-id="${htmlEscape(id)}">
                <td class="fw-semibold">${htmlEscape(name)}</td>
                <td>${htmlEscape(grade)}</td>
                <td>${htmlEscape(schoolYear)}</td>
                <td>${htmlEscape(sex)}</td>
                <td><span class="status-badge ${badgeClass}">${htmlEscape(statusLabel)}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-load-row">Edit</button>
                    <a href="../../dashboard/teacher_dashboard/download_enrollment_pdf.php?id=${encodeURIComponent(id)}"
                       class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation()">PDF</a>
                </td>
            </tr>`;
        }).join('');
    }

    function updateSortHeaders() {
        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
            if (th.dataset.sort === sortColumn) {
                th.classList.add(sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
            }
        });
    }

    function updateStatusTabStyles() {
        const statusTabClassMap = {
            '': 'active-all',
            'PENDING': 'active-pending',
            'VERIFIED': 'active-verified',
            'REJECTED': 'active-rejected',
            'PROCESSING': 'active-processing',
            'WITHDRAWN': 'active-withdrawn',
            'TRANSFERRED_IN': 'active-transferred-in',
            'TRANSFERRED_OUT': 'active-transferred-out',
            'DROPPED': 'active-dropped'
        };

        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.className = 'status-tab';
            if (tab.dataset.status === activeStatus) {
                tab.classList.add(statusTabClassMap[activeStatus] || 'active-all');
            }
        });
    }

    // ────────────────────────────────────────────────────────────
    // ENROLLMENT LOADING & FORM POPULATION
    // ────────────────────────────────────────────────────────────

    async function loadEnrollmentData(enrollmentId) {
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

        try {
            const [response] = await Promise.all([
                fetch(`./get_enrollment.php?id=${encodeURIComponent(enrollmentId)}`).then(r => r.json()),
                initLookupWidgets()
            ]);

            if (response.success) {
                populateForm(response.data);
                document.getElementById('enrollmentId').value = enrollmentId;
                details.classList.add('show');
            } else {
                alert(`Error loading enrollment: ${response.message}`);
            }
        } catch (err) {
            console.error('Error loading enrollment:', err);
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

    function populateForm(data) {
        const lookupFields = new Set(['pi_mother_tongue_id', 'pi_religion_id', 'ac_indigenous_group_id']);

        // ── Enrollment Section
        if (data.enrollment) {
            Object.entries(data.enrollment).forEach(([key, value]) => {
                // Handle checkbox groups
                if (key === 'li_learning_modality') {
                    const values = (value || '').split(',').map(v => v.trim()).filter(Boolean);
                    document.querySelectorAll('input[name="li_learning_modality[]"]')
                        .forEach(cb => { cb.checked = values.includes(cb.value); });
                    return;
                }

                // Handle lookup widgets
                if (lookupFields.has(key)) {
                    setLookupValue(key, value);
                    return;
                }

                const element = document.getElementById(key);
                if (!element) return;

                // Skip number inputs with empty values
                if (element.type === 'number' && (value === null || value === '' || value === undefined)) return;

                element.value = value ?? '';
            });

            // Set verification status
            const statusSelect = document.getElementById('verificationStatus');
            if (statusSelect && data.enrollment.verification) {
                statusSelect.value = data.enrollment.verification;
            }

            // Show sections with data
            document.getElementById('section-enrollment').style.display = 'block';
            if (Object.keys(data.enrollment).some(k => (k.startsWith('pi_') || k.startsWith('ac_') || k.startsWith('li_')) && data.enrollment[k])) {
                document.getElementById('section-personal').style.display = 'block';
            }
        }

        // ── Address Section
        if (data.address && Object.values(data.address).some(v => v)) {
            Object.entries(data.address).forEach(([key, value]) => {
                const element = document.getElementById(key);
                if (element) element.value = value || '';
            });
            document.getElementById('section-address').style.display = 'block';
        }

        // ── Medical Section
        if (data.medical && Object.values(data.medical).some(v => v || (Array.isArray(v) && v.length))) {
            Object.entries(data.medical).forEach(([key, value]) => {
                if (key === 'mf_o_medical_conditions' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('mf_oc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_'));
                        if (cb) cb.checked = true;
                    });
                } else if (key === 'mf_mc_conditions' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('mf_mc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_'));
                        if (cb) cb.checked = true;
                    });
                } else {
                    const element = document.getElementById(key);
                    if (element) element.value = value || '';
                }
            });
            document.getElementById('section-medical').style.display = 'block';
        }

        // ── Parents Section
        if (data.parents && Object.values(data.parents).some(v => v)) {
            Object.entries(data.parents).forEach(([key, value]) => {
                const element = document.getElementById(key);
                if (element) element.value = value || '';
            });
            document.getElementById('section-parents').style.display = 'block';
        }

        // ── Special Needs Section
        if (data.specialNeeds && Object.values(data.specialNeeds).some(v => v || (Array.isArray(v) && v.length))) {
            Object.entries(data.specialNeeds).forEach(([key, value]) => {
                if (key === 'snep_a1_diagnosis' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('snep_diag_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_'));
                        if (cb) cb.checked = true;
                    });
                } else if (key === 'snep_a2_manifestations' && Array.isArray(value)) {
                    value.forEach(val => {
                        const cb = document.getElementById('snep_manif_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_'));
                        if (cb) cb.checked = true;
                    });
                } else {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = value ?? '';
                        if (element.tagName === 'SELECT') {
                            element.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
            });
            document.getElementById('section-special-needs').style.display = 'block';
        }
    }

    // ────────────────────────────────────────────────────────────
    // EVENT LISTENERS & INITIALIZATION
    // ────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        initLookupWidgets();
        renderTable();
        updateSortHeaders();

        // Search input
        let searchTimer;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(renderTable, 200);
        });

        document.getElementById('clearSearch').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            renderTable();
        });

        // Filter dropdowns
        ['filterGrade', 'filterSex', 'filterSY'].forEach(filterId => {
            document.getElementById(filterId).addEventListener('change', renderTable);
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterGrade').value = '';
            document.getElementById('filterSex').value = '';
            document.getElementById('filterSY').value = '';
            activeStatus = '';
            updateStatusTabStyles();
            renderTable();
        });

        // Status tabs
        document.querySelectorAll('.status-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                activeStatus = this.dataset.status;
                if (activeStatus) {
                    activeStatus = normalizeStatus(activeStatus);
                }
                updateStatusTabStyles();
                renderTable();
            });
        });

        // Sort headers
        document.querySelectorAll('#resultsTable th[data-sort]').forEach(th => {
            th.addEventListener('click', function () {
                const col = this.dataset.sort;
                if (sortColumn === col) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = col;
                    sortDirection = 'asc';
                }
                updateSortHeaders();
                renderTable();
            });
        });

        // Table row click
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
                .then(data => {
                    if (data.success) {
                        // Reload page to refresh ALL_ENROLLMENTS with updated database values
                        window.location.href = './index.php?success=1';
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error saving enrollment.');
                });
        });

        // Pre-select from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const preSelected = urlParams.get('selected');
        if (preSelected) loadEnrollmentData(preSelected);
    });

    // ────────────────────────────────────────────────────────────
    // GLOBAL FUNCTIONS
    // ────────────────────────────────────────────────────────────

    window.resetForm = function () {
        document.getElementById('verifyForm').reset();
        document.querySelectorAll('.lookup-search-input').forEach(el => el.value = '');
        document.querySelectorAll('input[type="hidden"][data-options]').forEach(el => el.value = '');
        hideForm();
        [
            'section-enrollment', 'section-personal', 'section-address',
            'section-medical', 'section-parents', 'section-special-needs'
        ].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    };

})();
