<?php
/**
 * ITAM System — Secure Logout
 * Destroys the session and redirects to login.
 *
 * @file logout.php
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/bootstrap.php';

// Log the logout action before destroying session
if (!empty($_SESSION['user_id'])) {
    logAuditEvent('user.logout', 'auth', 'user', (int)$_SESSION['user_id']);
}

// Destroy session completely
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        false, // No Secure flag — HTTP only server
        $params['httponly']
    );
}
session_destroy();

header('Location: ' . ITAM_BASE_URL . '/login.php');
exit;
