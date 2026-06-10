<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$user = currentUser();
$db = getDB();

$stmt = $db->prepare('SELECT COUNT(*) FROM exam_submissions WHERE student_id = ?');
$stmt->execute([$user['id']]);
if ((int) $stmt->fetchColumn() === 0) {
    flash('error', 'You cannot view results until you have written and submitted an exam.');
    header('Location: dashboard.php');
    exit;
}

$results = $db->prepare("
    SELECT e.title, e.results_published, s.submitted_at, s.score, s.grade, u.full_name AS lecturer_name
    FROM exam_submissions s
    JOIN evaluations e ON s.evaluation_id = e.id
    JOIN users u ON e.lecturer_id = u.id
    WHERE s.student_id = ?
    ORDER BY s.submitted_at DESC
");
$results->execute([$user['id']]);
$results = $results->fetchAll();

$pageTitle = 'View Results - Student';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dashboard-page">
    <h1>View Results</h1>
    <p class="subtitle">Results published by your lecturer</p>

    <?php if (empty($results)): ?>
        <p class="empty-state">No submissions found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Evaluation</th>
                    <th>Lecturer</th>
                    <th>Submitted</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><?= htmlspecialchars($r['lecturer_name']) ?></td>
                        <td><?= htmlspecialchars(date('M j, Y H:i', strtotime($r['submitted_at']))) ?></td>
                        <td><?= $r['results_published'] ? ($r['score'] !== null ? htmlspecialchars($r['score']) : '—') : '—' ?></td>
                        <td><?= $r['results_published'] ? htmlspecialchars($r['grade'] ?? '—') : '—' ?></td>
                        <td>
                            <?php if ($r['results_published']): ?>
                                <span class="badge badge-success">Published</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
