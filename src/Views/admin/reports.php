<?php
$reports = [
    [
        'title' => 'User Registration Report',
        'description' => 'View new user registrations over time, broken down by role.',
        'icon' => '👤',
        'color' => '#4fc3f7',
        'key' => 'user_registration',
    ],
    [
        'title' => 'Medical Records Report',
        'description' => 'Analyze medical record creation trends and types.',
        'icon' => '📋',
        'color' => '#66bb6a',
        'key' => 'medical_records',
    ],
    [
        'title' => 'Provider Activity Report',
        'description' => 'Track provider activity including records created and patients seen.',
        'icon' => '🏥',
        'color' => '#ab47bc',
        'key' => 'provider_activity',
    ],
    [
        'title' => 'Consent Activity Report',
        'description' => 'Monitor consent grants and revocations over time.',
        'icon' => '🛡️',
        'color' => '#ffb74d',
        'key' => 'consent_activity',
    ],
];
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:20px;font-weight:700;">Reports</h2>
</div>

<div style="background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:24px;">
    <form method="GET" action="/admin/reports" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:4px;">From Date</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#666;margin-bottom:4px;">To Date</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
        </div>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
    <?php foreach ($reports as $report): ?>
        <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid <?= $report['color'] ?>;">
            <div style="font-size:32px;margin-bottom:12px;"><?= $report['icon'] ?></div>
            <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;"><?= htmlspecialchars($report['title']) ?></h3>
            <p style="font-size:13px;color:#666;margin-bottom:20px;line-height:1.5;"><?= htmlspecialchars($report['description']) ?></p>
            <form method="GET" action="/admin/reports/<?= $report['key'] ?>">
                <input type="hidden" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                <input type="hidden" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                <button type="submit" style="padding:10px 20px;background:<?= $report['color'] ?>;color:#1a1a2e;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;width:100%;">Generate Report</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
