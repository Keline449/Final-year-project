<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('lecturer');

$user = currentUser();
$pageTitle = 'Lecturer Landing Page - EMS';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dashboard-page">
    <div class="dashboard-header">
        <h1>Lecturer Landing Page</h1>
        <p>Welcome, <?= htmlspecialchars($user['full_name']) ?></p>
    </div>

    <div class="dropdown-panel">
        <button type="button" class="dropdown-toggle" id="dashboardDropdown" aria-expanded="false">
            Dashboard Information &#9662;
        </button>
        <div class="dropdown-menu" id="dashboardMenu">
            <div class="dropdown-section">
                <h3>Exam Criteria</h3>
                <ul>
                    <li>Upload clear evaluation forms for students.</li>
                    <li>Set fair marking schemes for each question.</li>
                    <li>Activate evaluations only when ready for students.</li>
                    <li>Review submissions before publishing results.</li>
                </ul>
            </div>
            <div class="dropdown-section warning">
                <h3>Warning Against Exam Malpractice</h3>
                <ul>
                    <li>Monitor for suspicious submission patterns.</li>
                    <li>Report any suspected malpractice to the academic office.</li>
                    <li>Do not share exam questions before the scheduled time.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="create_evaluation.php" class="action-card">
            <h2>Create Evaluation</h2>
            <ul>
                <li>Upload evaluation form</li>
                <li>Manage questions</li>
                <li>View results</li>
            </ul>
        </a>

        <a href="download_results.php" class="action-card">
            <h2>Download Results</h2>
            <ul>
                <li>Download results</li>
                <li>Publish results</li>
            </ul>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
