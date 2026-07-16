<?php
$records = $records ?? [];
$pagination = $pagination ?? ['current' => 1, 'last' => 1];
?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">My Medical Records</h2>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Date</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Type</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Provider</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Title</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="5" style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">No medical records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;transition:background 0.15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['date'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= htmlspecialchars($record['type'] ?? '') ?></span>
                        </td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['provider'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['title'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <a href="/patient/records/<?= (int)($record['id'] ?? 0) ?>" style="display:inline-block;padding:6px 14px;background:#4fc3f7;color:#1a1a2e;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;transition:background 0.2s;" onmouseover="this.style.background='#29b6f6'" onmouseout="this.style.background='#4fc3f7'">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pagination['last'] > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:24px;">
        <?php if ($pagination['current'] > 1): ?>
            <a href="?page=<?= $pagination['current'] - 1 ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Previous</a>
        <?php endif; ?>
        <span style="padding:8px 14px;background:#4fc3f7;color:#1a1a2e;border-radius:6px;font-size:13px;font-weight:600;"><?= $pagination['current'] ?> / <?= $pagination['last'] ?></span>
        <?php if ($pagination['current'] < $pagination['last']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
