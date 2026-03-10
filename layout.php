<?php
declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once ROOT_PATH . '/config/bootstrap.php';
requireLogin();

$content   = $content  ?? '<p class="text-muted">No content provided.</p>';
$pageTitle = $pageTitle ?? 'Dashboard';
$extraCss  = $extraCss  ?? '';
$extraJs   = $extraJs   ?? '';
?>
<?php require ROOT_PATH . '/includes/Head.php'; ?>

<main class="main" id="top">
    <div class="container-fluid" data-layout="container">
        
        <?php require ROOT_PATH . '/includes/Sidebar.php'; ?>

        <div class="content">
            <?php require_once ROOT_PATH . '/includes/TopNav.php'; ?>
            
            <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
                <div>
                    <h4 class="mb-1 font-sans-serif"><?= h($pageTitle) ?></h4>
                    <?php if (!empty($breadcrumbs)): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= ITAM_BASE_URL ?>/modules/dashboard/index.php">Home</a></li>
                            <?php foreach ($breadcrumbs as $bc): ?>
                                <?php if (!empty($bc['url'])): ?>
                                    <li class="breadcrumb-item"><a href="<?= h($bc['url']) ?>"><?= h($bc['label']) ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?= h($bc['label']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php endif; ?>
                </div>

                <?php if (!empty($headerAction)): ?>
                    <div><?= $headerAction ?></div>
                <?php endif ?>
            </div>

            <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard' && $pageTitle !== 'Login'): ?>
            <div class="row mb-3 mt-1 no-print">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 m-0 fw-semi-bold" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item">
                                <a href="<?= ITAM_BASE_URL ?>/index.php" class="text-decoration-none text-muted nav-hover-scale d-inline-block" title="Dashboard">
                                    <i class="fas fa-home"></i>
                                </a>
                            </li>
                            <?php
                            if (isset($breadcrumbs) && is_array($breadcrumbs)) {
                                foreach ($breadcrumbs as $bc) {
                                    $url = !empty($bc['url']) ? h($bc['url']) : 'javascript:void(0);';
                                    echo '<li class="breadcrumb-item"><a href="' . $url . '" class="text-decoration-none text-muted hover-primary transition-base">' . h($bc['label']) . '</a></li>';
                                }
                            } else {
                                $currentPath = $_SERVER['SCRIPT_NAME'];
                                if (strpos($currentPath, '/modules/asset_inventory/') !== false) {
                                    echo '<li class="breadcrumb-item"><a href="' . ITAM_BASE_URL . '/modules/asset_inventory/index.php" class="text-decoration-none text-muted hover-primary transition-base">Asset Inventory</a></li>';
                                    if (strpos($currentPath, 'category_') !== false) {
                                        echo '<li class="breadcrumb-item"><a href="' . ITAM_BASE_URL . '/modules/asset_inventory/category_index.php" class="text-decoration-none text-muted hover-primary transition-base">Categories</a></li>';
                                    }
                                } elseif (strpos($currentPath, '/modules/lifecycle/') !== false) {
                                    echo '<li class="breadcrumb-item"><a href="' . ITAM_BASE_URL . '/modules/lifecycle/index.php" class="text-decoration-none text-muted hover-primary transition-base">Lifecycle Management</a></li>';
                                } elseif (strpos($currentPath, '/modules/system_logs/') !== false) {
                                    echo '<li class="breadcrumb-item"><a href="' . ITAM_BASE_URL . '/modules/system_logs/index.php" class="text-decoration-none text-muted hover-primary transition-base">System</a></li>';
                                }
                            }
                            ?>
                            <li class="breadcrumb-item active text-primary" aria-current="page">
                                <?= h($pageTitle) ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>


            <?php endif; ?>
            
            <?= $content ?>

            <footer class="footer mt-5">
                <div class="row g-0 justify-content-between fs--1 mt-4 mb-3">
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">IT Asset Management System <span class="d-none d-sm-inline-block">| </span><br class="d-sm-none" /> v<?= defined('ITAM_VERSION') ? ITAM_VERSION : '1.0' ?></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</main>

    <div class="offcanvas offcanvas-end settings-panel border-0" id="settings-offcanvas" tabindex="-1" aria-labelledby="settings-offcanvas">
      <div class="offcanvas-header settings-panel-header bg-shape">
        <div class="z-index-1 py-1 light">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="text-white mb-0 me-2"><span class="fas fa-palette me-2 fs-0"></span>Settings</h5>
            <button class="btn btn-primary btn-sm rounded-pill mt-0 mb-0" data-theme-control="reset" style="font-size:12px"> <span class="fas fa-redo-alt me-1" data-fa-transform="shrink-3"></span>Reset</button>
          </div>
          <p class="mb-0 fs--1 text-white opacity-75"> Set customized style</p>
        </div>
        <button class="btn-close btn-close-white z-index-1 mt-0" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body scrollbar-overlay px-x1 h-100" id="themeController">
        <h5 class="fs-0">Color Scheme</h5>
        <p class="fs--1">Choose the perfect color mode for your app.</p>
        <div class="btn-group d-block w-100 btn-group-navbar-style">
          <div class="row gx-2">
            <div class="col-6">
              <input class="btn-check" id="themeSwitcherLight" name="theme-color" type="radio" value="light" data-theme-control="theme" />
              <label class="btn d-inline-block btn-navbar-style fs--1" for="themeSwitcherLight"> <span class="hover-overlay mb-2 rounded d-block"><img class="img-fluid img-prototype mb-0" src="/assets/img/generic/falcon-mode-default.jpg" alt=""/></span><span class="label-text">Light</span></label>
            </div>
            <div class="col-6">
              <input class="btn-check" id="themeSwitcherDark" name="theme-color" type="radio" value="dark" data-theme-control="theme" />
              <label class="btn d-inline-block btn-navbar-style fs--1" for="themeSwitcherDark"> <span class="hover-overlay mb-2 rounded d-block"><img class="img-fluid img-prototype mb-0" src="/assets/img/generic/falcon-mode-dark.jpg" alt=""/></span><span class="label-text"> Dark</span></label>
            </div>
          </div>
        </div>

        <hr />
        <div class="d-flex justify-content-between">
          <div class="d-flex align-items-start"><img class="me-2" src="/assets/img/icons/arrows-h.svg" width="20" alt="" />
            <div class="flex-1">
              <h5 class="fs-0">Wide Layout</h5>
              
            </div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input ms-0" id="mode-fluid" type="checkbox" data-theme-control="isFluid" />
          </div>
        </div>


        <hr />
        <h5 class="fs-0 d-flex align-items-center">Vertical Navbar Style</h5>
        <p class="fs--1 mb-0">Switch between styles for your vertical navbar </p>
        
        
        <div class="btn-group d-block w-100 btn-group-navbar-style">
          <div class="row gx-2">
            <div class="col-6">
              <input class="btn-check" id="navbar-style-transparent" type="radio" name="navbarStyle" value="transparent" data-theme-control="navbarStyle" />
              <label class="btn d-block w-100 btn-navbar-style fs--1" for="navbar-style-transparent"> <img class="img-fluid img-prototype" src="/assets/img/generic/default.png" alt="" /><span class="label-text"> Transparent</span></label>
            </div>
            <div class="col-6">
              <input class="btn-check" id="navbar-style-inverted" type="radio" name="navbarStyle" value="inverted" data-theme-control="navbarStyle" />
              <label class="btn d-block w-100 btn-navbar-style fs--1" for="navbar-style-inverted"> <img class="img-fluid img-prototype" src="/assets/img/generic/inverted.png" alt="" /><span class="label-text"> Inverted</span></label>
            </div>
            <div class="col-6">
              <input class="btn-check" id="navbar-style-card" type="radio" name="navbarStyle" value="card" data-theme-control="navbarStyle" />
              <label class="btn d-block w-100 btn-navbar-style fs--1" for="navbar-style-card"> <img class="img-fluid img-prototype" src="/assets/img/generic/card.png" alt="" /><span class="label-text"> Card</span></label>
            </div>
            <div class="col-6">
              <input class="btn-check" id="navbar-style-vibrant" type="radio" name="navbarStyle" value="vibrant" data-theme-control="navbarStyle" />
              <label class="btn d-block w-100 btn-navbar-style fs--1" for="navbar-style-vibrant"> <img class="img-fluid img-prototype" src="/assets/img/generic/vibrant.png" alt="" /><span class="label-text"> Vibrant</span></label>
            </div>
          </div>
        </div>
        <hr />
      </div>
    </div><a class="card setting-toggle" href="#settings-offcanvas" data-bs-toggle="offcanvas">
      <div class="card-body d-flex align-items-center py-md-2 px-2 py-1">
        <div class="bg-soft-primary position-relative rounded-start" style="height:34px;width:28px">
          <div class="settings-popover"><span class="ripple"><span class="fa-spin position-absolute all-0 d-flex flex-center"><span class="icon-spin position-absolute all-0 d-flex flex-center">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.7369 12.3941L19.1989 12.1065C18.4459 11.7041 18.0843 10.8487 18.0843 9.99495C18.0843 9.14118 18.4459 8.28582 19.1989 7.88336L19.7369 7.59581C19.9474 7.47484 20.0316 7.23291 19.9474 7.03131C19.4842 5.57973 18.6843 4.28943 17.6738 3.20075C17.5053 3.03946 17.2527 2.99914 17.0422 3.12011L16.393 3.46714C15.6883 3.84379 14.8377 3.74529 14.1476 3.3427C14.0988 3.31422 14.0496 3.28621 14.0002 3.25868C13.2568 2.84453 12.7055 2.10629 12.7055 1.25525V0.70081C12.7055 0.499202 12.5371 0.297594 12.2845 0.257272C10.7266 -0.105622 9.16879 -0.0653007 7.69516 0.257272C7.44254 0.297594 7.31623 0.499202 7.31623 0.70081V1.23474C7.31623 2.09575 6.74999 2.8362 5.99824 3.25599C5.95774 3.27861 5.91747 3.30159 5.87744 3.32493C5.15643 3.74527 4.26453 3.85902 3.53534 3.45302L2.93743 3.12011C2.72691 2.99914 2.47429 3.03946 2.30587 3.20075C1.29538 4.28943 0.495411 5.57973 0.0322686 7.03131C-0.051939 7.23291 0.0322686 7.47484 0.242788 7.59581L0.784376 7.8853C1.54166 8.29007 1.92694 9.13627 1.92694 9.99495C1.92694 10.8536 1.54166 11.6998 0.784375 12.1046L0.242788 12.3941C0.0322686 12.515 -0.051939 12.757 0.0322686 12.9586C0.495411 14.4102 1.29538 15.7005 2.30587 16.7891C2.47429 16.9504 2.72691 16.9907 2.93743 16.8698L3.58669 16.5227C4.29133 16.1461 5.14131 16.2457 5.8331 16.6455C5.88713 16.6767 5.94159 16.7074 5.99648 16.7375C6.75162 17.1511 7.31623 17.8941 7.31623 18.7552V19.2891C7.31623 19.4425 7.41373 19.5959 7.55309 19.696C7.64066 19.7589 7.74815 19.7843 7.85406 19.8046C9.35884 20.0925 10.8609 20.0456 12.2845 19.7729C12.5371 19.6923 12.7055 19.4907 12.7055 19.2891V18.7346C12.7055 17.8836 13.2568 17.1454 14.0002 16.7312C14.0496 16.7037 14.0988 16.6757 14.1476 16.6472C14.8377 16.2446 15.6883 16.1461 16.393 16.5227L17.0422 16.8698C17.2527 16.9907 17.5053 16.9504 17.6738 16.7891C18.7264 15.7005 19.4842 14.4102 19.9895 12.9586C20.0316 12.757 19.9474 12.515 19.7369 12.3941ZM10.0109 13.2005C8.1162 13.2005 6.64257 11.7893 6.64257 9.97478C6.64257 8.20063 8.1162 6.74905 10.0109 6.74905C11.8634 6.74905 13.3792 8.20063 13.3792 9.97478C13.3792 11.7893 11.8634 13.2005 10.0109 13.2005Z" fill="#2A7BE4"></path>
                  </svg></span></span></span></div>
        </div><small class="text-uppercase text-primary fw-bold bg-soft-primary py-2 pe-2 ps-1 rounded-end">customize</small>
      </div>
    </a>
    
    <style>
            /* ตกแต่งสีสันของข้อความลิงก์ */
            .hover-primary:hover { color: var(--bs-primary) !important; text-decoration: none; }
            .transition-base { transition: all 0.2s ease-in-out; }
            .nav-hover-scale { transition: transform 0.2s ease-in-out; display: inline-block; }
            .nav-hover-scale:hover { transform: scale(1.1); }
            
            /* 🚀 แก้ปัญหาลูกศร Breadcrumb ไม่แสดงผล โดยใช้การวาดรูป SVG แทน FontAwesome */
            .breadcrumb-item + .breadcrumb-item::before {
                content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%239da9bb' d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E") !important;
                margin-top: 2px;
                padding-right: 0.5rem;
                display: inline-block;
                vertical-align: middle;
            }
    </style>
    
<style>

/* CSS ควบคุมแผงตั้งค่าให้กรอบเป็นสีเขียว */
.theme-selector input[type="radio"] { cursor: pointer; }
.theme-selector input[type="radio"]:checked ~ label { color: #2c7be5; }
.theme-selector label { margin-bottom: 0; }
.theme-selector .preview-img { border-color: #edf2f9 !important; transition: border-color 0.2s; }
.theme-selector input[type="radio"]:checked + label { color: #00d27a; }
.theme-selector label:has(input[type="radio"]:checked) .preview-img { border-color: #00d27a !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const container = document.querySelector('[data-layout="container"]');
    const navbarVertical = document.querySelector('.navbar-vertical');

    // ── 0. ปุ่ม Reset (ล้างค่าทั้งหมดกลับเป็นเริ่มต้น) ──
    const btnReset = document.getElementById('btnResetSettings');
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            localStorage.clear();
            window.location.reload(); 
        });
    }

    // ── 1. ย่อ/ขยาย Sidebar ──
    // (เอาคำสั่ง cloneNode ออก เพื่อให้ปุ่มบนมือถือของ Bootstrap ยังทำงานได้ปกติ)
    const toggleButtons = document.querySelectorAll('.navbar-vertical-toggle');
    if (localStorage.getItem('isNavbarVerticalCollapsed') === 'true') {
        html.classList.add('navbar-vertical-collapsed');
    }
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            html.classList.toggle('navbar-vertical-collapsed');
            localStorage.setItem('isNavbarVerticalCollapsed', html.classList.contains('navbar-vertical-collapsed'));
        });
    });

    // ── 2. ปุ่มเปิด Customizer ──
    // (ลบ JS ส่วนสั่งเปิดทิ้งไปเลยครับ เพื่อให้ HTML data-bs-toggle ทำงานตัวเดียว จะได้ไม่ตีกัน)

    // ── 3. จัดการ Color Scheme (รูปภาพ Light/Dark) ──
