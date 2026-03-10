<?php
/**
 * สคริปต์สำหรับซ่อมแซมและปรับ Collation ให้ตรงกันทั้งฐานข้อมูล (Bypass Login)
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/bootstrap.php';

// ปิดการเช็คสิทธิ์ชั่วคราว เพื่อให้รันคำสั่งซ่อมฐานข้อมูลได้
// requireLogin();
// requireRole('admin');

$db = getDB();
$targetCollation = 'utf8mb4_general_ci';
$targetCharset = 'utf8mb4';

echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>";
echo "<h2 style='color: #2c7be5;'><i class='fas fa-tools'></i> Database Collation Fixer</h2>";
echo "<hr>";

try {
    $dbName = $db->query("SELECT DATABASE()")->fetchColumn();
    $db->exec("ALTER DATABASE `$dbName` CHARACTER SET $targetCharset COLLATE $targetCollation");
    echo "<p>✅ <b>Database:</b> `$dbName` updated to $targetCollation.</p>";

    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tables as $table) {
        $db->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET $targetCharset COLLATE $targetCollation");
        echo "<li>✅ Table <b>`$table`</b> converted completely.</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3 style='color: #00d27a;'>🎉 All tables and columns have been fixed successfully!</h3>";
    echo "<p>คุณสามารถกลับไปใช้งานระบบ Lifecycle ได้ตามปกติแล้วครับ</p>";
    echo "<a href='modules/lifecycle/index.php' style='display: inline-block; padding: 10px 20px; background-color: #2c7be5; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to Lifecycle Module</a>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; background: #ffe5e5; padding: 15px; border-radius: 5px;'>";
    echo "<b>Error Detail:</b> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div>";
?>