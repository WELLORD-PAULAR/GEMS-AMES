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
        <p>Submit student data. Form will be processed server-side and saved to the database.</p>

        <!-- Display messages -->
        <?php
        session_start();
        if (isset($_GET['success'])) {
            echo '<div class="alert success">Enrollment submitted successfully!</div>';
            unset($_SESSION['enrollment_result']);
        } elseif (isset($_GET['error'])) {
            echo '<div class="alert error">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

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

            <button type="submit">Submit Enrollment</button>
            <div id="status" class="status" style="display:none;"></div>
        </form>
    </div>
    <script src="enrollment.js"></script>
</body>
</html>
