<!-- Section 1: Enrollment Details -->
<div class="section">
    <h2>1. Enrollment Details</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="ed_grade_level" class="form-label">Grade Level</label>
            <select id="ed_grade_level" name="ed_grade_level" class="form-select" required>
                <option value="">-- Select Grade Level --</option>
                <option value="Kindergarten">Kindergarten</option>
                <option value="1">Grade 1</option>
                <option value="2">Grade 2</option>
                <option value="3">Grade 3</option>
                <option value="4">Grade 4</option>
                <option value="5">Grade 5</option>
                <option value="6">Grade 6</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="ed_lrn" class="form-label">LRN</label>
            <input type="number" id="ed_lrn" name="ed_lrn" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="ed_school_year" class="form-label">School Year</label>
            <input type="text" id="ed_school_year" name="ed_school_year" class="form-control" placeholder="2025-2026" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="rl_last_grade_level_completed" class="form-label">Last Grade Level Completed</label>
            <input type="text" id="rl_last_grade_level_completed" name="rl_last_grade_level_completed" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="rl_last_school_year_completed" class="form-label">Last School Year Completed</label>
            <input type="text" id="rl_last_school_year_completed" name="rl_last_school_year_completed" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="rl_school_attended" class="form-label">Last School Attended</label>
            <input type="text" id="rl_school_attended" name="rl_school_attended" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="rl_school_id" class="form-label">Last School ID</label>
            <input type="number" id="rl_school_id" name="rl_school_id" class="form-control">
        </div>
        <input type="hidden" name="user_account_id" value="<?= htmlspecialchars($_SESSION['auth_user']['id'] ?? '') ?>">
        <div class="col-12 mb-3">
            <label class="form-label">Learning Modality</label>
            <div class="row">
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_blended" value="BLENDED (COMBINATION)">
                        <label class="form-check-label" for="li_learning_modality_blended">BLENDED (COMBINATION)</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_homeschooling" value="HOMESCHOOLING">
                        <label class="form-check-label" for="li_learning_modality_homeschooling">HOMESCHOOLING</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_modular_print" value="MODULAR (PRINT)">
                        <label class="form-check-label" for="li_learning_modality_modular_print">MODULAR (PRINT)</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_radio" value="RADIO-BASED TELEVISION">
                        <label class="form-check-label" for="li_learning_modality_radio">RADIO-BASED TELEVISION</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_television" value="EDUCATIONAL TELEVISION">
                        <label class="form-check-label" for="li_learning_modality_television">EDUCATIONAL TELEVISION</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_modular_digital" value="MODULAR (DIGITAL)">
                        <label class="form-check-label" for="li_learning_modality_modular_digital">MODULAR (DIGITAL)</label>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="li_learning_modality[]" id="li_learning_modality_online" value="ONLINE">
                        <label class="form-check-label" for="li_learning_modality_online">ONLINE</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
