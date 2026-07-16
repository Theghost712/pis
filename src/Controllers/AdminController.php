<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\MedicalRecord;
use App\Models\Hospital;
use App\Services\AuthService;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Core\Session;
use App\Core\Security;

class AdminController extends Controller
{
    private User $userModel;
    private Patient $patientModel;
    private Provider $providerModel;
    private MedicalRecord $medicalRecordModel;
    private Hospital $hospitalModel;
    private AuthService $authService;
    private AuditService $auditService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->patientModel = new Patient();
        $this->providerModel = new Provider();
        $this->medicalRecordModel = new MedicalRecord();
        $this->hospitalModel = new Hospital();
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
        $this->notificationService = new NotificationService();
    }

    // ========== WEB ROUTES ==========

    public function users(): void
    {
        if ($this->isApiRequest()) {
            $this->handleApiUsers();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createWebUser();
            return;
        }

        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];
        $allUsers = $this->userModel->all();

        $this->view('admin.users', ['user' => $user, 'users' => $allUsers, 'currentPage' => 'users']);
    }

    public function createUser(): void
    {
        $this->createWebUser();
    }

    private function createWebUser(): void
    {
        $input = $this->getInput();

        if ($this->userModel->findByEmail($input['email'] ?? '')) {
            Session::flash('error', 'Email already exists.');
            $this->redirect('/admin/users');
            return;
        }

        $this->userModel->create([
            'username' => $input['username'] ?? $input['name'] ?? '',
            'email' => $input['email'] ?? '',
            'password' => $input['password'] ?? '',
            'first_name' => $input['first_name'] ?? $input['name'] ?? '',
            'last_name' => $input['last_name'] ?? '',
            'role' => $input['role'] ?? 'patient',
        ]);

        Session::flash('success', 'User created successfully.');
        $this->redirect('/admin/users');
    }

    public function audit(): void
    {
        if ($this->isApiRequest()) {
            $this->handleApiAudit();
            return;
        }

        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $filters = [];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['resource_type'])) $filters['resource_type'] = $_GET['resource_type'];

        $result = $this->auditService->getLogs($filters, $page, 50);

        $this->view('admin.audit', ['user' => $user, 'logs' => $result['data'], 'meta' => $result['meta'], 'currentPage' => 'audit']);
    }

    public function reports(): void
    {
        if ($this->isApiRequest()) {
            $this->handleApiReports();
            return;
        }

        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $this->view('admin.reports', ['user' => $user, 'currentPage' => 'reports']);
    }

    // ========== API ROUTES (internal handlers) ==========

    private function handleApiUsers(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeUser();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
            $this->updateUserRole();
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : 20;
        $role = $_GET['role'] ?? null;

        if ($role !== null) {
            $validRoles = ['admin', 'provider', 'patient', 'system_admin'];

            if (!in_array($role, $validRoles, true)) {
                $this->json(['error' => 'Invalid role. Must be one of: ' . implode(', ', $validRoles)], 422);
                return;
            }

            $users = $this->userModel->where(['role' => $role]);
            $this->json(['data' => $users, 'role' => $role]);
            return;
        }

        $result = $this->userModel->paginate($page, $perPage);
        $this->json($result);
    }

    public function storeUser(): void
    {
        $input = $this->getInput();

        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'patient';

        if (!$name || !$email || !$password) {
            $this->json(['error' => 'Missing required fields: name, email, password'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Invalid email format'], 422);
            return;
        }

        $validRoles = ['admin', 'provider', 'patient', 'system_admin'];

        if (!in_array($role, $validRoles, true)) {
            $this->json(['error' => 'Invalid role. Must be one of: ' . implode(', ', $validRoles)], 422);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            $this->json(['error' => 'Email already exists'], 409);
            return;
        }

        $id = $this->userModel->create([
            'username' => $input['username'] ?? $name,
            'email' => $email,
            'password' => $password,
            'first_name' => $input['first_name'] ?? $name,
            'last_name' => $input['last_name'] ?? '',
            'role' => $role,
        ]);

        $this->auditService->log('create', 'user', $id, "Admin created user #{$id} ({$role})");

        $this->json(['message' => 'User created', 'id' => $id], 201);
    }

    public function updateUserRole(): void
    {
        $input = $this->getInput();

        $userId = $input['user_id'] ?? null;
        $newRole = $input['role'] ?? null;

        if (!$userId || !$newRole) {
            $this->json(['error' => 'Missing required fields: user_id, role'], 422);
            return;
        }

        $validRoles = ['admin', 'provider', 'patient', 'system_admin'];

        if (!in_array($newRole, $validRoles, true)) {
            $this->json(['error' => 'Invalid role. Must be one of: ' . implode(', ', $validRoles)], 422);
            return;
        }

        $targetUser = $this->userModel->find((int) $userId);

        if (!$targetUser) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }

        $previousRole = $targetUser['role'];

        $this->userModel->update((int) $userId, ['role' => $newRole]);

        $this->auditService->log(
            'update_role',
            'user',
            (int) $userId,
            "Changed user #{$userId} role from {$previousRole} to {$newRole}"
        );

        $this->json(['message' => 'User role updated', 'previous_role' => $previousRole, 'new_role' => $newRole]);
    }

    private function handleApiAudit(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        $filters = [];

        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int) $_GET['user_id'];
        }

        if (!empty($_GET['action'])) {
            $filters['action'] = $_GET['action'];
        }

        if (!empty($_GET['resource_type'])) {
            $filters['resource_type'] = $_GET['resource_type'];
        }

        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : 50;

        $result = $this->auditService->getLogs($filters, $page, $perPage);

        $this->auditService->log('view', 'audit_logs', null, "Admin viewed audit logs (page {$page})");

        $this->json($result);
    }

    private function handleApiReports(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        $reportType = $_GET['type'] ?? 'overview';

        $allUsers = $this->userModel->all();
        $allPatients = $this->patientModel->all();
        $allProviders = $this->providerModel->all();
        $allRecords = $this->medicalRecordModel->all();

        $report = match ($reportType) {
            'users' => $this->generateUserReport($allUsers),
            'records' => $this->generateRecordReport($allRecords),
            'providers' => $this->generateProviderReport($allProviders),
            'patients' => $this->generatePatientReport($allPatients),
            default => $this->generateOverviewReport($allUsers, $allPatients, $allProviders, $allRecords),
        };

        $this->auditService->log('view', 'report', null, "Generated {$reportType} report");

        $this->json(['report_type' => $reportType, 'generated_at' => date('Y-m-d H:i:s'), 'data' => $report]);
    }

    public function dashboardApi(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user || $user['role'] !== 'admin') {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        $allUsers = $this->userModel->all();
        $allPatients = $this->patientModel->all();
        $allProviders = $this->providerModel->all();
        $allRecords = $this->medicalRecordModel->all();
        $allHospitals = $this->hospitalModel->all();

        $roles = [];
        foreach ($allUsers as $u) {
            $role = $u['role'] ?? 'unknown';
            $roles[$role] = ($roles[$role] ?? 0) + 1;
        }

        $recentAudit = $this->auditService->getLogs([], 1, 15);

        $this->json([
            'stats' => [
                'total_users' => count($allUsers),
                'total_patients' => count($allPatients),
                'total_providers' => count($allProviders),
                'total_records' => count($allRecords),
                'total_hospitals' => count($allHospitals),
                'users_by_role' => $roles,
            ],
            'recent_users' => array_slice($allUsers, 0, 10),
            'recent_audit_logs' => $recentAudit['data'],
        ]);
    }

    public function settings(): void
    {
        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $settings = [
            'session_timeout' => $_ENV['SESSION_TIMEOUT'] ?? '300',
            'max_login_attempts' => $_ENV['MAX_LOGIN_ATTEMPTS'] ?? '5',
            'min_password_length' => $_ENV['MIN_PASSWORD_LENGTH'] ?? '8',
            'require_mfa' => false,
        ];

        $this->view('admin.settings', ['user' => $user, 'settings' => $settings, 'currentPage' => 'settings']);
    }

    public function updateSettings(): void
    {
        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $settings = [
            'session_timeout' => $_POST['session_timeout'] ?? '300',
            'max_login_attempts' => $_POST['max_login_attempts'] ?? '5',
            'min_password_length' => $_POST['min_password_length'] ?? '8',
            'require_mfa' => isset($_POST['require_mfa']),
        ];

        $this->auditService->log('update', 'settings', null, 'Admin updated system settings');

        $this->view('admin.settings', ['user' => $user, 'settings' => $settings, 'currentPage' => 'settings']);
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/');
    }

    private function generateUserReport(array $users): array
    {
        $roles = [];
        $recent = [];

        foreach ($users as $u) {
            $role = $u['role'] ?? 'unknown';
            $roles[$role] = ($roles[$role] ?? 0) + 1;

            if (isset($u['created_at']) && strtotime($u['created_at']) > strtotime('-30 days')) {
                $recent[] = $u;
            }
        }

        return [
            'total_users' => count($users),
            'by_role' => $roles,
            'registered_last_30_days' => count($recent),
            'recent_registrations' => array_slice($recent, 0, 10),
        ];
    }

    private function generateRecordReport(array $records): array
    {
        $byType = [];
        $byMonth = [];

        foreach ($records as $r) {
            $type = $r['record_type'] ?? 'unknown';
            $byType[$type] = ($byType[$type] ?? 0) + 1;

            if (isset($r['record_date'])) {
                $month = date('Y-m', strtotime($r['record_date']));
                $byMonth[$month] = ($byMonth[$month] ?? 0) + 1;
            }
        }

        krsort($byMonth);

        return [
            'total_records' => count($records),
            'by_type' => $byType,
            'by_month' => $byMonth,
            'recent_records' => array_slice($records, 0, 10),
        ];
    }

    private function generateProviderReport(array $providers): array
    {
        $bySpecialization = [];

        foreach ($providers as $p) {
            $spec = $p['specialization'] ?? 'unknown';
            $bySpecialization[$spec] = ($bySpecialization[$spec] ?? 0) + 1;
        }

        return [
            'total_providers' => count($providers),
            'by_specialization' => $bySpecialization,
            'recent_providers' => array_slice($providers, 0, 10),
        ];
    }

    private function generatePatientReport(array $patients): array
    {
        $byBloodType = [];

        foreach ($patients as $p) {
            $bt = $p['blood_type'] ?? 'unknown';
            $byBloodType[$bt] = ($byBloodType[$bt] ?? 0) + 1;
        }

        return [
            'total_patients' => count($patients),
            'by_blood_type' => $byBloodType,
            'recent_patients' => array_slice($patients, 0, 10),
        ];
    }

    private function generateOverviewReport(
        array $users,
        array $patients,
        array $providers,
        array $records
    ): array {
        $roles = [];
        foreach ($users as $u) {
            $role = $u['role'] ?? 'unknown';
            $roles[$role] = ($roles[$role] ?? 0) + 1;
        }

        $types = [];
        foreach ($records as $r) {
            $type = $r['record_type'] ?? 'unknown';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }

        return [
            'total_users' => count($users),
            'total_patients' => count($patients),
            'total_providers' => count($providers),
            'total_records' => count($records),
            'users_by_role' => $roles,
            'records_by_type' => $types,
        ];
    }
}
