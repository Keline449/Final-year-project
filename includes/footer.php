</main>
<?php if (!empty($showPublicNav)): ?>
    <?php require_once __DIR__ . '/site_footer.php'; ?>
<?php else: ?>
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Evaluation Management System — Cloud Computing Platform</p>
    </div>
</footer>
<?php endif; ?>
<script src="/evaluationmanagement/assets/js/main.js"></script>
</body>
</html>
