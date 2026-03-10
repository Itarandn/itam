<?php
/**
 * ITAM System — Login Page
 * Authenticates users and initialises a secure session.
 *
 * @file login.php
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/bootstrap.php';

// Already logged in — redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ITAM_BASE_URL . '/modules/dashboard/index.php');
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter both username and password.';
    } else {
        $db   = getDB();
// 1. เปลี่ยน :u ตัวหลัง เป็น :email
$stmt = $db->prepare("SELECT id, username, full_name, password_hash, role, is_active FROM users WHERE username = :username OR email = :email LIMIT 1");

// 2. ส่งค่า username ไปให้ทั้งสองตัวแปร
$stmt->execute([
    ':username' => $username,
    ':email'    => $username
]);
        $user = $stmt->fetch();

        if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // Update last login timestamp
            $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id")
               ->execute([':id' => $user['id']]);

            // Audit log
            logAuditEvent('user.login', 'auth', 'user', (int)$user['id']);

            $redirect = filter_var($_GET['redirect'] ?? '', FILTER_SANITIZE_URL);
            $redirect = $redirect && str_starts_with($redirect, '/') ? $redirect : ITAM_BASE_URL . '/modules/dashboard/index.php';

            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid credentials or account is disabled.';
            // Log failed attempt
            error_log("[ITAM AUTH FAIL] Username: $username from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= ITAM_APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">


    <!-- ===============================================-->
    <!--ICON-FontAwesome 6.5.1---->
    <!-- ===============================================-->
    <link rel="stylesheet" href="/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/vendors/fontawesome/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="<?= ITAM_BASE_URL ?>/assets/css/itam.css">
</head>
<body class="login-page">

<div class="login-card">
    <!-- Logo -->
    <div class="text-center mb-4">
        <div class="rounded-3 bg-primary d-inline-flex align-items-center justify-content-center mb-3"
             style="width:52px;height:52px;">
            <i class="fas fa-network-wired text-white fa-lg"></i>
        </div>
        <h4 class="fw-700 mb-0"><?= ITAM_APP_NAME ?></h4>
        <p class="text-muted small">IT Asset Management System</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger small py-2 mb-3">
        <i class="fas fa-exclamation-circle me-1"></i> <?= h($error) ?>
    </div>
    <?php endif ?>

    <form method="POST" action="" novalidate>
        <?= csrfField() ?>

        <div class="mb-3">
            <label class="form-label small fw-600">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-user text-muted" style="font-size:0.85rem;"></i>
                </span>
                <input type="text" name="username"
                       class="form-control border-start-0 ps-0"
                       value="<?= h($username) ?>"
                       placeholder="Enter your username"
                       autofocus autocomplete="username" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-600">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-lock text-muted" style="font-size:0.85rem;"></i>
                </span>
                <input type="password" name="password"
                       class="form-control border-start-0 ps-0"
                       placeholder="Enter your password"
                       autocomplete="current-password" required>
                <button type="button" class="btn btn-outline-secondary border-start-0"
                        onclick="const p=this.previousElementSibling;p.type=p.type==='password'?'text':'password';this.querySelector('i').className=p.type==='password'?'fas fa-eye':'fas fa-eye-slash';">
                    <i class="fas fa-eye" style="font-size:0.8rem;"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="fas fa-sign-in-alt me-1"></i> Sign In
        </button>
    </form>

    <div class="text-center mt-4 text-muted" style="font-size:0.72rem;">
        ITAM v<?= ITAM_VERSION ?> &bull; Secured Session &bull; Internal Use Only
    </div>
</div>

<script src="/vendors/fontawesome/js/bootstrap.bundle.min.js"></script>
</body>
</html>
