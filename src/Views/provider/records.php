<?php $records = $records ?? []; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">Medical Records</h2>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Patient</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Type</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Title</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Date</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="5" style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">No records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($r['record_type'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($r['title'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($r['record_date'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($r['diagnosis'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
