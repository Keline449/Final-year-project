<?php
session_start();
session_destroy();
header('Location: /evaluationmanagement/index.php');
exit;
