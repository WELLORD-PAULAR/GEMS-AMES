        function filterByStatus(status) {
            window.location.href = './index.php?status=' + encodeURIComponent(status);
        }

        function getGradeSortValue(gradeText) {
            if (!gradeText) return Number.MAX_SAFE_INTEGER;
            if (/Kindergarten|KD|K/i.test(gradeText)) return 0;
            const match = gradeText.match(/\d+/);
            return match ? parseInt(match[0], 10) : Number.MAX_SAFE_INTEGER;
        }

        function sortEnrollmentsByGrade(direction) {
            const select = document.getElementById('enrollmentSelect');
            const options = Array.from(select.options).slice(1);
            const emptyOption = select.options[0];
            
            options.sort((a, b) => {
                const aGrade = a.getAttribute('data-grade') || '';
                const bGrade = b.getAttribute('data-grade') || '';
                
                const aText = a.textContent.split('(')[0].trim();
                const bText = b.textContent.split('(')[0].trim();
                
                const aGradeVal = getGradeSortValue(aGrade);
                const bGradeVal = getGradeSortValue(bGrade);
                
                if (aGradeVal === bGradeVal) {
                    return aText.localeCompare(bText);
                }
                return direction === 'asc' ? aGradeVal - bGradeVal : bGradeVal - aGradeVal;
            });
            
            select.innerHTML = '';
            select.appendChild(emptyOption);
            options.forEach(opt => select.appendChild(opt));
        }

        document.addEventListener('DOMContentLoaded', function() {
            const gradeSortControl = document.getElementById('gradeSort');
            if (gradeSortControl) {
                gradeSortControl.addEventListener('change', function() {
                    sortEnrollmentsByGrade(this.value);
                });
            }
        });

        function loadEnrollmentData(enrollmentId) {
            if (!enrollmentId) {
                document.getElementById('enrollmentDetails').classList.remove('show');
                return;
            }

            const spinner = document.getElementById('loadingSpinner');
            const details = document.getElementById('enrollmentDetails');
            
            spinner.style.display = 'block';
            details.classList.remove('show');

            fetch('./get_enrollment.php?id=' + encodeURIComponent(enrollmentId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.data);
                        document.getElementById('enrollmentId').value = enrollmentId;
                        details.classList.add('show');
                    } else {
                        alert('Error loading enrollment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading enrollment data');
                })
                .finally(() => {
                    spinner.style.display = 'none';
                });
        }

        function populateForm(data) {
            const sectionMap = {
                'enrollment': 'section-enrollment',
                'address': 'section-address',
                'medical': 'section-medical',
                'parents': 'section-parents',
                'specialNeeds': 'section-special-needs'
            };

            if (data.enrollment) {
                const lookupKeys = ['pi_mother_tongue_id','pi_religion_id','ac_indigenous_group_id'];
                Object.keys(data.enrollment).forEach(key => {
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
                                        const byName = opts.find(o => (o.name || o.label || '').toLowerCase() === (value || '').toString().toLowerCase());
                                        if (byName) searchInput.value = byName.name || byName.label || '';
                                    }
                                }
                            } catch (err) {
                                console.warn('Failed to populate lookup display for', key, err);
                            }
                        }
                    }
                });

                const statusSelect = document.getElementById('verificationStatus');
                if (statusSelect) {
                    statusSelect.value = data.enrollment.verification || statusSelect.value;
                }

                if (Object.values(data.enrollment).some(val => val)) {
                    document.getElementById('section-enrollment').style.display = 'block';

                    const hasPersonal = Object.keys(data.enrollment).some(k => k.startsWith('pi_') && data.enrollment[k]);
                    if (hasPersonal) {
                        document.getElementById('section-personal').style.display = 'block';
                    }
                }
            }

            if (data.address && Object.keys(data.address).length > 0) {
                Object.keys(data.address).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = data.address[key] || '';
                    }
                });

                if (Object.values(data.address).some(val => val)) {
                    document.getElementById('section-address').style.display = 'block';
                }
            }

            if (data.medical && Object.keys(data.medical).length > 0) {
                const setFields = ['mf_o_medical_conditions', 'mf_mc_conditions'];

                Object.keys(data.medical).forEach(key => {
                    const value = data.medical[key];
                    if (setFields.includes(key) && Array.isArray(value)) {
                        value.forEach(val => {
                            const checkboxId = key === 'mf_o_medical_conditions'
                                ? 'mf_oc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_')
                                : 'mf_mc_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_');
                            const checkbox = document.getElementById(checkboxId);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    } else {
                        const element = document.getElementById(key);
                        if (element) {
                            element.value = value || '';
                        }
                    }
                });

                if (Object.values(data.medical).some(val => val)) {
                    document.getElementById('section-medical').style.display = 'block';
                }
            }

            if (data.parents && Object.keys(data.parents).length > 0) {
                Object.keys(data.parents).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = data.parents[key] || '';
                    }
                });
                if (Object.values(data.parents).some(val => val)) {
                    document.getElementById('section-parents').style.display = 'block';
                }
            }

            if (data.specialNeeds && Object.keys(data.specialNeeds).length > 0) {
                const keyMap = {
                    'pwd': 'snep_pwd_id',
                    'pwd_id': 'snep_pwd_id',
                    'snep_pwd': 'snep_pwd_id'
                };

                const setFields = ['snep_a1_diagnosis', 'snep_a2_manifestations'];

                Object.keys(data.specialNeeds).forEach(key => {
                    const value = data.specialNeeds[key];
        
                    if (setFields.includes(key) && Array.isArray(value)) {
                        value.forEach(val => {
                            const checkboxId = key === 'snep_a1_diagnosis' 
                                ? 'snep_diag_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_')
                                : 'snep_manif_' + val.toLowerCase().replace(/[^a-z0-9]/g, '_');
                            const checkbox = document.getElementById(checkboxId);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    } else {
                        const mappedKey = keyMap[key] || key;
                        const element = document.getElementById(mappedKey);
                        if (element) {
                            const val = value ?? '';
                            element.value = val;
                            if (element.tagName === 'SELECT') {
                                const evt = new Event('change', { bubbles: true });
                                element.dispatchEvent(evt);
                            }
                        } else {
                            const candidate = document.querySelector(`[id$="${key}"]`);
                            if (candidate) {
                                candidate.value = value ?? '';
                            }
                        }
                    }
                });
                if (Object.values(data.specialNeeds).some(val => val)) {
                    document.getElementById('section-special-needs').style.display = 'block';
                }
            }
        }

        function resetForm() {
            document.getElementById('verifyForm').reset();
            document.getElementById('enrollmentSelect').value = '';
            document.getElementById('enrollmentDetails').classList.remove('show');
            const sections = ['section-enrollment','section-personal','section-address','section-medical','section-parents','section-special-needs'];
            sections.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        }
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const statusDiv = document.getElementById('status');
            
            fetch('./process_verify.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = './index.php?success=1';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving enrollment');
            });
        });
        (function () {
            const params = new URLSearchParams(window.location.search);
            const selectedEnrollment = params.get('selected');
            const status = params.get('status');
            
            if (status) {
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) {
                    statusFilter.value = status;
                }
            }
            
            if (selectedEnrollment) {
                const dropdown = document.getElementById('enrollmentSelect');
                if (dropdown) {
                    dropdown.value = selectedEnrollment;
                }
                if (typeof loadEnrollmentData === 'function') {
                    loadEnrollmentData(selectedEnrollment);
                }
            }
        })();