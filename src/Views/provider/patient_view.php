<?php
$patient = $patient ?? ['name' => '', 'mrn' => '', 'blood_type' => '', 'date_of_birth' => '', 'phone' => '', 'email' => ''];
$records = $records ?? [];
?>
<div style="margin-bottom:24px;">
    <a href="/provider/patients" style="color:#4fc3f7;text-decoration:none;font-size:14px;font-weight:600;">&larr; Back to Patients</a>
</div>

<div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div style="display:flex;gap:16px;align-items:center;">
            <div style="width:56px;height:56px;border-radius:50%;background:#4fc3f7;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#1a1a2e;">
                <?= strtoupper(substr($patient['name'] ?? 'P', 0, 1)) ?>
            </div>
            <div>
                <h2 style="font-size:20px;font-weight:700;margin-bottom:2px;"><?= htmlspecialchars($patient['name'] ?? '') ?></h2>
                <span style="color:#666;font-size:13px;">MRN: <?= htmlspecialchars($patient['mrn'] ?? '') ?></span>
            </div>
        </div>
        <a href="/provider/add-record?patient_id=<?= (int)($patient['id'] ?? 0) ?>" style="padding:10px 20px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">+ Add Record</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0;">
        <div>
            <div style="font-size:12px;color:#999;margin-bottom:2px;">Blood Type</div>
            <div style="font-size:14px;font-weight:600;color:#333;"><?= htmlspecialchars($patient['blood_type'] ?? 'N/A') ?></div>
        </div>
        <div>
            <div style="font-size:12px;color:#999;margin-bottom:2px;">Date of Birth</div>
            <div style="font-size:14px;font-weight:600;color:#333;"><?= htmlspecialchars($patient['date_of_birth'] ?? 'N/A') ?></div>
        </div>
        <div>
            <div style="font-size:12px;color:#999;margin-bottom:2px;">Phone</div>
            <div style="font-size:14px;font-weight:600;color:#333;"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></div>
        </div>
        <div>
            <div style="font-size:12px;color:#999;margin-bottom:2px;">Email</div>
            <div style="font-size:14px;font-weight:600;color:#333;"><?= htmlspecialchars($patient['email'] ?? 'N/A') ?></div>
        </div>
    </div>
</div>

<h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Medical Records</h3>
<div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #eee;">
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Date</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Type</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Title</th>
                <th style="text-align:left;padding:14px 20px;font-size:13px;font-weight:600;color:#666;">Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="4" style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">No records found for this patient.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $record): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['date'] ?? '') ?></td>
                        <td style="padding:14px 20px;">
                            <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= htmlspecialchars($record['type'] ?? '') ?></span>
                        </td>
                        <td style="padding:14px 20px;font-size:14px;color:#333;"><?= htmlspecialchars($record['title'] ?? '') ?></td>
                        <td style="padding:14px 20px;font-size:14px;color:#666;"><?= htmlspecialchars($record['diagnosis'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
