<?php
$patients = $patients ?? [];
$selectedPatient = $selectedPatient ?? '';
$recordTypes = ['visit', 'lab_result', 'prescription', 'imaging', 'procedure'];
?>
<div style="max-width:700px;">
    <div style="margin-bottom:24px;">
        <a href="/provider/dashboard" style="color:#4fc3f7;text-decoration:none;font-size:14px;font-weight:600;">&larr; Back to Dashboard</a>
    </div>

    <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:24px;">Add Medical Record</h2>

        <form method="POST" action="/provider/add-record">
            <?= \App\Core\Security::csrfField() ?>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Patient *</label>
                <select name="patient_id" required style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;">
                    <option value="">Select a patient...</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $selectedPatient == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name'] ?? '') ?> (<?= htmlspecialchars($p['mrn'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Record Type *</label>
                    <select name="record_type" required style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;">
                        <option value="">Select type...</option>
                        <?php foreach ($recordTypes as $rt): ?>
                            <option value="<?= $rt ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $rt))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Record Date *</label>
                    <input type="date" name="record_date" value="<?= htmlspecialchars($record_date ?? date('Y-m-d')) ?>" required style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required placeholder="e.g., Annual Checkup" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Description</label>
                <textarea name="description" rows="3" placeholder="Describe the record..." style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"><?= htmlspecialchars($description ?? '') ?></textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Diagnosis</label>
                <input type="text" name="diagnosis" value="<?= htmlspecialchars($diagnosis ?? '') ?>" placeholder="e.g., Type 2 Diabetes" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">Notes</label>
                <textarea name="notes" rows="3" placeholder="Additional notes..." style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"><?= htmlspecialchars($notes ?? '') ?></textarea>
            </div>

            <button type="submit" style="padding:12px 32px;background:#4fc3f7;color:#1a1a2e;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;">Save Record</button>
        </form>
    </div>
</div>
