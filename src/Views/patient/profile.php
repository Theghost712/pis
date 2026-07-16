<?php
$profile = $profile ?? [
    'medical_record_number' => '',
    'blood_type' => '',
    'date_of_birth' => '',
    'phone' => '',
    'address' => '',
    'emergency_contact_name' => '',
    'emergency_contact_phone' => '',
    'insurance_provider' => '',
    'insurance_policy_number' => '',
];
?>
<div style="max-width:800px;">
    <form method="POST" action="/patient/profile">
        <?= \App\Core\Security::csrfField() ?>

        <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;border-bottom:2px solid #f0f0f0;padding-bottom:12px;">Medical Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Medical Record Number</label>
                    <input type="text" name="medical_record_number" value="<?= htmlspecialchars($profile['medical_record_number']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;outline:none;" readonly>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Blood Type</label>
                    <select name="blood_type" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;">
                        <option value="">Select...</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt): ?>
                            <option value="<?= $bt ?>" <?= ($profile['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($profile['date_of_birth']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Phone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($profile['phone']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
            </div>
            <div style="margin-top:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($profile['address']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            </div>
        </div>

        <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;border-bottom:2px solid #f0f0f0;padding-bottom:12px;">Emergency Contact</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="<?= htmlspecialchars($profile['emergency_contact_name']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" value="<?= htmlspecialchars($profile['emergency_contact_phone']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
            </div>
        </div>

        <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;border-bottom:2px solid #f0f0f0;padding-bottom:12px;">Insurance Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Insurance Provider</label>
                    <input type="text" name="insurance_provider" value="<?= htmlspecialchars($profile['insurance_provider']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Policy Number</label>
                    <input type="text" name="insurance_policy_number" value="<?= htmlspecialchars($profile['insurance_policy_number']) ?>" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
            </div>
        </div>

        <button type="submit" style="padding:12px 32px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;">Save Profile</button>
    </form>
</div>
