<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

$user = currentUser();
$db = getDB();

$evalStmt = $db->prepare("
    SELECT e.*, u.full_name AS lecturer_name,
           (SELECT COUNT(*) FROM exam_submissions s WHERE s.student_id = ? AND s.evaluation_id = e.id) AS submitted
    FROM evaluations e
    JOIN users u ON e.lecturer_id = u.id
    WHERE e.is_active = 1
    ORDER BY e.created_at DESC
");
$evalStmt->execute([$user['id']]);
$evaluations = $evalStmt->fetchAll();

$evaluationId = (int) ($_GET['id'] ?? 0);
$evaluation = null;
$questions = [];

if ($evaluationId) {
    $stmt = $db->prepare('SELECT e.*, u.full_name AS lecturer_name FROM evaluations e JOIN users u ON e.lecturer_id = u.id WHERE e.id = ? AND e.is_active = 1');
    $stmt->execute([$evaluationId]);
    $evaluation = $stmt->fetch();

    if ($evaluation) {
        $q = $db->prepare('SELECT * FROM questions WHERE evaluation_id = ? ORDER BY sort_order, id');
        $q->execute([$evaluationId]);
        $questions = $q->fetchAll();

        $subCheck = $db->prepare('SELECT id FROM exam_submissions WHERE student_id = ? AND evaluation_id = ?');
        $subCheck->execute([$user['id'], $evaluationId]);
        if ($subCheck->fetch()) {
            flash('info', 'You have already submitted this evaluation.');
            header('Location: exam.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluationId && $evaluation) {
    $answers = $_POST['answers'] ?? [];
    if (empty($answers)) {
        flash('error', 'Please answer at least one question.');
    } else {
        $db->beginTransaction();
        try {
            $ins = $db->prepare('INSERT INTO exam_submissions (student_id, evaluation_id) VALUES (?, ?)');
            $ins->execute([$user['id'], $evaluationId]);
            $submissionId = (int) $db->lastInsertId();

            $ansStmt = $db->prepare('INSERT INTO answers (submission_id, question_id, answer_text) VALUES (?, ?, ?)');
            foreach ($answers as $qid => $text) {
                $ansStmt->execute([$submissionId, (int) $qid, trim($text)]);
            }
            $db->commit();
            flash('success', 'Evaluation submitted successfully!');
            header('Location: dashboard.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            flash('error', 'Submission failed. Please try again.');
        }
    }
}

$pageTitle = 'Start Exam - Student';
require_once __DIR__ . '/../includes/header.php';
$flash = getFlash();
?>

<div class="container dashboard-page">
    <h1>Start Exam</h1>
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php if (!$evaluation): ?>
        <p class="subtitle">Select an evaluation uploaded by your lecturer:</p>
        <div class="eval-list">
            <?php if (empty($evaluations)): ?>
                <p class="empty-state">No active evaluations available yet.</p>
            <?php else: ?>
                <?php foreach ($evaluations as $ev): ?>
                    <div class="eval-item">
                        <h3><?= htmlspecialchars($ev['title']) ?></h3>
                        <p>Lecturer: <?= htmlspecialchars($ev['lecturer_name']) ?></p>
                        <?php if ($ev['description']): ?>
                            <p><?= htmlspecialchars($ev['description']) ?></p>
                        <?php endif; ?>
                        <?php if ($ev['submitted']): ?>
                            <span class="badge">Submitted</span>
                        <?php else: ?>
                            <a href="exam.php?id=<?= (int) $ev['id'] ?>" class="btn btn-primary">Begin Evaluation</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
    <?php else: ?>
        <div class="exam-form-wrap">
            <h2><?= htmlspecialchars($evaluation['title']) ?></h2>
            <p>Lecturer: <?= htmlspecialchars($evaluation['lecturer_name']) ?></p>
            <?php if ($evaluation['form_file']): ?>
                <p><a href="/evaluationmanagement/uploads/<?= htmlspecialchars($evaluation['form_file']) ?>" target="_blank" class="btn btn-outline btn-sm">Download Evaluation Form</a></p>
            <?php endif; ?>

            <?php if (empty($questions)): ?>
                <p class="empty-state">No questions added yet by the lecturer.</p>
            <?php else: ?>
                <form method="post" class="ems-form exam-form">
                    <?php foreach ($questions as $i => $q): ?>
                        <div class="question-block">
                            <label><strong>Q<?= $i + 1 ?>.</strong> <?= htmlspecialchars($q['question_text']) ?> <span class="points">(<?= (int) $q['points'] ?> pts)</span></label>
                            <?php
                            $opts = $q['options_json'] ? json_decode($q['options_json'], true) : null;
                            if ($q['question_type'] === 'multiple_choice' && $opts):
                                foreach ($opts as $opt):
                            ?>
                                <label class="radio-label">
                                    <input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>" required>
                                    <?= htmlspecialchars($opt) ?>
                                </label>
                            <?php endforeach; else: ?>
                                <textarea name="answers[<?= (int) $q['id'] ?>]" rows="3" required placeholder="Your answer..."></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Submit Evaluation</button>
                    <a href="exam.php" class="btn btn-outline">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
