
        const apiBase = '/GEMS-AMES/ams/api/index.php?request=';
        const statusBox = document.getElementById('status');
        const form = document.getElementById('enrollmentForm');

        async function fetchOptions(endpoint, selectId) {
            try {
                const response = await fetch(`${apiBase}${endpoint}`);
                const result = await response.json();
                if (!result.success) return;

                const select = document.getElementById(selectId);
                select.innerHTML = result.data.map(item => `<option value="${item.id}">${item.name}</option>`).join('');
            } catch (error) {
                console.error('Option load error:', endpoint, error);
            }
        }

        async function initOptions() {
            await Promise.all([
                fetchOptions('mother-tongue', 'pi_mother_tongue_id'),
                fetchOptions('religions', 'pi_religion_id'),
                fetchOptions('indigenous-groups', 'ac_indigenous_group_id')
            ]);
        }

        function updateStatus(message, type = 'success') {
            statusBox.textContent = message;
            statusBox.className = `status ${type}`;
            statusBox.style.display = 'block';
        }

        async function postJson(path, body) {
            const response = await fetch(`${apiBase}${path}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            });
            return response.json();
        }

        function collectFormData() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (value !== null && value !== '') {
                    data[key] = value;
                }
            });
            return data;
        }

        form.addEventListener('submit', async event => {
            event.preventDefault();
            statusBox.style.display = 'none';
            const data = collectFormData();

            if (!data.fk_full_name_bd || !data.ed_grade_level || !data.ed_lrn || !data.ed_school_year || !data.pi_last_name || !data.pi_first_name) {
                updateStatus('Please fill out all required enrollment fields before submitting.', 'error');
                return;
            }

            try {
                updateStatus('Submitting enrollment...', 'success');

                const enrollmentData = {
                    fk_full_name_bd: data.fk_full_name_bd,
                    ed_grade_level: data.ed_grade_level,
                    ed_lrn: Number(data.ed_lrn),
                    ed_school_year: data.ed_school_year,
                    rl_last_grade_level_completed: data.rl_last_grade_level_completed || '',
                    rl_last_school_year_completed: data.rl_last_school_year_completed || '',
                    rl_school_attended: data.rl_school_attended || '',
                    rl_school_id: data.rl_school_id ? Number(data.rl_school_id) : 0,
                    pi_psa_bcn: data.pi_psa_bcn ? Number(data.pi_psa_bcn) : 0,
                    pi_last_name: data.pi_last_name,
                    pi_first_name: data.pi_first_name,
                    pi_middle_name: data.pi_middle_name || '',
                    pi_extension: data.pi_extension || '',
                    pi_birth_date: data.pi_birth_date || new Date().toISOString().slice(0, 10),
                    pi_sex: data.pi_sex || 'MALE',
                    pi_place_of_birth: data.pi_place_of_birth || '',
                    pi_mother_tongue_id: Number(data.pi_mother_tongue_id || 1),
                    pi_religion_id: Number(data.pi_religion_id || 1),
                    pi__attended_early_learning_program_name: data.pi__attended_early_learning_program_name || '',
                    pi_learning_classification: data.pi_learning_classification || 'GRADED',
                    ac_indigenous_group_id: Number(data.ac_indigenous_group_id || 1),
                    ac_4ps_household_number: data.ac_4ps_household_number || '',
                    li_learning_modality: data.li_learning_modality || 'BLENDED (COMBINATION)'
                };

                if (data.user_account_id) {
                    enrollmentData.user_account_id = Number(data.user_account_id);
                }

                const enrollmentResult = await postJson('enrollments', enrollmentData);
                if (!enrollmentResult.success) {
                    updateStatus(`Enrollment failed: ${enrollmentResult.message}` + (enrollmentResult.errors ? '\n' + JSON.stringify(enrollmentResult.errors) : ''), 'error');
                    return;
                }

                const fk = data.fk_full_name_bd;
                const requests = [];

                const addressPayload = {
                    fk_full_name_bd: fk,
                    ca_house_number: data.ca_house_number || '',
                    ca_street_name: data.ca_street_name || '',
                    ca_barangay: data.ca_barangay || '',
                    ca_municipality: data.ca_municipality || '',
                    ca_provice: data.ca_provice || '',
                    ca_country: data.ca_country || '',
                    ca_zipcode: data.ca_zipcode ? Number(data.ca_zipcode) : 0,
                    ca_address_status: data.ca_address_status || 'Rental',
                    pa_house_number: data.pa_house_number || '',
                    pa_street_name: data.pa_street_name || '',
                    pa_barangay: data.pa_barangay || '',
                    pa_municipality: data.pa_municipality || '',
                    pa_province: data.pa_province || '',
                    pa_country: data.pa_country || '',
                    pa_zip_code: data.pa_zip_code ? Number(data.pa_zip_code) : 0,
                    pa_address_status: data.pa_address_status || 'Rental'
                };
                requests.push(postJson('addresses', addressPayload));

                const medicalPayload = {
                    fk_full_name_bd: fk,
                    mf_a_medicine: data.mf_a_medicine || '',
                    mf_a_pollen: data.mf_a_pollen || '',
                    mf_a_food: data.mf_a_food || '',
                    mf_a_others: data.mf_a_others || '',
                    mf_o_medical_conditions: data.mf_o_medical_conditions || '',
                    mf_o_others: data.mf_o_others || '',
                    mf_sh_surgery_date: data.mf_sh_surgery_date || new Date().toISOString().slice(0, 10),
                    mf_sh_hospital_name: data.mf_sh_hospital_name || '',
                    mf_sh_bodypart_affected: data.mf_sh_bodypart_affected || '',
                    mf_tm_type: data.mf_tm_type || '',
                    mf_tm_dosage_schedule: data.mf_tm_dosage_schedule || '',
                    mf_mc_conditions: data.mf_mc_conditions || '',
                    mf_mc_cancer_type: data.mf_mc_cancer_type || '',
                    mf_mc_others: data.mf_mc_others || '',
                    mf_exposure_c_v: Number(data.mf_exposure_c_v || 0),
                    mf_o_pertinent_information: data.mf_o_pertinent_information || ''
                };
                requests.push(postJson('medical', medicalPayload));

                const parentsPayload = {
                    fk_full_name_bd: fk,
                    fi_last_name: data.fi_last_name || '',
                    fi_first_name: data.fi_first_name || '',
                    fi_middle_name: data.fi_middle_name || '',
                    fi_contact_number: data.fi_contact_number || '',
                    fi_occupation: data.fi_occupation || '',
                    fi_relationship_status: data.fi_relationship_status || '',
                    fi_communication: data.fi_communication || '',
                    mi_last_name: data.mi_last_name || '',
                    mi_first_name: data.mi_first_name || '',
                    mi_middle_name: data.mi_middle_name || '',
                    mi_contact_number: data.mi_contact_number || '',
                    mi_occupation: data.mi_occupation || '',
                    mi_relationship_status: data.mi_relationship_status || '',
                    mi_communication: data.mi_communication || '',
                    gi_last_name: data.gi_last_name || '',
                    gi_first_name: data.gi_first_name || '',
                    gi_middle_name: data.gi_middle_name || '',
                    gi_contact_number: data.gi_contact_number || '',
                    gi_occupation: data.gi_occupation || '',
                    gi_relationship_status: data.gi_relationship_status || '',
                    gi_communication: data.gi_communication || '',
                    ec_to_contact: data.ec_to_contact || 'FATHER'
                };
                requests.push(postJson('parents', parentsPayload));

                const specialNeedsPayload = {
                    fk_full_name_bd: fk,
                    snep_a1_diagnosis: data.snep_a1_diagnosis || '',
                    snep_a1_sub_shpcd: data.snep_a1_sub_shpcd || 'CANCER',
                    snep_a1_sub_vi: data.snep_a1_sub_vi || 'BLIND',
                    snep_a2_manifestations: data.snep_a2_manifestations || '',
                    snep_pwd_id: Number(data.snep_pwd_id || 0)
                };
                requests.push(postJson('special-needs', specialNeedsPayload));

                const results = await Promise.all(requests);
                const failed = results.filter(res => !res.success);

                if (failed.length > 0) {
                    const messages = failed.map(res => `${res.message}${res.errors ? ': ' + JSON.stringify(res.errors) : ''}`);
                    updateStatus('Enrollment created but some related sections failed:\n' + messages.join('\n'), 'error');
                    return;
                }

                updateStatus('Enrollment and related information successfully submitted.', 'success');
                form.reset();
            } catch (error) {
                console.error(error);
                updateStatus('Unexpected error sending enrollment: ' + error.message, 'error');
            }
        });

        initOptions();