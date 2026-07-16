<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Validation;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Consent;
use App\Models\AuditLog;
use App\Services\NotificationService;

class ConsentController
{
    private Session $session;
    private Validation $validation;
    private Patient $patientModel;
    private Provider $providerModel;
    private Consent $consentModel;
    private AuditLog $auditLog;
    private NotificationService $notification;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->session->start();
        $this->validation = new Validation();
        $this->patientModel = new Patient();
        $this->providerModel = new Provider();
        $this->consentModel = new Consent();
        $this->auditLog = new AuditLog();
        $this->notification = new NotificationService();
        
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
        
        require_once __DIR__ . '/../Views/patient/consent.php';
    }

    public function create(): void
    {
        $data = $_POST;
        
        $rules = [
            'provider_id' => 'required|numeric|exists:providers,id',
            'scope' => 'required|min:5',
            'expires_at' => 'required|date'
        ];
        
        if (!$this->validation->validate($data, $rules)) {
            $this->session->setFlash('error', $this->validation->getFirstError());
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

        $provider = $this->providerModel->findById($data['provider_id']);
        if (!$provider) {
            $this->session->setFlash('error', 'Provider not found.');
            header('Location: /consent');
            exit;
        }

        // Check if consent already exists
        if (Consent::hasActiveConsent($patient->getId(), $data['provider_id'])) {
            $this->session->setFlash('error', 'Active consent already exists for this provider.');
            header('Location: /consent');
            exit;
        }

        // Check if expiration date is in the future
        if (strtotime($data['expires_at']) < time()) {
            $this->session->setFlash('error', 'Expiration date must be in the future.');
            header('Location: /consent');
            exit;
        }

        $consentId = $this->consentModel->create([
            'patient_id' => $patient->getId(),
            'provider_id' => $data['provider_id'],
            'scope' => $data['scope'],
            'expires_at' => $data['expires_at']
        ]);

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_granted',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'details' => [
                'provider_id' => $data['provider_id'],
                'scope' => $data['scope'],
                'expires_at' => $data['expires_at']
            ],
            'status' => 'success'
        ]);

        // Send notification to provider
        $providerUser = $provider->getUser();
        if ($providerUser) {
            $this->notification->sendConsentNotification(
                $providerUser->getEmail(),
                $providerUser->getFullName(),
                [
                    'patient_name' => $patient->getFullName(),
                    'scope' => $data['scope'],
                    'expires_at' => date('M d, Y', strtotime($data['expires_at']))
                ]
            );
        }

        $this->session->setFlash('success', 'Consent granted successfully.');
        header('Location: /consent');
        exit;
    }

    public function revoke(): void
    {
        $consentId = $_POST['consent_id'] ?? null;
        
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

        $consent = $this->consentModel->findById($consentId);
        
        if (!$consent || $consent->getPatientId() !== $patient->getId()) {
            $this->session->setFlash('error', 'Consent not found or unauthorized.');
            header('Location: /consent');
            exit;
        }

        $consent->revoke();

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_revoked',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Consent revoked successfully.');
        header('Location: /consent');
        exit;
    }

    public function renew(): void
    {
        $consentId = $_POST['consent_id'] ?? null;
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

        $consent = $this->consentModel->findById($consentId);
        
        if (!$consent || $consent->getPatientId() !== $patient->getId()) {
            $this->session->setFlash('error', 'Consent not found or unauthorized.');
            header('Location: /consent');
            exit;
        }

        $consent->renew($days);

        $this->auditLog->log([
            'user_id' => $userId,
            'action' => 'consent_renewed',
            'resource_type' => 'consent',
            'resource_id' => $consentId,
            'details' => ['days' => $days],
            'status' => 'success'
        ]);

        $this->session->setFlash('success', 'Consent renewed successfully.');
        header('Location: /consent');
        exit;
    }

    private function getAvailableProviders(int $patientId): array
    {
        $db = \App\Core\Database::getInstance();
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