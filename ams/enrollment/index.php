<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
</head>
<body>
    <div class="container">
        <h1>Student Enrollment</h1>
        <p>Submit student data using the AMS API endpoints. The form will create the enrollment first, then save address, medical, parent, and special needs information.</p>

        <form id="enrollmentForm">
            <div class="section">
                <h2>1. Enrollment Details</h2>
                <div class="grid">
                    <div>
                        <label for="fk_full_name_bd">Student Full Name</label>
                        <input type="text" id="fk_full_name_bd" name="fk_full_name_bd" required>
                    </div>
                    <div>
                        <label for="ed_grade_level">Grade Level</label>
                        <input type="text" id="ed_grade_level" name="ed_grade_level" required>
                    </div>
                    <div>
                        <label for="ed_lrn">LRN</label>
                        <input type="number" id="ed_lrn" name="ed_lrn" required>
                    </div>
                    <div>
                        <label for="ed_school_year">School Year</label>
                        <input type="text" id="ed_school_year" name="ed_school_year" placeholder="2025-2026" required>
                    </div>
                    <div>
                        <label for="user_account_id">User Account ID</label>
                        <input type="number" id="user_account_id" name="user_account_id" placeholder="Optional">
                    </div>
                    <div>
                        <label for="li_learning_modality">Learning Modality</label>
                        <select id="li_learning_modality" name="li_learning_modality">
                            <option>BLENDED (COMBINATION)</option>
                            <option>HOMESCHOOLING</option>
                            <option>MODULAR (PRINT)</option>
                            <option>RADIO-BASED TELEVISION</option>
                            <option>EDUCATIONAL TELEVISION</option>
                            <option>MODULAR (DIGITAL)</option>
                            <option>ONLINE</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>2. Student Personal Information</h2>
                <div class="grid">
                    <div>
                        <label for="pi_last_name">Last Name</label>
                        <input type="text" id="pi_last_name" name="pi_last_name" required>
                    </div>
                    <div>
                        <label for="pi_first_name">First Name</label>
                        <input type="text" id="pi_first_name" name="pi_first_name" required>
                    </div>
                    <div>
                        <label for="pi_middle_name">Middle Name</label>
                        <input type="text" id="pi_middle_name" name="pi_middle_name">
                    </div>
                    <div>
                        <label for="pi_extension">Extension</label>
                        <input type="text" id="pi_extension" name="pi_extension">
                    </div>
                    <div>
                        <label for="pi_birth_date">Birth Date</label>
                        <input type="date" id="pi_birth_date" name="pi_birth_date">
                    </div>
                    <div>
                        <label for="pi_sex">Sex</label>
                        <select id="pi_sex" name="pi_sex">
                            <option>MALE</option>
                            <option>FEMALE</option>
                        </select>
                    </div>
                    <div>
                        <label for="pi_place_of_birth">Place of Birth</label>
                        <input type="text" id="pi_place_of_birth" name="pi_place_of_birth">
                    </div>
                    <div>
                        <label for="pi_learning_classification">Learning Classification</label>
                        <select id="pi_learning_classification" name="pi_learning_classification">
                            <option>GRADED</option>
                            <option>NON-GRADED</option>
                        </select>
                    </div>
                    <div>
                        <label for="pi__attended_early_learning_program_name">Early Learning Program</label>
                        <input type="text" id="pi__attended_early_learning_program_name" name="pi__attended_early_learning_program_name">
                    </div>
                    <div>
                        <label for="pi_mother_tongue_id">Mother Tongue</label>
                        <select id="pi_mother_tongue_id" name="pi_mother_tongue_id"></select>
                    </div>
                    <div>
                        <label for="pi_religion_id">Religion</label>
                        <select id="pi_religion_id" name="pi_religion_id"></select>
                    </div>
                    <div>
                        <label for="ac_indigenous_group_id">Indigenous Group</label>
                        <select id="ac_indigenous_group_id" name="ac_indigenous_group_id"></select>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>3. Address</h2>
                <div class="grid">
                    <div>
                        <label for="ca_house_number">Current House Number</label>
                        <input type="text" id="ca_house_number" name="ca_house_number">
                    </div>
                    <div>
                        <label for="ca_street_name">Current Street Name</label>
                        <input type="text" id="ca_street_name" name="ca_street_name">
                    </div>
                    <div>
                        <label for="ca_barangay">Current Barangay</label>
                        <input type="text" id="ca_barangay" name="ca_barangay">
                    </div>
                    <div>
                        <label for="ca_municipality">Current Municipality</label>
                        <input type="text" id="ca_municipality" name="ca_municipality">
                    </div>
                    <div>
                        <label for="ca_provice">Current Province</label>
                        <input type="text" id="ca_provice" name="ca_provice">
                    </div>
                    <div>
                        <label for="ca_country">Current Country</label>
                        <input type="text" id="ca_country" name="ca_country">
                    </div>
                    <div>
                        <label for="ca_zipcode">Current Zipcode</label>
                        <input type="number" id="ca_zipcode" name="ca_zipcode">
                    </div>
                    <div>
                        <label for="ca_address_status">Current Address Status</label>
                        <select id="ca_address_status" name="ca_address_status">
                            <option>Rental</option>
                            <option>Owned</option>
                            <option>Living With Relatives</option>
                            <option>Inherited</option>
                        </select>
                    </div>
                    <div>
                        <label for="pa_house_number">Parent/Guardian House Number</label>
                        <input type="text" id="pa_house_number" name="pa_house_number">
                    </div>
                    <div>
                        <label for="pa_street_name">Parent/Guardian Street Name</label>
                        <input type="text" id="pa_street_name" name="pa_street_name">
                    </div>
                    <div>
                        <label for="pa_barangay">Parent/Guardian Barangay</label>
                        <input type="text" id="pa_barangay" name="pa_barangay">
                    </div>
                    <div>
                        <label for="pa_municipality">Parent/Guardian Municipality</label>
                        <input type="text" id="pa_municipality" name="pa_municipality">
                    </div>
                    <div>
                        <label for="pa_province">Parent/Guardian Province</label>
                        <input type="text" id="pa_province" name="pa_province">
                    </div>
                    <div>
                        <label for="pa_country">Parent/Guardian Country</label>
                        <input type="text" id="pa_country" name="pa_country">
                    </div>
                    <div>
                        <label for="pa_zip_code">Parent/Guardian Zipcode</label>
                        <input type="number" id="pa_zip_code" name="pa_zip_code">
                    </div>
                    <div>
                        <label for="pa_address_status">Parent/Guardian Address Status</label>
                        <select id="pa_address_status" name="pa_address_status">
                            <option>Rental</option>
                            <option>Owned</option>
                            <option>Living With Relatives</option>
                            <option>Inherited</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>4. Medical Information</h2>
                <div class="grid">
                    <div>
                        <label for="mf_a_medicine">Medicine Allergies</label>
                        <input type="text" id="mf_a_medicine" name="mf_a_medicine">
                    </div>
                    <div>
                        <label for="mf_a_pollen">Pollen Allergies</label>
                        <input type="text" id="mf_a_pollen" name="mf_a_pollen">
                    </div>
                    <div>
                        <label for="mf_a_food">Food Allergies</label>
                        <input type="text" id="mf_a_food" name="mf_a_food">
                    </div>
                    <div>
                        <label for="mf_a_others">Other Allergies</label>
                        <input type="text" id="mf_a_others" name="mf_a_others">
                    </div>
                    <div class="full-width">
                        <label for="mf_o_medical_conditions">Medical Conditions (comma-separated)</label>
                        <input type="text" id="mf_o_medical_conditions" name="mf_o_medical_conditions" placeholder="ASTHMA, DIABETES MELLITUS">
                    </div>
                    <div class="full-width">
                        <label for="mf_o_others">Other Medical Conditions</label>
                        <input type="text" id="mf_o_others" name="mf_o_others">
                    </div>
                    <div>
                        <label for="mf_sh_surgery_date">Surgery Date</label>
                        <input type="date" id="mf_sh_surgery_date" name="mf_sh_surgery_date">
                    </div>
                    <div>
                        <label for="mf_sh_hospital_name">Hospital Name</label>
                        <input type="text" id="mf_sh_hospital_name" name="mf_sh_hospital_name">
                    </div>
                    <div>
                        <label for="mf_sh_bodypart_affected">Body Part Affected</label>
                        <input type="text" id="mf_sh_bodypart_affected" name="mf_sh_bodypart_affected">
                    </div>
                    <div>
                        <label for="mf_tm_type">Therapy Type</label>
                        <input type="text" id="mf_tm_type" name="mf_tm_type">
                    </div>
                    <div>
                        <label for="mf_tm_dosage_schedule">Dosage / Schedule</label>
                        <input type="text" id="mf_tm_dosage_schedule" name="mf_tm_dosage_schedule">
                    </div>
                    <div>
                        <label for="mf_mc_cancer_type">Cancer Type</label>
                        <input type="text" id="mf_mc_cancer_type" name="mf_mc_cancer_type">
                    </div>
                    <div class="full-width">
                        <label for="mf_mc_others">Other Chronic Conditions</label>
                        <input type="text" id="mf_mc_others" name="mf_mc_others">
                    </div>
                    <div class="full-width">
                        <label for="mf_o_pertinent_information">Pertinent Medical Information</label>
                        <textarea id="mf_o_pertinent_information" name="mf_o_pertinent_information"></textarea>
                    </div>
                    <div>
                        <label for="mf_exposure_c_v">COVID Exposure</label>
                        <select id="mf_exposure_c_v" name="mf_exposure_c_v">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>5. Parent / Guardian Information</h2>
                <div class="grid">
                    <div>
                        <label for="fi_last_name">Father Last Name</label>
                        <input type="text" id="fi_last_name" name="fi_last_name">
                    </div>
                    <div>
                        <label for="fi_first_name">Father First Name</label>
                        <input type="text" id="fi_first_name" name="fi_first_name">
                    </div>
                    <div>
                        <label for="fi_middle_name">Father Middle Name</label>
                        <input type="text" id="fi_middle_name" name="fi_middle_name">
                    </div>
                    <div>
                        <label for="fi_contact_number">Father Contact Number</label>
                        <input type="text" id="fi_contact_number" name="fi_contact_number">
                    </div>
                    <div>
                        <label for="fi_occupation">Father Occupation</label>
                        <input type="text" id="fi_occupation" name="fi_occupation">
                    </div>
                    <div>
                        <label for="fi_relationship_status">Father Relationship Status</label>
                        <input type="text" id="fi_relationship_status" name="fi_relationship_status">
                    </div>
                    <div class="full-width">
                        <label for="fi_communication">Father Communication</label>
                        <input type="text" id="fi_communication" name="fi_communication">
                    </div>
                    <div>
                        <label for="mi_last_name">Mother Last Name</label>
                        <input type="text" id="mi_last_name" name="mi_last_name">
                    </div>
                    <div>
                        <label for="mi_first_name">Mother First Name</label>
                        <input type="text" id="mi_first_name" name="mi_first_name">
                    </div>
                    <div>
                        <label for="mi_middle_name">Mother Middle Name</label>
                        <input type="text" id="mi_middle_name" name="mi_middle_name">
                    </div>
                    <div>
                        <label for="mi_contact_number">Mother Contact Number</label>
                        <input type="text" id="mi_contact_number" name="mi_contact_number">
                    </div>
                    <div>
                        <label for="mi_occupation">Mother Occupation</label>
                        <input type="text" id="mi_occupation" name="mi_occupation">
                    </div>
                    <div>
                        <label for="mi_relationship_status">Mother Relationship Status</label>
                        <input type="text" id="mi_relationship_status" name="mi_relationship_status">
                    </div>
                    <div class="full-width">
                        <label for="mi_communication">Mother Communication</label>
                        <input type="text" id="mi_communication" name="mi_communication">
                    </div>
                    <div>
                        <label for="gi_last_name">Guardian Last Name</label>
                        <input type="text" id="gi_last_name" name="gi_last_name">
                    </div>
                    <div>
                        <label for="gi_first_name">Guardian First Name</label>
                        <input type="text" id="gi_first_name" name="gi_first_name">
                    </div>
                    <div>
                        <label for="gi_middle_name">Guardian Middle Name</label>
                        <input type="text" id="gi_middle_name" name="gi_middle_name">
                    </div>
                    <div>
                        <label for="gi_contact_number">Guardian Contact Number</label>
                        <input type="text" id="gi_contact_number" name="gi_contact_number">
                    </div>
                    <div>
                        <label for="gi_occupation">Guardian Occupation</label>
                        <input type="text" id="gi_occupation" name="gi_occupation">
                    </div>
                    <div>
                        <label for="gi_relationship_status">Guardian Relationship Status</label>
                        <input type="text" id="gi_relationship_status" name="gi_relationship_status">
                    </div>
                    <div class="full-width">
                        <label for="gi_communication">Guardian Communication</label>
                        <input type="text" id="gi_communication" name="gi_communication">
                    </div>
                    <div class="full-width">
                        <label for="ec_to_contact">Emergency Contact</label>
                        <select id="ec_to_contact" name="ec_to_contact">
                            <option>FATHER</option>
                            <option>MOTHER</option>
                            <option>GUARDIAN</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>6. Special Needs</h2>
                <div class="grid">
                    <div>
                        <label for="snep_a1_diagnosis">Diagnosis</label>
                        <input type="text" id="snep_a1_diagnosis" name="snep_a1_diagnosis">
                    </div>
                    <div>
                        <label for="snep_a1_sub_shpcd">SHPCD Subtype</label>
                        <select id="snep_a1_sub_shpcd" name="snep_a1_sub_shpcd">
                            <option>CANCER</option>
                            <option>NON-CANCER</option>
                        </select>
                    </div>
                    <div>
                        <label for="snep_a1_sub_vi">Visual Impairment Subtype</label>
                        <select id="snep_a1_sub_vi" name="snep_a1_sub_vi">
                            <option>BLIND</option>
                            <option>LOW-VISION</option>
                        </select>
                    </div>
                    <div class="full-width">
                        <label for="snep_a2_manifestations">Manifestations</label>
                        <input type="text" id="snep_a2_manifestations" name="snep_a2_manifestations" placeholder="Describe manifestations">
                    </div>
                    <div class="full-width">
                        <label for="snep_pwd_id">PWD</label>
                        <select id="snep_pwd_id" name="snep_pwd_id">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit">Submit Enrollment</button>
            <div id="status" class="status" style="display:none;"></div>
        </form>
    </div>

    <script>
        const apiBase = '../api/index.php?request=';
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
    </script>
</body>
</html>
