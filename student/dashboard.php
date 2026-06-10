<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$user = currentUser();
$db = getDB();

$stmt = $db->prepare('SELECT COUNT(*) FROM exam_submissions WHERE student_id = ?');
$stmt->execute([$user['id']]);
$hasSubmitted = (int) $stmt->fetchColumn() > 0;

$examSchedule = $db->query("
    SELECT id, title, exam_start_time, exam_end_time
    FROM evaluations
    WHERE is_active = 1
      AND exam_start_time IS NOT NULL
      AND exam_end_time IS NOT NULL
    ORDER BY exam_start_time ASC
")->fetchAll();

$now = new DateTime();

$pageTitle = 'Student Landing Page - EMS';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dashboard-page">
    <div class="dashboard-header">
        <h1>Student Landing Page</h1>
        <p>Welcome, <?= htmlspecialchars($user['full_name']) ?></p>
    </div>

    <?php if (!empty($examSchedule)): ?>
        <section class="exam-schedule-notifications" aria-label="Exam schedule">
            <h2 class="schedule-heading">Exam Schedule Notifications</h2>
            <?php foreach ($examSchedule as $exam):
                $start = new DateTime($exam['exam_start_time']);
                $end = new DateTime($exam['exam_end_time']);
                $status = 'upcoming';
                if ($now >= $start && $now <= $end) {
                    $status = 'active';
                } elseif ($now > $end) {
                    $status = 'ended';
                }
            ?>
                <div class="exam-notification exam-notification-<?= htmlspecialchars($status) ?>">
                    <h3><?= htmlspecialchars($exam['title']) ?></h3>
                    <p><strong>Start:</strong> <?= $start->format('l, M j, Y \a\t g:i A') ?></p>
                    <p><strong>End:</strong> <?= $end->format('l, M j, Y \a\t g:i A') ?></p>
                    <?php if ($status === 'active'): ?>
                        <p class="exam-status-msg">The exam window is open now. You may start your evaluation.</p>
                    <?php elseif ($status === 'upcoming'): ?>
                        <p class="exam-status-msg">This exam has not started yet. Please return at the scheduled start time.</p>
                    <?php else: ?>
                        <p class="exam-status-msg">This exam window has ended.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <div class="dropdown-panel">
        <button type="button" class="dropdown-toggle" id="dashboardDropdown" aria-expanded="false">
            Dashboard Information &#9662;
        </button>
        <div class="dropdown-menu" id="dashboardMenu">
            <div class="dropdown-section">
                <h3>Exam Criteria</h3>
                <ul>
                    <li>Read all questions carefully before answering.</li>
                    <li>Answer within the allocated time for each evaluation.</li>
                    <li>Each question carries marks as indicated by the lecturer.</li>
                    <li>Submit your evaluation only when you have completed all questions.</li>
                    <li>Ensure stable internet connection during the online exam.</li>
                </ul>
            </div>
            <div class="dropdown-section warning">
                <h3>Warning Against Exam Malpractice</h3>
                <ul>
                    <li>No copying from other students or external sources.</li>
                    <li>No use of unauthorized materials or devices during the exam.</li>
                    <li>No impersonation or sharing of login credentials.</li>
                    <li>Malpractice may lead to disqualification and disciplinary action.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="exam.php" class="action-card">
            <h2>Start Exam</h2>
            <ul>
                <li>Evaluation form (uploaded by lecturer)</li>
                <li>Answer questions</li>
                <li>Submit evaluation</li>
            </ul>
        </a>

        <a href="results.php" class="action-card <?= !$hasSubmitted ? 'disabled' : '' ?>" <?= !$hasSubmitted ? 'onclick="return false;"' : '' ?>>
            <h2>View Results</h2>
            <p>Published results from your lecturer</p>
            <?php if (!$hasSubmitted): ?>
                <span class="badge badge-warning">Complete an exam first</span>
            <?php endif; ?>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
