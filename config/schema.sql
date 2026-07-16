-- PIS Database Schema (canonical)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'provider', 'patient', 'system_admin') DEFAULT 'patient',
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    mfa_secret VARCHAR(32) NULL,
    mfa_enabled BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_username (username),
    INDEX idx_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    blood_type VARCHAR(5),
    phone VARCHAR(20),
    address TEXT,
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(20),
    allergies TEXT,
    insurance_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_patients_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    specialization VARCHAR(100),
    hospital_id INT,
    department VARCHAR(100),
    years_of_experience INT,
    consultation_fee DECIMAL(10,2),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL,
    INDEX idx_providers_user_id (user_id),
    INDEX idx_providers_hospital (hospital_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT,
    record_type ENUM('visit', 'lab_result', 'prescription', 'imaging', 'procedure', 'diagnosis', 'immunization', 'surgery', 'consultation', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    diagnosis TEXT,
    prescription TEXT,
    lab_results JSON,
    content JSON,
    notes TEXT,
    record_date DATE NOT NULL,
    version INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL,
    INDEX idx_medical_records_patient_id (patient_id),
    INDEX idx_medical_records_provider_id (provider_id),
    INDEX idx_medical_records_type (record_type),
    INDEX idx_medical_records_date (record_date),
    INDEX idx_medical_records_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    consent_type VARCHAR(100),
    scope TEXT,
    status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
    description TEXT,
    granted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_consent (patient_id, provider_id, status),
    INDEX idx_consent_patient (patient_id),
    INDEX idx_consent_provider (provider_id),
    INDEX idx_consent_status (status),
    INDEX idx_consent_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id INT,
    description TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    details JSON,
    status ENUM('success', 'failure') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_user_id (user_id),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_created_at (created_at),
    INDEX idx_audit_logs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_provider_id INT NOT NULL,
    to_provider_id INT NOT NULL,
    patient_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (to_provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_referrals_from (from_provider_id),
    INDEX idx_referrals_to (to_provider_id),
    INDEX idx_referrals_patient (patient_id),
    INDEX idx_referrals_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS file_storage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    mime_type VARCHAR(100),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
    INDEX idx_file_storage_record (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data

INSERT INTO hospitals (name, address, phone, email) VALUES
('City General Hospital', '123 Main St, City, State 12345', '555-0100', 'info@citygeneral.com'),
('Medical Center East', '456 Oak Ave, City, State 12345', '555-0200', 'info@medicalcenter.com');

-- Default users (password: Admin123! for all)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified) VALUES
('admin', 'admin@system.com', '$2y$12$Rj9VqPZQ9BvVv8vVv8vVv8vVv8vVv8vVv8vVv8', 'System', 'Admin', 'system_admin', 1),
('dr_smith', 'dr.smith@hospital.com', '$2y$12$Rj9VqPZQ9BvVv8vVv8vVv8vVv8vVv8vVv8vVv8', 'John', 'Smith', 'provider', 1),
('dr_johnson', 'dr.johnson@hospital.com', '$2y$12$Rj9VqPZQ9BvVv8vVv8vVv8vVv8vVv8vVv8vVv8', 'Sarah', 'Johnson', 'provider', 1),
('jane_doe', 'jane.doe@email.com', '$2y$12$Rj9VqPZQ9BvVv8vVv8vVv8vVv8vVv8vVv8vVv8', 'Jane', 'Doe', 'patient', 1);

INSERT INTO providers (user_id, specialization, license_number, hospital_id, years_of_experience) VALUES
(2, 'Cardiology', 'LIC-001', 1, 15),
(3, 'Neurology', 'LIC-002', 2, 10);

INSERT INTO patients (user_id, date_of_birth, gender, phone, address) VALUES
(4, '1985-05-15', 'female', '555-0300', '789 Pine St, City, State 12345');

INSERT INTO consent (patient_id, provider_id, scope, expires_at) VALUES
(1, 1, 'Full access to all medical records including lab results and prescriptions.', DATE_ADD(NOW(), INTERVAL 1 YEAR)),
(1, 2, 'Access to neurological examination records only.', DATE_ADD(NOW(), INTERVAL 6 MONTH));
