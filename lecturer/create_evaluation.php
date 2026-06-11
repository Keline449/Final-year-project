<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('lecturer');

$user = currentUser();
$db = getDB();
$flash = getFlash();

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $examStart = trim($_POST['exam_start_time'] ?? '') ?: null;
        $examEnd = trim($_POST['exam_end_time'] ?? '') ?: null;
        $formFile = null;

        if (!empty($_FILES['form_file']['name'])) {
            $ext = strtolower(pathinfo($_FILES['form_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx', 'txt'], true)) {
                $formFile = uniqid('form_') . '.' . $ext;
                move_uploaded_file($_FILES['form_file']['tmp_name'], $uploadDir . $formFile);
            }
        }

        if ($title) {
            $stmt = $db->prepare('INSERT INTO evaluations (lecturer_id, title, description, form_file, exam_start_time, exam_end_time) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $title, $description, $formFile, $examStart, $examEnd]);
            flash('success', 'Evaluation created. Add questions below.');
            header('Location: create_evaluation.php?id=' . $db->lastInsertId());
            exit;
        }
    }

    if ($_POST['action'] === 'add_question') {
        $evalId = (int) ($_POST['evaluation_id'] ?? 0);
        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] ?? 'short_answer';
        $points = max(1, (int) ($_POST['points'] ?? 1));
        $options = null;

        if ($type === 'multiple_choice' && !empty($_POST['options'])) {
            $opts = array_filter(array_map('trim', explode("\n", $_POST['options'])));
            $options = json_encode($opts);
        }

        $own = $db->prepare('SELECT id FROM evaluations WHERE id = ? AND lecturer_id = ?');
        $own->execute([$evalId, $user['id']]);
        if ($own->fetch() && $text) {
            $stmt = $db->prepare('INSERT INTO questions (evaluation_id, question_text, question_type, options_json, points) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$evalId, $text, $type, $options, $points]);
            flash('success', 'Question added.');
            header('Location: create_evaluation.php?id=' . $evalId);
            exit;
        }
    }

    if ($_POST['action'] === 'delete_question') {
        $qid = (int) ($_POST['question_id'] ?? 0);
        $db->prepare('DELETE q FROM questions q JOIN evaluations e ON q.evaluation_id = e.id WHERE q.id = ? AND e.lecturer_id = ?')->execute([$qid, $user['id']]);
        flash('success', 'Question removed.');
        header('Location: create_evaluation.php?id=' . (int) ($_POST['evaluation_id'] ?? 0));
        exit;
    }
}

$evalId = (int) ($_GET['id'] ?? 0);
$currentEval = null;
$questions = [];

$evaluations = $db->prepare('SELECT * FROM evaluations WHERE lecturer_id = ? ORDER BY created_at DESC');
$evaluations->execute([$user['id']]);
$evaluations = $evaluations->fetchAll();

if ($evalId) {
    $stmt = $db->prepare('SELECT * FROM evaluations WHERE id = ? AND lecturer_id = ?');
    $stmt->execute([$evalId, $user['id']]);
    $currentEval = $stmt->fetch();
    if ($currentEval) {
        $q = $db->prepare('SELECT * FROM questions WHERE evaluation_id = ? ORDER BY sort_order, id');
        $q->execute([$evalId]);
        $questions = $q->fetchAll();
    }
}

$pageTitle = 'Create Evaluation - Lecturer';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dashboard-page">
    <h1>Create Evaluation</h1>
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2>Upload Evaluation Form</h2>
        <form method="post" enctype="multipart/form-data" class="ems-form">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Exam Start Time</label>
                    <input type="datetime-local" name="exam_start_time">
                    <small>Shown to students on their dashboard</small>
                </div>
                <div class="form-group">
                    <label>Exam End Time</label>
                    <input type="datetime-local" name="exam_end_time">
                    <small>Students are notified when the window closes</small>
                </div>
            </div>
            <div class="form-group">
                <label>Evaluation Form (PDF, DOC, DOCX, TXT)</label>
                <input type="file" name="form_file" accept=".pdf,.doc,.docx,.txt">
            </div>
            <button type="submit" class="btn btn-primary">Create Evaluation</button>
        </form>
    </section>

    <section class="panel">
        <h2>Your Evaluations</h2>
        <ul class="link-list">
            <?php foreach ($evaluations as $ev): ?>
                <li>
                    <a href="?id=<?= (int) $ev['id'] ?>" class="<?= $evalId === (int) $ev['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($ev['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php if ($currentEval): ?>
        <section class="panel">
            <h2>Manage Questions — <?= htmlspecialchars($currentEval['title']) ?></h2>
            <?php if ($currentEval['form_file']): ?>
                <p>Form: <a href="/uploads/<?= htmlspecialchars($currentEval['form_file']) ?>" target="_blank">Download</a></p>
            <?php endif; ?>

            <form method="post" class="ems-form">
                <input type="hidden" name="action" value="add_question">
                <input type="hidden" name="evaluation_id" value="<?= $evalId ?>">
                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="question_text" required rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="question_type" id="questionType">
                            <option value="short_answer">Short Answer</option>
                            <option value="essay">Essay</option>
                            <option value="multiple_choice">Multiple Choice</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="points" value="1" min="1">
                    </div>
                </div>
                <div class="form-group" id="optionsGroup" style="display:none;">
                    <label>Options (one per line)</label>
                    <textarea name="options" rows="4" placeholder="Option A&#10;Option B&#10;Option C"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Question</button>
            </form>

            <h3>Questions (pop up to students when they start exam)</h3>
            <?php if (empty($questions)): ?>
                <p class="empty-state">No questions yet.</p>
            <?php else: ?>
                <ol class="question-list">
                    <?php foreach ($questions as $i => $q): ?>
                        <li>
                            <strong>Q<?= $i + 1 ?>:</strong> <?= htmlspecialchars($q['question_text']) ?>
                            <span class="meta">(<?= htmlspecialchars($q['question_type']) ?>, <?= (int) $q['points'] ?> pts)</span>
                            <form method="post" class="inline-form" onsubmit="return confirm('Delete this question?');">
                                <input type="hidden" name="action" value="delete_question">
                                <input type="hidden" name="question_id" value="<?= (int) $q['id'] ?>">
                                <input type="hidden" name="evaluation_id" value="<?= $evalId ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>View Results</h2>
            <a href="view_results.php?id=<?= $evalId ?>" class="btn btn-outline">View Submissions for this Evaluation</a>
        </section>
    <?php endif; ?>

    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
