<!-- Section 6: Special Needs -->
<div class="section">
    <h2>6. Special Needs</h2>
    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label">Diagnosis</label>
            <div class="d-flex flex-wrap gap-3">
                <?php
                $diagnoses = [
                    'ADHD' => 'ADHD',
                    'ASD'  => 'ASD',
                    'CP'   => 'CP',
                    'E-B D'  => 'E-B D',
                    'HI'   => 'HI',
                    'ID'   => 'ID',
                    'LD'   => 'LD',
                    'MD'   => 'MD',
                    'O/P H'  => 'O/P H',
                    'S/L D'  => 'S/L D',
                    'SHP/CD' => 'SHP/CD',
                    'VI'   => 'VI',
                    'NONE' => 'NONE',
                ];
                foreach ($diagnoses as $value => $label):
                    $id = 'snep_diag_' . preg_replace('/[^a-z0-9]/', '_', strtolower($value));
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="snep_a1_diagnosis[]"
                           id="<?= $id ?>"
                           value="<?= htmlspecialchars($value) ?>">
                    <label class="form-check-label" for="<?= $id ?>">
                        <?= htmlspecialchars($label) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="snep_a1_sub_shpcd" class="form-label">SHPCD Subtype</label>
            <select id="snep_a1_sub_shpcd" name="snep_a1_sub_shpcd" class="form-select">
                <option value="NONE">NONE</option>
                <option value="CANCER">CANCER</option>
                <option value="NON-CANCER">NON-CANCER</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="snep_a1_sub_vi" class="form-label">Visual Impairment Subtype</label>
            <select id="snep_a1_sub_vi" name="snep_a1_sub_vi" class="form-select">
                <option value="NONE">NONE</option>
                <option value="BLIND">BLIND</option>
                <option value="LOW-VISION">LOW-VISION</option>
            </select>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Manifestations</label>
            <div class="d-flex flex-wrap gap-3">
                <?php
                $manifestations = [
                    'DiAK'     => 'DiAK',
                    'DiC'      => 'DiC',
                    'DiDIB'    => 'DiDIB',
                    'DiH'      => 'DiH',
                    'DiM'      => 'DiM',
                    'DiPAS'    => 'DiPAS',
                    'DiRCPAaU' => 'DiRCPAaU',
                    'DiS'      => 'DiS',
                    'NONE'     => 'NONE',
                ];
                foreach ($manifestations as $value => $label):
                    $id = 'snep_manif_' . preg_replace('/[^a-z0-9]/', '_', strtolower($value));
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="snep_a2_manifestations[]"
                           id="<?= $id ?>"
                           value="<?= htmlspecialchars($value) ?>">
                    <label class="form-check-label" for="<?= $id ?>">
                        <?= htmlspecialchars($label) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 mb-3">
            <label for="snep_pwd_id" class="form-label">PWD</label>
            <select id="snep_pwd_id" name="snep_pwd_id" class="form-select">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
</div>