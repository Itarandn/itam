<?php
declare(strict_types=1);
define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/bootstrap.php';
requireLogin();

$q = trim($_GET['q'] ?? '');
$results = [
    'assets'    => [],
    'users'     => [],
    'licenses'  => [],
    'locations' => [],
    'vendors'   => []
];

if ($q !== '') {
    $db = getDB();
    // ใส่ % หน้า-หลัง เพื่อค้นหาแบบมีคำนี้อยู่ส่วนใดส่วนหนึ่งของข้อความ (Wildcard Search)
    $searchTerm = "%{$q}%";

    // 1. ค้นหาสินทรัพย์ (Assets) - อัปเดตค้นหาเจาะลึกทุกฟิลด์และเชื่อมตาราง Users
    try {
        $stmtAsset = $db->prepare("
            SELECT a.id, a.asset_tag, a.name, a.status, a.category_id, 
                   a.manufacturer, a.model, a.hostname, u.full_name AS assigned_to_name 
            FROM assets a
            LEFT JOIN users u ON a.assigned_to = u.id
            WHERE a.asset_tag LIKE ? 
               OR a.name LIKE ? 
               OR a.serial_number LIKE ?
               OR a.manufacturer LIKE ?
               OR a.model LIKE ?
               OR a.vendor_sku LIKE ?
               OR a.hostname LIKE ?
               OR u.full_name LIKE ?
               OR u.username LIKE ?
            LIMIT 30
        ");
        // ส่งตัวแปร $searchTerm ไป 9 ตัว ให้ตรงกับจำนวนเครื่องหมาย ? ในคำสั่ง WHERE
        $stmtAsset->execute([
            $searchTerm, $searchTerm, $searchTerm, 
            $searchTerm, $searchTerm, $searchTerm, 
            $searchTerm, $searchTerm, $searchTerm
        ]);
        $results['assets'] = $stmtAsset->fetchAll();
    } catch (PDOException $e) {
        // หากเกิด Error กรณีฐานข้อมูลยังไม่มีคอลัมน์ vendor_sku
        if (strpos($e->getMessage(), 'Unknown column \'a.vendor_sku\'') !== false) {
            die('<div style="padding: 20px; text-align: center; font-family: sans-serif;">
                    <h3 style="color: red;">พบข้อผิดพลาด: ฐานข้อมูลยังไม่มีคอลัมน์ Vendor SKU</h3>
                    <p>กรุณารันคำสั่ง SQL นี้ใน phpMyAdmin ของคุณ:</p>
                    <code style="background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;">
                        ALTER TABLE `assets` ADD COLUMN `vendor_sku` VARCHAR(100) DEFAULT NULL AFTER `model`;
                    </code>
                 </div>');
        } else {
            throw $e;
        }
    }

    // 2. ค้นหาผู้ใช้งาน (Users)
    $stmtUser = $db->prepare("SELECT id, username, full_name, name_th, name_en, department, email FROM users WHERE username LIKE ? OR full_name LIKE ? OR name_th LIKE ? OR name_en LIKE ? OR department LIKE ? OR email LIKE ? LIMIT 20");
    $stmtUser->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $results['users'] = $stmtUser->fetchAll();

    // 3. ค้นหาใบอนุญาตซอฟต์แวร์ (Licenses)
    $stmtLicense = $db->prepare("SELECT id, software_name, license_key, status FROM software_licenses WHERE software_name LIKE ? OR license_key LIKE ? LIMIT 20");
    $stmtLicense->execute([$searchTerm, $searchTerm]);
    $results['licenses'] = $stmtLicense->fetchAll();

    // 4. ค้นหาสถานที่ (Locations)
    $stmtLoc = $db->prepare("SELECT id, location_code, name, address FROM locations WHERE location_code LIKE ? OR name LIKE ? OR address LIKE ? LIMIT 20");
    $stmtLoc->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results['locations'] = $stmtLoc->fetchAll();

    // 5. ค้นหาผู้จำหน่าย (Vendors)
    $stmtVendor = $db->prepare("SELECT id, name FROM vendors WHERE name LIKE ? LIMIT 20");
    $stmtVendor->execute([$searchTerm]);
    $vendors = $stmtVendor->fetchAll();
    foreach ($vendors as &$v) {
        $v['contact_person'] = '-';
        $v['email'] = '-';
    }
    $results['vendors'] = $vendors;
}

$pageTitle = 'ผลการค้นหา: ' . ($q !== '' ? h($q) : 'ทั้งหมด');
$breadcrumbs = [
    ['label' => 'ผลการค้นหา', 'url' => '']
];

ob_start();
?>

<div class="card mb-3 shadow-sm border border-light">
    <div class="card-header bg-light d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
            <i class="fas fa-search fs-1"></i>
        </div>
        <div>
            <h5 class="mb-0 text-primary">ผลการค้นหาในระบบ</h5>
            <?php if ($q !== ''): ?>
                <p class="mb-0 text-muted fs--1">คำค้นหาของคุณ: <span class="fw-bold text-dark">"<?= h($q) ?>"</span></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-body bg-light">
        
        <?php if ($q === ''): ?>
            <div class="text-center py-6 text-muted bg-white rounded-3 border border-2 border-dashed">
                <i class="fas fa-keyboard fs-3 mb-3 text-300 d-block"></i>
                <h5 class="text-500">พร้อมสำหรับการค้นหา</h5>
                <p class="mb-0">ค้นหาได้ทั้ง รหัสสินทรัพย์, ซีเรียล, ผู้ผลิต, ชื่อผู้ใช้ หรือที่อยู่ IP</p>
            </div>
        <?php else: ?>
            
            <?php 
            $totalFound = count($results['assets']) + count($results['users']) + count($results['licenses']) + count($results['locations']) + count($results['vendors']);
            if ($totalFound === 0): 
            ?>
                <div class="text-center py-6 text-muted bg-white rounded-3 border border-2 border-dashed">
                    <i class="fas fa-search-minus fs-3 mb-3 text-warning d-block"></i>
                    <h5 class="text-500">ไม่พบข้อมูลที่ตรงกับ "<?= h($q) ?>"</h5>
                    <p class="mb-0">ลองเปลี่ยนคำค้นหาให้สั้นลง หรือใช้คำที่ใกล้เคียงดูนะครับ</p>
                </div>
            <?php else: ?>
                <div class="row g-3">

                    <?php if (!empty($results['assets'])): ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100 shadow-none border">
                            <div class="card-header bg-soft-info py-2">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-boxes text-info me-2"></i>สินทรัพย์ (Assets) <span class="badge bg-info ms-1"><?= count($results['assets']) ?></span></h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($results['assets'] as $asset): ?>
                                    <a href="<?= ITAM_BASE_URL ?>/modules/asset_inventory/edit.php?id=<?= $asset['id'] ?>" class="list-group-item list-group-item-action transition-base">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-soft-primary me-2 font-monospace" style="width: 80px;"><?= h($asset['asset_tag']) ?></span>
                                                <span class="fw-semi-bold text-dark text-truncate" style="max-width: 200px;"><?= h($asset['name']) ?></span>
                                            </div>
                                            <span class="badge bg-secondary"><?= h($asset['status']) ?></span>
                                        </div>
                                        <div class="text-muted fs--2 mt-1" style="padding-left: 90px;">
                                            <?= $asset['manufacturer'] ? h($asset['manufacturer']) . ' ' . h($asset['model'] ?? '') : '' ?>
                                            <?= $asset['hostname'] ? ' <span class="mx-1">•</span> <i class="fas fa-network-wired me-1"></i>' . h($asset['hostname']) : '' ?>
                                            <?= $asset['assigned_to_name'] ? ' <span class="mx-1">•</span> <i class="fas fa-user-tag me-1"></i>' . h($asset['assigned_to_name']) : '' ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['users'])): ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100 shadow-none border">
                            <div class="card-header bg-soft-success py-2">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-users text-success me-2"></i>พนักงาน (Users) <span class="badge bg-success ms-1"><?= count($results['users']) ?></span></h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($results['users'] as $u): 
                                    $displayName = trim(($u['name_th'] ?? '') ?: (($u['name_en'] ?? '') ?: ($u['full_name'] ?? $u['username'])));
                                ?>
                                    <a href="<?= ITAM_BASE_URL ?>/modules/user_management/user_edit.php?id=<?= $u['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center transition-base">
                                        <div>
                                            <span class="fw-semi-bold text-dark"><?= h($displayName) ?></span>
                                            <small class="text-muted d-block"><i class="fas fa-envelope me-1"></i><?= h($u['email'] ?? 'ไม่มีอีเมล') ?></small>
                                        </div>
                                        <span class="badge badge-soft-success"><?= h($u['department'] ?? 'ไม่มีแผนก') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['licenses'])): ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100 shadow-none border">
                            <div class="card-header bg-soft-warning py-2">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-key text-warning me-2"></i>ซอฟต์แวร์ (Licenses) <span class="badge bg-warning ms-1"><?= count($results['licenses']) ?></span></h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($results['licenses'] as $lic): ?>
                                    <a href="<?= ITAM_BASE_URL ?>/modules/license_management/edit.php?id=<?= $lic['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center transition-base">
                                        <span class="fw-semi-bold text-dark text-truncate" style="max-width: 250px;"><?= h($lic['software_name']) ?></span>
                                        <span class="badge badge-soft-warning font-monospace text-truncate" style="max-width: 120px;" title="<?= h($lic['license_key'] ?? '') ?>"><?= h($lic['license_key'] ?? '-') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['locations'])): ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100 shadow-none border">
                            <div class="card-header bg-soft-danger py-2">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-building text-danger me-2"></i>สถานที่ (Locations) <span class="badge bg-danger ms-1"><?= count($results['locations']) ?></span></h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($results['locations'] as $loc): ?>
                                    <a href="<?= ITAM_BASE_URL ?>/modules/asset_inventory/location_view.php?id=<?= $loc['id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center transition-base">
                                        <span class="badge badge-soft-danger me-2 font-monospace"><?= h($loc['location_code'] ?? 'N/A') ?></span>
                                        <span class="fw-semi-bold text-dark text-truncate"><?= h($loc['name']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($results['vendors'])): ?>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100 shadow-none border">
                            <div class="card-header bg-soft-primary py-2">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-store text-primary me-2"></i>ผู้จำหน่าย (Vendors) <span class="badge bg-primary ms-1"><?= count($results['vendors']) ?></span></h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($results['vendors'] as $vendor): ?>
                                    <a href="<?= ITAM_BASE_URL ?>/modules/asset_inventory/vendor_view.php?id=<?= $vendor['id'] ?>" class="list-group-item list-group-item-action transition-base">
                                        <div class="fw-semi-bold text-dark"><?= h($vendor['name']) ?></div>
                                        <div class="text-muted fs--1 mt-1">
                                            <i class="fas fa-user-tie me-1"></i><?= h($vendor['contact_person'] ?? '-') ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-envelope me-1"></i><?= h($vendor['email'] ?? '-') ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<style>
/* แต่งลิงก์ให้สวยงามเมื่อเอาเมาส์ไปชี้ */
.transition-base:hover {
    background-color: #f8f9fa !important;
    transform: translateX(3px);
    transition: all 0.2s ease-in-out;
}
</style>

<?php
$content = ob_get_clean();
require ROOT_PATH . 'layout.php';
?>