<!-- Section 1: Enrollment Details -->
<div class="section">
    <h2>1. Enrollment Details</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="ed_grade_level" class="form-label">Grade Level</label>
            <input type="text" id="ed_grade_level" name="ed_grade_level" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="ed_lrn" class="form-label">LRN</label>
            <input type="number" id="ed_lrn" name="ed_lrn" class="form-control" required>
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
        <div class="col-md-6 mb-3">
            <label for="li_learning_modality" class="form-label">Learning Modality</label>
            <select id="li_learning_modality" name="li_learning_modality" class="form-select">
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
