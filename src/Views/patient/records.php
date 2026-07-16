<?php
$pageTitle = 'Medical Records';
$dashboardUrl = '/patient/dashboard';
$records = $records ?? [];
ob_start();
?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;"><i class="fas fa-notes-medical text-primary me-2"></i>My Medical Records</h2>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Date</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Type</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Provider</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Title</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="4" style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        No medical records found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars(date('M d, Y', strtotime($record['record_date']))) ?></td>
                        <td style="padding:14px 20px;">
                            <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $record['record_type']))) ?></span>
                        </td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars(($record['first_name'] ?? 'Unknown') . ' ' . ($record['last_name'] ?? '')) ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['title'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
