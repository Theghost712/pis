<?php
$pageTitle = 'Reset Password';
$token = htmlspecialchars($_GET['token'] ?? '');
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-lock-open me-2"></i>Set New Password</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    Enter your new password below.
                </p>
                <form method="POST" action="/reset-password" class="validated">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter new password" required>
                        <small class="text-muted">Minimum 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" 
                               name="password_confirmation" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check-circle me-2"></i>Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
