<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\MedicalRecord;
use App\Models\Consent;
use App\Models\AuditLog;
use App\Core\Database;

class DashboardController extends Controller
{
    private Session $session;
    private User $userModel;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->session->start();
        $this->userModel = new User();
        
        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function patientDashboard(): void
    {
        $userId = $this->session->getUserId();
        $patient = new Patient();
        $patientData = $patient->findByUserId($userId);
        
        if (!$patientData) {
            header('Location: /');
            exit;
        }

        $medicalRecord = new MedicalRecord();
        $records = $medicalRecord->findByPatientId($patientData->getId());
        
        $consent = new Consent();
        $consents = $consent->findByPatientId($patientData->getId());
        
        $recentRecords = array_slice($records, 0, 5);
        $activeConsents = array_filter($consents, function($c) {
            return $c['status'] === 'active' && strtotime($c['expires_at']) > time();
        });

        $stats = [
            'total_records' => count($records),
            'active_consents' => count($activeConsents),
            'total_consents' => count($consents),
            'recent_visits' => count($recentRecords)
        ];

        $this->view('patient.dashboard', ['patientData' => $patientData, 'stats' => $stats, 'recentRecords' => $recentRecords, 'activeConsents' => $activeConsents, 'currentPage' => 'dashboard']);
    }

    public function providerDashboard(): void
    {
        $userId = $this->session->getUserId();
        $provider = new Provider();
        $providerData = $provider->findByUserId($userId);
        
        if (!$providerData) {
            header('Location: /');
            exit;
        }

        $patients = $providerData->getPatients();
        $referrals = $providerData->getReferrals();
        
        $medicalRecord = new MedicalRecord();
        $records = $medicalRecord->findByProviderId($providerData->getId());
        
        $stats = [
            'total_patients' => count($patients),
            'total_records' => count($records),
            'pending_referrals' => count(array_filter($referrals, function($r) {
                return $r['status'] === 'pending';
            })),
            'total_referrals' => count($referrals)
        ];

        $this->view('provider.dashboard', ['stats' => $stats, 'currentPage' => 'dashboard']);
    }

    public function adminDashboard(): void
    {
        $userId = $this->session->getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user || !in_array($user->getRole(), ['admin', 'system_admin'])) {
            header('Location: /');
            exit;
        }

        // Get system stats
        $db = Database::getInstance();
        
        $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM users");
        $totalUsers = $stmt->fetch()['count'];
        
        $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM patients");
        $totalPatients = $stmt->fetch()['count'];
        
        $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM providers");
        $totalProviders = $stmt->fetch()['count'];
        
        $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM medical_records");
        $totalRecords = $stmt->fetch()['count'];
        
        $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM consent WHERE status = 'active' AND expires_at > NOW()");
        $activeConsents = $stmt->fetch()['count'];

        $auditLog = new AuditLog();
        $recentLogs = $auditLog->getRecent(10);
        $logStats = $auditLog->getStats();

        // Get user activity stats
        $stmt = $db->prepareAndExecute(
            "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $newUsers = $stmt->fetch()['count'];

        $stats = [
            'total_users' => $totalUsers,
            'total_patients' => $totalPatients,
            'total_providers' => $totalProviders,
            'total_records' => $totalRecords,
            'active_consents' => $activeConsents,
            'new_users' => $newUsers
        ];

        $this->view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalPatients' => $totalPatients,
            'totalProviders' => $totalProviders,
            'totalRecords' => $totalRecords,
            'activeConsents' => $activeConsents,
            'newUsers' => $newUsers,
            'recentAuditLogs' => $recentLogs ?? [],
            'systemHealth' => ['database' => 'healthy', 'storage' => 'healthy', 'uptime' => '99.9%'],
            'currentPage' => 'dashboard',
        ]);
    }
} 