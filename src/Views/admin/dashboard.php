<?php
$totalUsers = $totalUsers ?? 0;
$totalPatients = $totalPatients ?? 0;
$totalProviders = $totalProviders ?? 0;
$totalRecords = $totalRecords ?? 0;
$recentAuditLogs = $recentAuditLogs ?? [];
$systemHealth = $systemHealth ?? ['database' => 'healthy', 'storage' => 'healthy', 'uptime' => '99.9%'];
?>
<div class="dash-heading">
    <h2>Admin Dashboard</h2>
    <p>System overview and management.</p>
</div>

<div class="metric-cards">
    <div class="metric-card" style="border-left-color:#4fc3f7;">
        <div class="metric-card-label">Total Users</div>
        <div class="metric-card-value"><?= (int)$totalUsers ?></div>
    </div>
    <div class="metric-card" style="border-left-color:#66bb6a;">
        <div class="metric-card-label">Patients</div>
        <div class="metric-card-value"><?= (int)$totalPatients ?></div>
    </div>
    <div class="metric-card" style="border-left-color:#ab47bc;">
        <div class="metric-card-label">Providers</div>
        <div class="metric-card-value"><?= (int)$totalProviders ?></div>
    </div>
    <div class="metric-card" style="border-left-color:#ffb74d;">
        <div class="metric-card-label">Records</div>
        <div class="metric-card-value"><?= (int)$totalRecords ?></div>
    </div>
</div>

<div class="dashboard-panels">
    <div class="dashboard-panel">
        <h3>Recent Audit Logs</h3>
        <?php if (empty($recentAuditLogs)): ?>
            <p style="color:#999;font-size:14px;">No recent audit entries.</p>
        <?php else: ?>
            <div class="audit-table-wrap">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAuditLogs as $log): ?>
                            <tr>
                                <td style="color:#666;"><?= htmlspecialchars($log['time'] ?? '') ?></td>
                                <td style="color:#333;font-weight:500;"><?= htmlspecialchars($log['user'] ?? '') ?></td>
                                <td style="color:#333;"><?= htmlspecialchars($log['action'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-panel">
        <h3>System Health</h3>
        <div class="health-items">
            <div class="health-item">
                <span class="health-item-label">Database</span>
                <?php if (($systemHealth['database'] ?? '') === 'healthy'): ?>
                    <span class="health-badge health-badge-ok">Healthy</span>
                <?php else: ?>
                    <span class="health-badge health-badge-down">Down</span>
                <?php endif; ?>
            </div>
            <div class="health-item">
                <span class="health-item-label">Storage</span>
                <?php if (($systemHealth['storage'] ?? '') === 'healthy'): ?>
                    <span class="health-badge health-badge-ok">Healthy</span>
                <?php else: ?>
                    <span class="health-badge health-badge-down">Down</span>
                <?php endif; ?>
            </div>
            <div class="health-item">
                <span class="health-item-label">Uptime</span>
                <span style="font-size:14px;font-weight:700;color:#333;"><?= htmlspecialchars($systemHealth['uptime'] ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
</div>
