<?php
$totalPatients = $totalPatients ?? 0;
$recentRecordsCount = $recentRecordsCount ?? 0;
$pendingReferrals = $pendingReferrals ?? 0;
$recentActivity = $recentActivity ?? [];
$providerName = $user['name'] ?? 'Provider';
?>
<div style="margin-bottom:32px;">
    <h2 style="font-size:24px;font-weight:700;margin-bottom:4px;">Welcome, Dr. <?= htmlspecialchars(explode(' ', $providerName)[0]) ?>!</h2>
    <p style="color:#666;font-size:14px;">Here's your practice overview.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px;">
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #4fc3f7;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Total Patients</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$totalPatients ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #66bb6a;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Records Added</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$recentRecordsCount ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #ffb74d;">
        <div style="font-size:13px;color:#666;margin-bottom:4px;">Pending Referrals</div>
        <div style="font-size:28px;font-weight:800;color:#1a1a2e;"><?= (int)$pendingReferrals ?></div>
    </div>
</div>

<div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Recent Activity</h3>
    <?php if (empty($recentActivity)): ?>
        <p style="color:#999;font-size:14px;">No recent activity.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="border-bottom:2px solid #eee;">
                    <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Time</th>
                    <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Patient</th>
                    <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Action</th>
                    <th style="text-align:left;padding:8px 0;color:#666;font-weight:600;">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivity as $activity): ?>
                    <tr style="border-bottom:1px solid #f5f5f5;">
                        <td style="padding:10px 0;color:#666;"><?= htmlspecialchars($activity['time'] ?? '') ?></td>
                        <td style="padding:10px 0;color:#333;font-weight:500;"><?= htmlspecialchars($activity['patient'] ?? '') ?></td>
                        <td style="padding:10px 0;"><?= htmlspecialchars($activity['action'] ?? '') ?></td>
                        <td style="padding:10px 0;color:#666;"><?= htmlspecialchars($activity['details'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
