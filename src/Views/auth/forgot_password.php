<?php
$pageTitle = 'Forgot Password';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-info text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Reset Password</h4>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
                <form method="POST" action="/forgot-password" class="validated">
                    <input type="hidden" name="csrf_token" value="<?php echo $this->security->generateCSRFToken(); ?>">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn btn-info w-100 text-white">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="/login" class="text-decoration-none small">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
