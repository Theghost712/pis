<?php
$users = $users ?? [];
$pagination = $pagination ?? ['current' => 1, 'last' => 1];
$currentRoleFilter = $currentRoleFilter ?? '';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">User Management</h2>
    <a href="/admin/users/create" style="padding:10px 20px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">+ Add User</a>
</div>

<div style="margin-bottom:20px;display:flex;gap:10px;">
    <a href="/admin/users" style="padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;<?= $currentRoleFilter === '' ? 'background:#1a1a2e;color:#fff;' : 'background:#f0f0f0;color:#666;' ?>">All</a>
    <a href="/admin/users?role=patient" style="padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;<?= $currentRoleFilter === 'patient' ? 'background:#1a1a2e;color:#fff;' : 'background:#f0f0f0;color:#666;' ?>">Patients</a>
    <a href="/admin/users?role=provider" style="padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;<?= $currentRoleFilter === 'provider' ? 'background:#1a1a2e;color:#fff;' : 'background:#f0f0f0;color:#666;' ?>">Providers</a>
    <a href="/admin/users?role=admin" style="padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;<?= $currentRoleFilter === 'admin' ? 'background:#1a1a2e;color:#fff;' : 'background:#f0f0f0;color:#666;' ?>">Admins</a>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Name</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Email</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Role</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Created</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px;font-size:14px;color:#333;font-weight:500;"><?= htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <?php
                            $roleColors = ['patient' => ['#e3f2fd', '#1565c0'], 'provider' => ['#e8f5e9', '#2e7d32'], 'admin' => ['#fce4ec', '#c62828']];
                            $role = $u['role'] ?? 'patient';
                            $rc = $roleColors[$role] ?? ['#f5f5f5', '#666'];
                            ?>
                            <span style="background:<?= $rc[0] ?>;color:<?= $rc[1] ?>;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($role) ?></span>
                        </td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <div style="display:flex;gap:8px;">
                                <a href="/admin/users/<?= (int)($u['id'] ?? 0) ?>/edit" style="padding:5px 12px;background:#e3f2fd;color:#1565c0;border-radius:5px;text-decoration:none;font-size:12px;font-weight:600;">Edit</a>
                                <form method="POST" action="/admin/users/<?= (int)($u['id'] ?? 0) ?>/delete" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <?= \App\Core\Security::csrfField() ?>
                                    <button type="submit" style="padding:5px 12px;background:#ffebee;color:#c62828;border:1px solid #ef9a9a;border-radius:5px;font-size:12px;font-weight:600;cursor:pointer;">Delete</button>
                                </form>
                            </div>
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
            <a href="?page=<?= $pagination['current'] - 1 ?><?= $currentRoleFilter ? "&role={$currentRoleFilter}" : '' ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Previous</a>
        <?php endif; ?>
        <span style="padding:8px 14px;background:#4fc3f7;color:#1a1a2e;border-radius:6px;font-size:13px;font-weight:600;"><?= $pagination['current'] ?> / <?= $pagination['last'] ?></span>
        <?php if ($pagination['current'] < $pagination['last']): ?>
            <a href="?page=<?= $pagination['current'] + 1 ?><?= $currentRoleFilter ? "&role={$currentRoleFilter}" : '' ?>" style="padding:8px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;text-decoration:none;color:#333;font-size:13px;">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
