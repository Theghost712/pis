<?php
$pageTitle = 'Register';
$formData = $this->session->getFlash('form_data', []);
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Patient Registration</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="text-center mb-4">Create Your Account</h5>
                <form method="POST" action="/register" class="validated" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select...</option>
                                <option value="male" <?php echo (($formData['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (($formData['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo (($formData['gender'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" 
                                   value="<?php echo htmlspecialchars($formData['emergency_contact_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Emergency Phone</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="<?php echo htmlspecialchars($formData['emergency_contact_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="blood_type" class="form-label">Blood Type</label>
                            <select class="form-select" id="blood_type" name="blood_type">
                                <option value="">Select...</option>
                                <option value="A+" <?php echo (($formData['blood_type'] ?? '') === 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (($formData['blood_type'] ?? '') === 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (($formData['blood_type'] ?? '') === 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (($formData['blood_type'] ?? '') === 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo (($formData['blood_type'] ?? '') === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (($formData['blood_type'] ?? '') === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo (($formData['blood_type'] ?? '') === 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (($formData['blood_type'] ?? '') === 'O-') ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="allergies" class="form-label">Allergies</label>
                            <input type="text" class="form-control" id="allergies" name="allergies" 
                                   value="<?php echo htmlspecialchars($formData['allergies'] ?? ''); ?>"
                                   placeholder="e.g., Penicillin, Pollen">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </form>
                <div class="text-center mt-3">
                    Already have an account? <a href="/login">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';