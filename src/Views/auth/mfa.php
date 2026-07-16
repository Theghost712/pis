<?php
$pageTitle = 'Two-Factor Authentication';
$content = '
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-dark text-center py-3">
                <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-center text-muted mb-4">
                    Please enter the 6-digit code from your authenticator app.
                </p>
                <form method="POST" action="/login">
                    <input type="hidden" name="username" value="' . htmlspecialchars($_POST['username'] ?? '') . '">
                    <input type="hidden" name="password" value="' . htmlspecialchars($_POST['password'] ?? '') . '">
                    <input type="hidden" name="csrf_token" value="' . $this->security->generateCSRFToken() . '">
                    <div class="mb-4">
                        <label for="mfa_code" class="form-label">Authentication Code</label>
                        <input type="text" class="form-control form-control-lg text-center" id="mfa_code" 
                               name="mfa_code" placeholder="000000" maxlength="6" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-check-circle me-2"></i>Verify
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="/login" class="text-decoration-none small">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</div>
';
require_once __DIR__ . '/../layouts/main.php'; 