const themeRadio = document.getElementById(currentTheme === 'dark' ? 'themeSwitcherDark' : 'themeSwitcherLight');
if (themeRadio) themeRadio.checked = true;

document.querySelectorAll('input[name="theme-color"]').forEach(radio => {
    radio.addEventListener('change', (e) => {{
            const theme = e.target.value;
            localStorage.setItem('theme', theme);
            html.setAttribute('data-bs-theme', theme);
            if (theme === 'dark') html.classList.add('dark'); else html.classList.remove('dark');
            
            // ซิงค์กับปุ่มข้างกระดิ่ง
            const topNavIconLight = document.querySelector('.theme-control-toggle-light');
            const topNavIconDark = document.querySelector('.theme-control-toggle-dark');
            if(topNavIconLight && topNavIconDark) {
                if (theme === 'dark') { 
                    topNavIconLight.classList.add('d-none'); 
                    topNavIconDark.classList.remove('d-none'); 
                } else { 
                    topNavIconLight.classList.remove('d-none'); 
                    topNavIconDark.classList.add('d-none'); 
                }
            }
        });
    });




    // ── 5. จัดการ Fluid Layout ──
    const fluidToggle = document.getElementById('fluidLayoutToggle');
    const isFluid = localStorage.getItem('isFluid') !== 'false';
    if (fluidToggle) fluidToggle.checked = isFluid;
    if (container && !isFluid) { 
        container.classList.remove('container-fluid'); 
        container.classList.add('container'); 
    }

    if (fluidToggle && container) {
        fluidToggle.addEventListener('change', (e) => {
            const fluid = e.target.checked;
            localStorage.setItem('isFluid', fluid);
            container.classList.toggle('container-fluid', fluid);
            container.classList.toggle('container', !fluid);
        });
    }

    // ── 6. จัดการ Vertical Navbar Style ──
    const currentNavStyle = localStorage.getItem('navbarStyle') || 'transparent';
    const activeNavRadio = document.querySelector(`input[name="navbarStyle"][value="${currentNavStyle}"]`);
    if (activeNavRadio) activeNavRadio.checked = true;

    document.querySelectorAll('input[name="navbarStyle"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const style = e.target.value;
            localStorage.setItem('navbarStyle', style);
            if (navbarVertical) {
                navbarVertical.classList.remove('navbar-inverted', 'navbar-card', 'navbar-vibrant', 'navbar-transparent');
                if (style !== 'transparent') navbarVertical.classList.add(`navbar-${style}`);
            }
        });
    });
});
</script>

<?php require ROOT_PATH . '/includes/Footer.php'; ?>