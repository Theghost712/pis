<?php $settings = $settings ?? []; ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">System Settings</h2>
</div>

<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:30px;">
    <form method="POST" action="/admin/settings">
        <?= \App\Core\Security::csrfField() ?>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#666;margin-bottom:6px;">Session Timeout (seconds)</label>
            <input type="number" name="session_timeout" value="<?= htmlspecialchars($settings['session_timeout'] ?? '300') ?>" style="width:100%;max-width:400px;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#666;margin-bottom:6px;">Max Login Attempts</label>
            <input type="number" name="max_login_attempts" value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>" style="width:100%;max-width:400px;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#666;margin-bottom:6px;">Password Minimum Length</label>
            <input type="number" name="min_password_length" value="<?= htmlspecialchars($settings['min_password_length'] ?? '8') ?>" style="width:100%;max-width:400px;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="require_mfa" value="1" <?= ($settings['require_mfa'] ?? false) ? 'checked' : '' ?> style="width:18px;height:18px;">
                <span style="font-size:14px;color:#333;">Require MFA for all users</span>
            </label>
        </div>

        <button type="submit" style="padding:10px 24px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">Save Settings</button>
    </form>
</div>
