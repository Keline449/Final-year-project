<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('lecturer');

$user = currentUser();
$db = getDB();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $evalId = (int) ($_POST['evaluation_id'] ?? 0);

    $own = $db->prepare('SELECT * FROM evaluations WHERE id = ? AND lecturer_id = ?');
    $own->execute([$evalId, $user['id']]);
    $eval = $own->fetch();

    if ($eval && $action === 'publish') {
        $db->prepare('UPDATE evaluations SET results_published = 1 WHERE id = ?')->execute([$evalId]);
        flash('success', 'Results published. Students can now view their results.');
        header('Location: download_results.php');
        exit;
    }

    if ($eval && $action === 'download') {
        $rows = $db->prepare("
            SELECT u.full_name, u.email, s.submitted_at, s.score, s.grade
            FROM exam_submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.evaluation_id = ?
            ORDER BY u.full_name
        ");
        $rows->execute([$evalId]);
        $data = $rows->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="results_' . preg_replace('/[^a-z0-9]/i', '_', $eval['title']) . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Full Name', 'Email', 'Submitted At', 'Score', 'Grade']);
        foreach ($data as $r) {
            fputcsv($out, [$r['full_name'], $r['email'], $r['submitted_at'], $r['score'], $r['grade']]);
        }
        fclose($out);
        exit;
    }
}

$evaluations = $db->prepare('SELECT e.*, (SELECT COUNT(*) FROM exam_submissions s WHERE s.evaluation_id = e.id) AS submission_count FROM evaluations e WHERE lecturer_id = ? ORDER BY created_at DESC');
$evaluations->execute([$user['id']]);
$evaluations = $evaluations->fetchAll();

$pageTitle = 'Download & Publish Results - Lecturer';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dashboard-page">
    <h1>Download Results</h1>
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (empty($evaluations)): ?>
        <p class="empty-state">No evaluations yet.</p>
    <?php else: ?>
        <?php foreach ($evaluations as $ev): ?>
            <div class="panel">
                <h3><?= htmlspecialchars($ev['title']) ?></h3>
                <p><?= (int) $ev['submission_count'] ?> submission(s)
                    <?php if ($ev['results_published']): ?>
                        <span class="badge badge-success">Published</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Not published</span>
                    <?php endif; ?>
                </p>
                <form method="post" class="inline-actions">
                    <input type="hidden" name="evaluation_id" value="<?= (int) $ev['id'] ?>">
                    <button type="submit" name="action" value="download" class="btn btn-outline">Download Results (CSV)</button>
                    <?php if (!$ev['results_published']): ?>
                        <button type="submit" name="action" value="publish" class="btn btn-primary" onclick="return confirm('Publish results? Students who submitted can view them.');">Publish Results</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
