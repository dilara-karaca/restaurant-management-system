<?php
require_once __DIR__ . '/../../includes/functions.php';

startSession();

jsonResponse(true, 'Session bilgisi', [
    'session_id' => session_id(),
    'personnel_logged_in' => $_SESSION['personnel_logged_in'] ?? null,
    'personnel_id' => $_SESSION['personnel_id'] ?? null,
    'personnel_user_id' => $_SESSION['personnel_user_id'] ?? null,
    'personnel_username' => $_SESSION['personnel_username'] ?? null,
    'personnel_role' => $_SESSION['personnel_role'] ?? null
]);
