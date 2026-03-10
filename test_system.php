<?php
// สร้างไฟล์ test_system.php ไว้ที่หน้าแรกสุดของเว็บ
require_once 'config/bootstrap.php';

echo "<h1>ITAM System Diagnosis</h1>";

// 1. ทดสอบ Database
try {
    $db = getDB();
    $version = $db->query('SELECT version()')->fetchColumn();
    echo "<p style='color:green;'>✅ Database Connected! (MySQL Version: {$version})</p>";
    
    // ลองเช็คว่ามีตาราง users หรือไม่
    $userCount = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "<p style='color:green;'>✅ Table `users` exists. (Found {$userCount} users)</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database Error: " . h($e->getMessage()) . "</p>";
}

// 2. ทดสอบ Session & CSRF
echo "<p style='color:blue;'>ℹ️ Session ID: " . session_id() . "</p>";
echo "<p style='color:blue;'>ℹ️ CSRF Token: " . csrfToken() . "</p>";

// 3. เช็คสถานะ Login
$user = currentUser();
if ($user['id'] > 0) {
    echo "<p style='color:green;'>✅ You are logged in as: <b>" . h($user['name']) . "</b> (Role: " . h($user['role']) . ")</p>";
} else {
    echo "<p style='color:orange;'>⚠️ You are currently NOT logged in.</p>";
}

echo "<hr><a href='login.php'>Go to Login Page</a>";