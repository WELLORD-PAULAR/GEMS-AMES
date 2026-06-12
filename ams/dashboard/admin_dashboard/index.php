<?php

require_once __DIR__ . '/../../login/SessionManager.php';
require_once __DIR__ . '/../../config/config.php';

SessionManager::requireAuth();

$user = SessionManager::getUser();

if (!SessionManager::hasRole('ADMIN')) {
    header('Location: ../teacher_dashboard/');
    exit;
}

// Enrollment statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'verified' => 0,
    'rejected' => 0,
];

try {
    $stmt = $pdo->query("SELECT verification, COUNT(*) AS count FROM enrollment2 GROUP BY verification");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = strtoupper(trim($row['verification'] ?? ''));
        switch ($status) {
            case 'VERIFIED':
                $stats['verified'] += (int) $row['count'];
                break;
            case 'REJECTED':
                $stats['rejected'] += (int) $row['count'];
                break;
            default:
                $stats['pending'] += (int) $row['count'];
                break;
        }
        $stats['total'] += (int) $row['count'];
    }
} catch (PDOException $e) {
    // keep zeros on failure; optionally log $e->getMessage() in a real app
}

// Generate avatar initials from username
$initials = strtoupper(substr($user['username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — GEMS-AMES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/auth.css">
</head>
<body class="admin-page">

    <!-- ── Top Navigation Bar ──────────────────────────────────── -->
    <header class="admin-topbar">
        <div class="topbar-brand">
            <img src="../../style/logo.png" alt="GEMS Logo" class="topbar-logo">
            <span class="topbar-title">GEMS-AMES</span>
        </div>

        <nav>
            <ul class="topbar-nav">
                <li><a href="./">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right:5px;vertical-align:-1px;">
                        <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5v-4h3v4H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354z"/>
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="../../forms/verify/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right:5px;vertical-align:-1px;">
                        <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    </svg>
                    Enrollments
                </a></li>
                <li><a href="../../">
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
                    <a href="../../login/logout.php" class="btn-logout">
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

    <!-- ── Main Content ─────────────────────────────────────────── -->
    <main class="admin-body">

        <!-- Breadcrumb -->
        <nav class="admin-breadcrumb">
            <a href="./">Dashboard</a>
            <span>›</span>
            <span>Home</span>
        </nav>

        <!-- Page Header -->
        <div class="admin-page-header">
            <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?> 👋</h2>
            <p>Here's an overview of the GEMS enrollment management system.</p>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-amber">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                    <div class="stat-label">Pending Enrollments</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($stats['verified']); ?></div>
                    <div class="stat-label">Verified</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                    </svg>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($stats['rejected']); ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Current User Info -->
            <div class="col-lg-5">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                            Account Information
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <ul class="info-list">
                            <li>
                                <span class="info-label">User ID</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['id']); ?></span>
                            </li>
                            <li>
                                <span class="info-label">Username</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                            </li>
                            <li>
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li>
                                <span class="info-label">Role</span>
                                <span class="info-value">
                                    <span class="badge-role badge-admin"><?php echo htmlspecialchars($user['role']); ?></span>
                                </span>
                            </li>
                            <li>
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge-role badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge-role badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Links + System Status -->
            <div class="col-lg-7">
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/>
                            </svg>
                            Quick Links
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="quick-links">
                            <a href="../../forms/verify/" class="quick-link-card">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8"/>
                                    </svg>
                                </div>
                                Verify Enrollments
                            </a>
                            <a href="../../forms/enrollment_form/" class="quick-link-card">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                    </svg>
                                </div>
                                New Enrollment
                            </a>
                            <a href="masterlist/masterlist_selector.php" class="quick-link-card" title="Select columns and download CSV masterlist">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 0 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                                        <path d="M12 8a.5.5 0 0 1-.5.5H5a.5.5 0 0 1 0-1h6.5a.5.5 0 0 1 .5.5zm0 2a.5.5 0 0 1-.5.5H5a.5.5 0 0 1 0-1h6.5a.5.5 0 0 1 .5.5z"/>
                                    </svg>
                                </div>
                                Download Masterlist
                            </a>
                            <a href="sectioning/assign_sections.php" class="quick-link-card" title="Assign students to sections">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
                                    </svg>
                                </div>
                                Assign Sections
                            </a>
                            <span class="quick-link-divider"></span>
                            <a href="teacher_management/" class="quick-link-card" title="Manage teacher profiles">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 16a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-16a4 4 0 1 1 0 8 4 4 0 0 1 0-8zM.5 1a.5.5 0 0 0 0 1h1.110a.5.5 0 0 0 .393-.192l.57-.76h5.693l.57.76a.5.5 0 0 0 .393.192h1.11a.5.5 0 0 0 0-1H14.5a.5.5 0 0 0-.491.408l-.524 3.143a3 3 0 0 1-2.855 2.43h-.015a3 3 0 0 1-2.847-2.431L1.991.408A.5.5 0 0 0 1.5 0H.5z"/>
                                    </svg>
                                </div>
                                Teacher Management
                            </a>
                            <a href="subject_management/" class="quick-link-card" title="Manage academic subjects">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3z"/>
                                    </svg>
                                </div>
                                Subject Management
                            </a>
                            <a href="section_management/" class="quick-link-card" title="Manage classes and sections">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4V2h4v2zm0 5h-5V7h5v2zm0 5h-5v-2h5v2zM4 4a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm0 5a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm0 5a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
                                    </svg>
                                </div>
                                Section Management
                            </a>
                            <a href="teacher_assignments/" class="quick-link-card" title="Assign teachers to subjects and sections">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/>
                                    </svg>
                                </div>
                                Teacher Assignments
                            </a>
                            <span class="quick-link-divider"></span>
                            <a href="../../" class="quick-link-card">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5v-4h3v4H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354z"/>
                                    </svg>
                                </div>
                                AMS Home
                            </a>
                            <a href="../../login/logout.php" class="quick-link-card">
                                <div class="quick-link-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                                    </svg>
                                </div>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                            </svg>
                            System Status
                        </h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="system-notice">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                            </svg>
                            <span>This is the Admin Dashboard for <strong>GEMS-AMES</strong>. The system is running normally. Use the quick links above to manage student enrollments and verifications.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
