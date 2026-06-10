<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('lecturer');

$user = currentUser();
$db = getDB();
$evalId = (int) ($_GET['id'] ?? 0);

$eval = $db->prepare('SELECT * FROM evaluations WHERE id = ? AND lecturer_id = ?');
$eval->execute([$evalId, $user['id']]);
$eval = $eval->fetch();

if (!$eval) {
    header('Location: create_evaluation.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score'], $_POST['submission_id'])) {
    $subId = (int) $_POST['submission_id'];
    $score = (float) $_POST['score'];
    $grade = trim($_POST['grade'] ?? '');
    $db->prepare('UPDATE exam_submissions s JOIN evaluations e ON s.evaluation_id = e.id SET s.score = ?, s.grade = ? WHERE s.id = ? AND e.lecturer_id = ?')
        ->execute([$score, $grade, $subId, $user['id']]);
    flash('success', 'Score updated.');
    header('Location: view_results.php?id=' . $evalId);
    exit;
}

$submissions = $db->prepare("
    SELECT s.*, u.full_name, u.email
    FROM exam_submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.evaluation_id = ?
    ORDER BY s.submitted_at DESC
");
$submissions->execute([$evalId]);
$submissions = $submissions->fetchAll();

$pageTitle = 'View Results - Lecturer';
require_once __DIR__ . '/../includes/header.php';
$flash = getFlash();
?>

<div class="container dashboard-page">
    <h1>View Results — <?= htmlspecialchars($eval['title']) ?></h1>
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (empty($submissions)): ?>
        <p class="empty-state">No student submissions yet.</p>
    <?php else: ?>
        <?php foreach ($submissions as $sub): ?>
            <div class="panel submission-panel">
                <h3><?= htmlspecialchars($sub['full_name']) ?></h3>
                <p class="meta"><?= htmlspecialchars($sub['email']) ?></p>
                <p>Submitted: <?= date('M j, Y H:i', strtotime($sub['submitted_at'])) ?></p>

                <?php
                $ans = $db->prepare('SELECT a.*, q.question_text FROM answers a JOIN questions q ON a.question_id = q.id WHERE a.submission_id = ?');
                $ans->execute([$sub['id']]);
                foreach ($ans->fetchAll() as $a):
                ?>
                    <div class="answer-block">
                        <strong><?= htmlspecialchars($a['question_text']) ?></strong>
                        <p><?= nl2br(htmlspecialchars($a['answer_text'])) ?></p>
                    </div>
                <?php endforeach; ?>

                <form method="post" class="ems-form inline-score-form">
                    <input type="hidden" name="submission_id" value="<?= (int) $sub['id'] ?>">
                    <input type="number" name="score" step="0.01" value="<?= htmlspecialchars($sub['score'] ?? '') ?>" placeholder="Score">
                    <input type="text" name="grade" value="<?= htmlspecialchars($sub['grade'] ?? '') ?>" placeholder="Grade">
                    <button type="submit" class="btn btn-sm btn-primary">Save Score</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="create_evaluation.php?id=<?= $evalId ?>">&larr; Back</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
