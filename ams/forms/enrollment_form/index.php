<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="enrollment.css">
</head>
<body>
    <div class="container">
        <div class="enrollment-container">
            <h1>Student Enrollment Form</h1>
            <p>Complete all sections below to submit a new student enrollment.</p>

            <!-- Success/Error Message Display -->
            <?php
                $success = isset($_GET['success']) && $_GET['success'] == '1';
                $error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
                $enrollmentId = isset($_GET['enrollment_id']) ? urldecode($_GET['enrollment_id']) : null;
            ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">✅ Enrollment Submitted Successfully!</h5>
                    <p class="mb-2">The student enrollment has been recorded in the system.</p>
                    <hr>
                    <p class="mb-0">
                        <strong>Enrollment ID:</strong> <code><?php echo htmlspecialchars($enrollmentId ?? 'N/A'); ?></code>
                    </p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        Clear Message & Enroll Another Student
                    </button>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">❌ Enrollment Failed</h5>
                    <p class="mb-2">There was a problem submitting the enrollment:</p>
                    <p class="mb-0"><strong><?php echo htmlspecialchars($error); ?></strong></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="enrollmentForm" method="POST" action="./process.php">
                <!-- Section 1: Enrollment Details -->
                <?php include 'sections/enrollment_details.php'; ?>

                <!-- Section 2: Student Personal Information -->
                <?php include 'sections/personal_info.php'; ?>

                <!-- Section 3: Address Information -->
                <?php include 'sections/address_info.php'; ?>

                <!-- Section 4: Medical Information -->
                <?php include 'sections/medical_info.php'; ?>

                <!-- Section 5: Parent / Guardian Information -->
                <?php include 'sections/parents_info.php'; ?>

                <!-- Section 6: Special Needs -->
                <?php include 'sections/special_needs_info.php'; ?>

                <button type="submit" class="btn btn-primary btn-lg">Submit Enrollment</button>
                <div id="status" class="status" style="display:none;"></div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="enrollment.js"></script>
</body>
</html>
