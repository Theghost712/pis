<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Consent;
use App\Models\User;

class ConsentService
{
    private Consent $consentModel;
    private AuditService $auditService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->consentModel = new Consent();
        $this->auditService = new AuditService();
        $this->notificationService = new NotificationService();
    }

    public function grant(int $consentId, int $userId): ?array
    {
        $consent = $this->consentModel->find($consentId);

        if (!$consent) {
            return null;
        }

        if ($consent['status'] === 'active') {
            return $consent;
        }

        $this->consentModel->updateStatus($consentId, 'active', 'granted_at');

        $this->auditService->log(
            'consent_granted',
            'consent',
            $consentId,
            "Consent #{$consentId} granted for type: {$consent['consent_type']}"
        );

        $updated = $this->consentModel->find($consentId);
        $this->notifyConsentChange($updated, 'granted');

        return $updated;
    }

    public function revoke(int $consentId, int $userId): ?array
    {
        $consent = $this->consentModel->find($consentId);

        if (!$consent) {
            return null;
        }

        if ($consent['status'] === 'revoked') {
            return $consent;
        }

        $this->consentModel->updateStatus($consentId, 'revoked', 'revoked_at');

        $this->auditService->log(
            'consent_revoked',
            'consent',
            $consentId,
            "Consent #{$consentId} revoked for type: {$consent['consent_type']}"
        );

        $updated = $this->consentModel->find($consentId);
        $this->notifyConsentChange($updated, 'revoked');

        return $updated;
    }

    public function isConsentActive(int $patientId, string $consentType, ?int $providerId = null): bool
    {
        $conditions = [
            'patient_id' => $patientId,
            'consent_type' => $consentType,
            'status' => 'active',
        ];

        if ($providerId !== null) {
            $conditions['provider_id'] = $providerId;
        }

        $results = $this->consentModel->where($conditions);

        if (empty($results)) {
            return false;
        }

        $consent = $results[0];

        if ($consent['expires_at'] !== null && strtotime($consent['expires_at']) < time()) {
            $this->consentModel->updateStatus((int)$consent['id'], 'expired');
            return false;
        }

        return true;
    }

    private function notifyConsentChange(array $consent, string $action): void
    {
        $patientModel = new \App\Models\Patient();
        $patient = $patientModel->find((int)$consent['patient_id']);

        if ($patient && ($action === 'revoked' || $action === 'granted')) {
            $this->notificationService->send(
                $patient['user_id'],
                "Consent {$action}",
                "Your consent for '{$consent['consent_type']}' has been {$action}.",
                'consent'
            );
        }
    }
}
