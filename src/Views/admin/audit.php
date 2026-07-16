<?php
$auditLogs = $auditLogs ?? [];
$pagination = $pagination ?? ['current' => 1, 'last' => 1];
$actionFilter = $actionFilter ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$actions = ['login', 'logout', 'create', 'read', 'update', 'delete', 'grant_consent', 'revoke_consent'];
?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">Audit Logs</h2>
</div>

<div style="background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:20px;">
    <form method="GET" action="/admin/audit" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:4px;">Action</label>
            <select name="action" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;background:#fff;">
                <option value="">All Actions</option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= $a ?>" <?= $actionFilter === $a ? 'selected' : '' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $a))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:4px;">From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:4px;">To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
        </div>
        <button type="submit" style="padding:8px 20px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;">Filter</button>
    </form>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">Timestamp</th>
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">User</th>
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">Action</th>
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">Resource</th>
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">Description</th>
                <th style="text-align:left;padding:14px 16px;font-size:13px;font-weight:600;color:#666;">IP</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($auditLogs)): ?>
                <tr>
                    <td colspan="6" style="padding:40px 16px;text-align:center;color:#999;font-size:14px;">No audit logs found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($auditLogs as $log): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:12px 16px;font-size:13px;color:#666;white-space:nowrap;"><?= htmlspecialchars($log['timestamp'] ?? '') ?></td>
                        <td style="padding:12px 16px;font-size:13px;color:#333;font-weight:500;"><?= htmlspecialchars($log['user'] ?? '') ?></td>
                        <td style="padding:12px 16px;">
                            <span style="background:#f5f5f5;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= htmlspecialchars($log['action'] ?? '') ?></span>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;color:#333;"><?= htmlspecialchars($log['resource'] ?? '') ?></td>
                        <td style="padding:12px 16px;font-size:13px;color:#666;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($log['description'] ?? '') ?></td>
                        <td style="padding:12px 16px;font-size:13px;color:#999;font-family:monospace;"><?= htmlspecialchars($log['ip'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pagination['last'] > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:24px;">
        <?php if ($pagination['current'] > 1): ?>
            <a href="?page=<?= $pagination['current'] - 1 ?>&action=<?= urlencode($actionFilter) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Previous</a>
        <?php endif; ?>
        <span style="padding:8px 14px;background:#4fc3f7;color:#1a1a2e;border-radius:6px;font-size:13px;font-weight:600;"><?= $pagination['current'] ?> / <?= $pagination['last'] ?></span>
        <?php if ($pagination['current'] < $pagination['last']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?>&action=<?= urlencode($actionFilter) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
