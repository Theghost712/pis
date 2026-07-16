<?php
/** @var \App\Core\Session $session */
$session = \App\Core\Session::getInstance();
$role = $session->getUserRole() ?? '';
$currentPage = $currentPage ?? '';
$UserName = $session->getUserName() ?? 'User';
$initials = strtoupper(substr($UserName, 0, 1));

$dashboards = [
    'admin' => '/admin/dashboard',
    'system_admin' => '/admin/dashboard',
    'provider' => '/provider/dashboard',
    'patient' => '/patient/dashboard',
];
$dashboardUrl = $dashboards[$role] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Patient Information Sharing System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php if ($session->isLoggedIn() && in_array($role, ['admin', 'system_admin', 'provider', 'patient'])): ?>
<div class="app-layout">

    <!-- Overlay (click to close sidebar) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="<?= $dashboardUrl ?>">
                <i class="fas fa-heartbeat"></i>
                <span>PISS</span>
            </a>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= $initials ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($UserName) ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $role))) ?></div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <?php if (in_array($role, ['admin', 'system_admin'])): ?>
                <a href="/admin/dashboard" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
                <a href="/admin/users" class="sidebar-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i><span>Users</span>
                </a>
                <a href="/admin/audit" class="sidebar-link <?= $currentPage === 'audit' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i><span>Audit Logs</span>
                </a>
                <a href="/admin/reports" class="sidebar-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i><span>Reports</span>
                </a>
                <a href="/admin/settings" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i><span>Settings</span>
                </a>
            <?php elseif ($role === 'provider'): ?>
                <a href="/provider/dashboard" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
                <a href="/provider/patients" class="sidebar-link <?= $currentPage === 'patients' ? 'active' : '' ?>">
                    <i class="fas fa-user-injured"></i><span>My Patients</span>
                </a>
                <a href="/provider/records" class="sidebar-link <?= $currentPage === 'records' ? 'active' : '' ?>">
                    <i class="fas fa-file-medical"></i><span>Medical Records</span>
                </a>
                <a href="/provider/referrals" class="sidebar-link <?= $currentPage === 'referrals' ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt"></i><span>Referrals</span>
                </a>
            <?php elseif ($role === 'patient'): ?>
                <a href="/patient/dashboard" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i><span>Dashboard</span>
                </a>
                <a href="/patient/profile" class="sidebar-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i><span>My Profile</span>
                </a>
                <a href="/patient/records" class="sidebar-link <?= $currentPage === 'records' ? 'active' : '' ?>">
                    <i class="fas fa-file-medical"></i><span>Medical Records</span>
                </a>
                <a href="/consent" class="sidebar-link <?= $currentPage === 'consent' ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt"></i><span>Consent</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="/logout" class="sidebar-link sidebar-logout">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-right">
                <span class="topbar-greeting">Welcome, <?= htmlspecialchars($UserName) ?></span>
                <a href="/logout" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </header>
        <main class="app-content">
            <?php if ($session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($session->getFlash('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($session->hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($session->getFlash('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($session->hasFlash('info')): ?>
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= htmlspecialchars($session->getFlash('info')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div id="notification-area"></div>
            <?= $content ?? '' ?>
        </main>
    </div>
</div>
<?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-heartbeat me-2"></i>PISS
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/login"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/register"><i class="fas fa-user-plus me-1"></i>Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mt-4">
        <?php if ($session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($session->getFlash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($session->getFlash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($session->hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= htmlspecialchars($session->getFlash('info')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div id="notification-area"></div>
        <?= $content ?? '' ?>
    </main>
    <footer class="bg-white border-top py-3 mt-5">
        <div class="container text-center text-muted">
            <small>&copy; <?= date('Y') ?> Patient Information Sharing System. All rights reserved.</small>
        </div>
    </footer>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/main.js"></script>
<?php if ($session->isLoggedIn()): ?>
<script>
(function() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('visible');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('visible');
        document.body.classList.remove('sidebar-open');
    }

    toggle.addEventListener('click', function() {
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
        link.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
