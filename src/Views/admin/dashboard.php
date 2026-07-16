<?php
$totalUsers = $totalUsers ?? 0;
$totalPatients = $totalPatients ?? 0;
$totalProviders = $totalProviders ?? 0;
$totalRecords = $totalRecords ?? 0;
$recentAuditLogs = $recentAuditLogs ?? [];
$systemHealth = $systemHealth ?? ['database' => 'healthy', 'storage' => 'healthy', 'uptime' => '99.9%'];
?>
<div style="margin-bottom:32px;">
    <h2 style="font-size:24px;font-weight:700;margin-bottom:4px;">Admin Dashboard</h2>
    <p style="color:#666;font-size:14px;">System overview and management.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #4fc3f7;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Total Users</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$totalUsers ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #66bb6a;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Patients</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$totalPatients ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #ab47bc;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Providers</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$totalProviders ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #ffb74d;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Records</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$totalRecords ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Recent Audit Logs</h3>
        <?php if (empty($recentAuditLogs)): ?>
            <p style="color:#999;font-size:14px;">No recent audit entries.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid #eee;">
                        <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Time</th>
                        <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">User</th>
                        <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAuditLogs as $log): ?>
                        <tr style="border-bottom:1px solid #f5f5f5;">
                            <td style="padding:8px 0;color:#666;"><?= htmlspecialchars($log['time'] ?? '') ?></td>
                            <td style="padding:8px 0;color:#333;"><?= htmlspecialchars($log['user'] ?? '') ?></td>
                            <td style="padding:8px 0;color:#333;"><?= htmlspecialchars($log['action'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">System Health</h3>
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8f9fa;border-radius:8px;">
                <span style="font-size:14px;font-weight:500;">Database</span>
                <?php if (($systemHealth['database'] ?? '') === 'healthy'): ?>
                    <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Healthy</span>
                <?php else: ?>
                    <span style="background:#ffebee;color:#c62828;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Down</span>
                <?php endif; ?>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8f9fa;border-radius:8px;">
                <span style="font-size:14px;font-weight:500;">Storage</span>
                <?php if (($systemHealth['storage'] ?? '') === 'healthy'): ?>
                    <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Healthy</span>
                <?php else: ?>
                    <span style="background:#ffebee;color:#c62828;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Down</span>
                <?php endif; ?>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8f9fa;border-radius:8px;">
                <span style="font-size:14px;font-weight:500;">Uptime</span>
                <span style="font-size:14px;font-weight:700;color:#333;"><?= htmlspecialchars($systemHealth['uptime'] ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
</div>
