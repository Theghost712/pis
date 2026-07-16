<?php
$pageTitle = 'Patient Dashboard';
$dashboardUrl = '/patient/dashboard';

$content = '
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-circle text-primary me-2"></i>Welcome, ' . htmlspecialchars($_SESSION['user_name'] ?? 'Patient') . '</h2>
            <span class="badge bg-primary fs-6">Patient ID: P' . str_pad($patientData->getId(), 6, '0', STR_PAD_LEFT) . '</span>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card bg-primary text-white h-100 shadow-sm">
            <div class="card-body dashboard-stat">
                <div class="number">' . $stats['total_records'] . '</div>
                <div class="label text-white-50">Total Records</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-success text-white h-100 shadow-sm">
            <div class="card-body dashboard-stat">
                <div class="number">' . $stats['active_consents'] . '</div>
                <div class="label text-white-50">Active Consents</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-info text-white h-100 shadow-sm">
            <div class="card-body dashboard-stat">
                <div class="number">' . $stats['total_consents'] . '</div>
                <div class="label text-white-50">Total Consents</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card bg-warning text-dark h-100 shadow-sm">
            <div class="card-body dashboard-stat">
                <div class="number">' . $stats['recent_visits'] . '</div>
                <div class="label text-dark-50">Recent Visits</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-notes-medical text-primary me-2"></i>Recent Medical Records</h5>
                <a href="/patient/records" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentRecords)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p>No medical records found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Provider</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRecords as $record): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['record_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst(str_replace('_', ' ', $record['record_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($record['last_name'] ?? ''); ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/consent" class="btn btn-primary">
                        <i class="fas fa-handshake me-2"></i>Manage Consents
                    </a>
                    <a href="/patient/profile" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit me-2"></i>Update Profile
                    </a>
                    <a href="/patient/records" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i>View All Records
                    </a>
                    <a href="/setup-mfa" class="btn btn-outline-warning">
                        <i class="fas fa-shield-alt me-2"></i>Setup 2FA
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Consents -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Active Consents</h5>
                <a href="/consent" class="btn btn-sm btn-outline-success">Manage</a>
            </div>
            <div class="card-body">
                <?php if (empty($activeConsents)): ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                        <p class="small">No active consents.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($activeConsents, 0, 5) as $consent): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo htmlspecialchars($consent['first_name'] . ' ' . $consent['last_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Expires: <?php echo date('M d, Y', strtotime($consent['expires_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($activeConsents) > 5): ?>
                            <div class="text-center mt-2">
                                <a href="/consent" class="text-decoration-none small">+ <?php echo count($activeConsents) - 5; ?> more</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
';
require_once __DIR__ . '/../layouts/main.php';