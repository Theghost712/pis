<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Security;
use App\Core\Database;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Consent;
use App\Models\AuditLog;

class ConsentController
{
    private Session $session;
    private Security $security;
    private Patient $patientModel;
    private Provider $providerModel;
    private Consent $consentModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->session->start();
        $this->security = new Security();
        $this->patientModel = new Patient();
        $this->providerModel = new Provider();
        $this->consentModel = new Consent();
        $this->auditLog = new AuditLog();
        
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function index(): void
    {
        $userId = $this->session->getUserId();
        $patient = $this->patientModel->findByUserId($userId);
        
        if (!$patient) {
            header('Location: /');
            exit;
        }

        $consents = $this->consentModel->findByPatientId($patient->getId());
        $availableProviders = $this->getAvailableProviders($patient->getId());
        $csrfToken = $this->security->generateCSRFToken();
        
        require_once __DIR__ . '/../Views/patient/consent.php';
    }

    public function create(): void
    {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';
        
        $userId = $this->session->getUserId();
        $patient = $this->patientModel->findByUserId($userId);
        
        if (!$patient) {
            $this->session->setFlash('error', 'Patient not found.');
            header('Location: /consent');
            exit;
        }

        $providerId = (int)($data['provider_id'] ?? 0);
        $scope = $data['scope'] ?? '';
        $expiresAt = $data['expires_at'] ?? '';

        if (!$providerId || !$scope || !$expiresAt) {
            $this->session->setFlash('error', 'All fields are required.');
            header('Location: /consent');
            exit;
        }

        if (strtotime($expiresAt) < time()) {
            $this->session->setFlash('error', 'Expiration date must be in the future.');
            header('Location: /consent');
            exit;
        }

        $consentId = $this->consentModel->create([
            'patient_id' => $patient->getId(),
            'provider_id' => $providerId,
            'scope' => $scope,
            'expires_at' => $expiresAt,
        ]);

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_granted',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'details' => [
                'provider_id' => $providerId,
                'scope' => $scope,
                'expires_at' => $expiresAt,
            ],
            'status' => 'success',
        ]);

        $this->session->setFlash('success', 'Consent granted successfully.');
        header('Location: /consent');
        exit;
    }

    public function revoke(): void
    {
        $consentId = (int)($_POST['consent_id'] ?? 0);
        
        if (!$consentId) {
            $this->session->setFlash('error', 'Consent ID required.');
            header('Location: /consent');
            exit;
        }

        $userId = $this->session->getUserId();
        $patient = $this->patientModel->findByUserId($userId);
        
        if (!$patient) {
            $this->session->setFlash('error', 'Patient not found.');
            header('Location: /consent');
            exit;
        }

        $consentData = $this->consentModel->find($consentId);
        
        if (!$consentData || (int)$consentData['patient_id'] !== $patient->getId()) {
            $this->session->setFlash('error', 'Consent not found or unauthorized.');
            header('Location: /consent');
            exit;
        }

        $this->consentModel->updateStatus($consentId, 'revoked', 'updated_at');

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_revoked',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'status' => 'success',
        ]);

        $this->session->setFlash('success', 'Consent revoked successfully.');
        header('Location: /consent');
        exit;
    }

    public function renew(): void
    {
        $consentId = (int)($_POST['consent_id'] ?? 0);
        $days = (int)($_POST['days'] ?? 365);
        
        if (!$consentId) {
            $this->session->setFlash('error', 'Consent ID required.');
            header('Location: /consent');
            exit;
        }

        if ($days < 1 || $days > 730) {
            $this->session->setFlash('error', 'Invalid renewal period.');
            header('Location: /consent');
            exit;
        }

        $userId = $this->session->getUserId();
        $patient = $this->patientModel->findByUserId($userId);
        
        if (!$patient) {
            $this->session->setFlash('error', 'Patient not found.');
            header('Location: /consent');
            exit;
        }

        $consentData = $this->consentModel->find($consentId);
        
        if (!$consentData || (int)$consentData['patient_id'] !== $patient->getId()) {
            $this->session->setFlash('error', 'Consent not found or unauthorized.');
            header('Location: /consent');
            exit;
        }

        $newExpiry = date('Y-m-d H:i:s', time() + ($days * 86400));
        $db = Database::getInstance();
        $db->prepareAndExecute(
            "UPDATE consent SET status = 'active', expires_at = ?, updated_at = NOW() WHERE id = ?",
            [$newExpiry, $consentId]
        );

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_renewed',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'details' => ['days' => $days],
            'status' => 'success',
        ]);

        $this->session->setFlash('success', 'Consent renewed successfully.');
        header('Location: /consent');
        exit;
    }

    private function getAvailableProviders(int $patientId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepareAndExecute(
            "SELECT p.*, u.first_name, u.last_name, h.name as hospital_name 
             FROM providers p
             JOIN users u ON p.user_id = u.id
             JOIN hospitals h ON p.hospital_id = h.id
             WHERE p.id NOT IN (
                 SELECT provider_id FROM consent 
                 WHERE patient_id = ? AND status = 'active' AND expires_at > NOW()
             )
             AND u.is_active = 1
             ORDER BY u.last_name, u.first_name",
            [$patientId]
        );
        return $stmt->fetchAll();
    }
}
