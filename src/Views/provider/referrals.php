<?php
$referrals = $referrals ?? [];
?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">Referrals</h2>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Date</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Patient</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Referred To</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Reason</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($referrals)): ?>
                <tr>
                    <td colspan="5" style="padding:60px 20px;text-align:center;">
                        <div style="font-size:40px;margin-bottom:12px;">🔄</div>
                        <div style="color:#999;font-size:15px;font-weight:600;">No referrals yet</div>
                        <div style="color:#bbb;font-size:13px;margin-top:4px;">Referrals will appear here once created.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($referrals as $referral): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($referral['date'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;font-weight:500;"><?= htmlspecialchars($referral['patient_name'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($referral['referred_to'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($referral['reason'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <?php
                            $statusColors = ['pending' => ['#fff8e1', '#f57f17'], 'accepted' => ['#e8f5e9', '#2e7d32'], 'completed' => ['#e3f2fd', '#1565c0']];
                            $status = $referral['status'] ?? 'pending';
                            $colors = $statusColors[$status] ?? $statusColors['pending'];
                            ?>
                            <span style="background:<?= $colors[0] ?>;color:<?= $colors[1] ?>;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($status) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
