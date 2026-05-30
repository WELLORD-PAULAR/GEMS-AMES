<!-- Section 4: Medical Information -->
<div class="section">
    <h2>4. Medical Information</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="mf_a_medicine" class="form-label">Medicine Allergies</label>
            <input type="text" id="mf_a_medicine" name="mf_a_medicine" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_a_pollen" class="form-label">Pollen Allergies</label>
            <input type="text" id="mf_a_pollen" name="mf_a_pollen" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_a_food" class="form-label">Food Allergies</label>
            <input type="text" id="mf_a_food" name="mf_a_food" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_a_others" class="form-label">Other Allergies</label>
            <input type="text" id="mf_a_others" name="mf_a_others" class="form-control">
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Medical Conditions</label>
            <div class="d-flex flex-wrap gap-3">
                <?php
                $medConditions = [
                    'ERROR OF REFRACTION',
                    'SEIZURE',
                    'ANEMIA',
                    'FRACTURE/DISLOCATION',
                    'ASTHMA',
                    'HEART ILLNESS',
                    'BLEEDING DISORDER',
                ];
                foreach ($medConditions as $condition):
                    $id = 'mf_oc_' . preg_replace('/[^a-z0-9]/', '_', strtolower($condition));
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="mf_o_medical_conditions[]"
                           id="<?= $id ?>"
                           value="<?= htmlspecialchars($condition) ?>">
                    <label class="form-check-label" for="<?= $id ?>">
                        <?= htmlspecialchars($condition) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 mb-3">
            <label for="mf_o_others" class="form-label">Other Medical Conditions</label>
            <input type="text" id="mf_o_others" name="mf_o_others" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_sh_surgery_date" class="form-label">Surgery Date</label>
            <input type="date" id="mf_sh_surgery_date" name="mf_sh_surgery_date" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_sh_hospital_name" class="form-label">Hospital Name</label>
            <input type="text" id="mf_sh_hospital_name" name="mf_sh_hospital_name" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_sh_bodypart_affected" class="form-label">Body Part Affected</label>
            <input type="text" id="mf_sh_bodypart_affected" name="mf_sh_bodypart_affected" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_tm_type" class="form-label">Therapy Type</label>
            <input type="text" id="mf_tm_type" name="mf_tm_type" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_tm_dosage_schedule" class="form-label">Dosage / Schedule</label>
            <input type="text" id="mf_tm_dosage_schedule" name="mf_tm_dosage_schedule" class="form-control">
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Family Medical History / Chronic Conditions</label>
            <div class="d-flex flex-wrap gap-3">
                <?php
                $chronicConditions = [
                    'TUBERCOLOSIS',
                    'DIABETES MELLITUS',
                    'HYPERTENSION',
                    'STROKE/ HEART ATTACK',
                    'DEPRESSION',
                    'KIDNEY PROBLEMS',
                ];
                foreach ($chronicConditions as $condition):
                    $id = 'mf_mc_' . preg_replace('/[^a-z0-9]/', '_', strtolower($condition));
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="mf_mc_conditions[]"
                           id="<?= $id ?>"
                           value="<?= htmlspecialchars($condition) ?>">
                    <label class="form-check-label" for="<?= $id ?>">
                        <?= htmlspecialchars($condition) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="mf_mc_cancer_type" class="form-label">Cancer Type</label>
            <input type="text" id="mf_mc_cancer_type" name="mf_mc_cancer_type" class="form-control">
        </div>
        <div class="col-12 mb-3">
            <label for="mf_mc_others" class="form-label">Other Chronic Conditions</label>
            <input type="text" id="mf_mc_others" name="mf_mc_others" class="form-control">
        </div>
        <div class="col-12 mb-3">
            <label for="mf_o_pertinent_information" class="form-label">Pertinent Medical Information</label>
            <textarea id="mf_o_pertinent_information" name="mf_o_pertinent_information" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label for="mf_exposure_c_v" class="form-label">COVID Exposure</label>
            <select id="mf_exposure_c_v" name="mf_exposure_c_v" class="form-select">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
</div>