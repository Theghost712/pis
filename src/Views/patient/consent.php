<?php
$pageTitle = 'Manage Consents';
$dashboardUrl = '/patient/dashboard';

$content = '
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-handshake text-primary me-2"></i>Manage Consents</h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i>Grant New Consent</h5>
            </div>
            <div class="card-body">
                <?php if (empty($availableProviders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No available providers to grant consent to.
                    </div>
                <?php else: ?>
                    <form method="POST" action="/consent/create" class="validated">
                        <input type="hidden" name="csrf_token" value="<?php echo $this->security->generateCSRFToken(); ?>">
                        <div class="mb-3">
                            <label for="provider_id" class="form-label">Select Provider</label>
                            <select class="form-select" id="provider_id" name="provider_id" required>
                                <option value="">Choose a provider...</option>
                                <?php foreach ($availableProviders as $provider): ?>
                                    <option value="' . $provider['id'] . '">
                                        ' . htmlspecialchars($provider['first_name'] . ' ' . $provider['last_name']) . '
                                        - ' . htmlspecialchars($provider['specialization']) . '
                                        (' . htmlspecialchars($provider['hospital_name']) . ')
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="scope" class="form-label">Access Scope</label>
                            <textarea class="form-control" id="scope" name="scope" rows="3" required 
                                      placeholder="Describe what records this provider can access..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at" 
                                   value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-2"></i>Grant Consent
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Consents</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($consents)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p>No consents found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="consentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Provider</th>
                                    <th>Scope</th>
                                    <th>Granted</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consents as $consent): 
                                    $isExpired = strtotime($consent['expires_at']) <= time();
                                    $isActive = $consent['status'] === 'active' && !$isExpired;
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($consent['first_name'] . ' ' . $consent['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($consent['specialization']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($consent['scope'], 0, 50)) . (strlen($consent['scope']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($consent['granted_at'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($consent['expires_at'])); ?></td>
                                        <td>
                                            <?php if ($isActive): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php elseif ($consent['status'] === 'expired' || $isExpired): ?>
                                                <span class="badge bg-warning">Expired</span>
                                            <?php elseif ($consent['status'] === 'revoked'): ?>
                                                <span class="badge bg-danger">Revoked</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $consent['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isActive): ?>
                                                <form method="POST" action="/consent/revoke" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $this->security->generateCSRFToken(); ?>">
                                                    <input type="hidden" name="consent_id" value="' . $consent['id'] . '">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm(\'Are you sure you want to revoke this consent?\')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                <?php if (strtotime($consent['expires_at']) <= time() + 30 * 86400): ?>
                                                    <form method="POST" action="/consent/renew" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $this->security->generateCSRFToken(); ?>">
                                                        <input type="hidden" name="consent_id" value="' . $consent['id'] . '">
                                                        <input type="hidden" name="days" value="365">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php elseif ($consent['status'] === 'expired' || $isExpired): ?>
                                                <form method="POST" action="/consent/renew" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $this->security->generateCSRFToken(); ?>">
                                                    <input type="hidden" name="consent_id" value="' . $consent['id'] . '">
                                                    <input type="hidden" name="days" value="365">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-redo"></i> Renew
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
';
require_once __DIR__ . '/../layouts/main.php';