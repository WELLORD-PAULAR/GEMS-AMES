<?php

require_once __DIR__ . '/../../../login/SessionManager.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../handlers/SectionAssignmentHandler.php';

use AMS\Handlers\SectionAssignmentHandler;

SessionManager::requireAuth();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../../teacher_dashboard/');
    exit;
}

$user = SessionManager::getUser();
$handler = new SectionAssignmentHandler($pdo);
$sections = $handler->getAllSections();

$initials = strtoupper(substr($user['username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Sections — GEMS-AMES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/auth.css">
    <link rel="stylesheet" href="assign_sections.css">
</head>
<body class="admin-page">

    <header class="admin-topbar">
        <div class="topbar-brand">
            <img src="../../../style/logo.png" alt="GEMS Logo" class="topbar-logo">
            <span class="topbar-title">GEMS-AMES</span>
        </div>

        <nav>
            <ul class="topbar-nav">
                <li><a href="../">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right:5px;vertical-align:-1px;">
                        <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5v-4h3v4H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354z"/>
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="../../../forms/verify/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right:5px;vertical-align:-1px;">
                        <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    </svg>
                    Enrollments
                </a></li>
                <li><a href="../../../">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right:5px;vertical-align:-1px;">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    AMS Home
                </a></li>

                <li><span class="topbar-divider"></span></li>

                <li>
                    <div class="topbar-user">
                        <div class="topbar-user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                        <span>Logged in as <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
                    </div>
                </li>

                <li>
                    <a href="../../../login/logout.php" class="btn-logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                            <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="admin-body">

        <nav class="admin-breadcrumb">
            <a href="../">Dashboard</a>
            <span>›</span>
            <span>Assign Sections</span>
        </nav>

        <div class="admin-page-header">
            <h2>Assign Student Sections</h2>
            <p>Select students and assign them to their appropriate sections by grade level.</p>
            <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px; margin-top: 15px; border-radius: 4px; font-size: 13px;">
                <strong>📅 DepEd Three-Term Calendar SY 2026-2027</strong><br>
                1st Term: June-Aug | 2nd Term: Sept-Nov | 3rd Term: Dec-Apr
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-controls">
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by name or LRN...">
                <select id="statusFilter" class="form-select" style="max-width: 200px;">
                    <option value="">All Students</option>
                    <option value="VERIFIED">Verified Only</option>
                    <option value="PROCESSING">Processing</option>
                </select>
                <button class="btn btn-outline-secondary" onclick="resetFilters()">Reset Filters</button>
                <button class="btn btn-primary" onclick="loadStudents()">
                    <span id="loadBtn">Load Students</span>
                    <span id="loadingSpinner" class="loading-spinner" style="display:none; margin-left: 8px;"></span>
                </button>
            </div>
        </div>

        <ul class="nav nav-tabs" id="gradeTabs" role="tablist" style="border-bottom: 2px solid #e0e0e0; margin-bottom: 30px;">
            <li class="nav-item">
                <button class="nav-link active" id="tab-K" onclick="showGradeTab('K')">Grade K</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-1" onclick="showGradeTab('1')">Grade 1</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-2" onclick="showGradeTab('2')">Grade 2</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-3" onclick="showGradeTab('3')">Grade 3</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-4" onclick="showGradeTab('4')">Grade 4</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-5" onclick="showGradeTab('5')">Grade 5</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-6" onclick="showGradeTab('6')">Grade 6</button>
            </li>
        </ul>

        <div id="tabK" class="grade-tab-content active"></div>
        <div id="tab1" class="grade-tab-content"></div>
        <div id="tab2" class="grade-tab-content"></div>
        <div id="tab3" class="grade-tab-content"></div>
        <div id="tab4" class="grade-tab-content"></div>
        <div id="tab5" class="grade-tab-content"></div>
        <div id="tab6" class="grade-tab-content"></div>

        <button id="saveAllBtn" class="save-all-btn" onclick="saveAllSections()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px; vertical-align: -3px;">
                <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
            </svg>
            Save All Changes
        </button>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sections = <?php echo json_encode($sections); ?>;
    </script>
    <script src="assign_sections.js"></script>
</body>
</html>

        <div id="tabK" class="grade-tab-content active"></div>
        <div id="tab1" class="grade-tab-content"></div>
        <div id="tab2" class="grade-tab-content"></div>
        <div id="tab3" class="grade-tab-content"></div>
        <div id="tab4" class="grade-tab-content"></div>
        <div id="tab5" class="grade-tab-content"></div>
        <div id="tab6" class="grade-tab-content"></div>

        <button id="saveAllBtn" class="save-all-btn" onclick="saveAllSections()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px; vertical-align: -3px;">
                <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
            </svg>
            Save All Changes
        </button>

    </main>

</body>
</html>
