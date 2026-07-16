<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Provider;
use App\Services\AuthService;
use App\Services\AuditService;
use App\Core\Session;

class RecordController extends Controller
{
    private MedicalRecord $model;
    private Patient $patientModel;
    private Provider $providerModel;
    private AuthService $authService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->model = new MedicalRecord();
        $this->patientModel = new Patient();
        $this->providerModel = new Provider();
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
    }

    // ========== WEB ROUTES ==========

    public function addForm(): void
    {
        $userId = Session::get('user_id');
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];
        $patients = $this->patientModel->all();

        $this->view('provider.add_record', ['user' => $user, 'patients' => $patients, 'currentPage' => 'add_record']);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/provider/records/add');
            return;
        }

        $input = $this->getInput();
        $userId = Session::get('user_id');
        $provider = $this->providerModel->findByUserId($userId);

        $this->model->create([
            'patient_id' => (int) ($input['patient_id'] ?? 0),
            'provider_id' => $provider ? $provider['id'] : null,
            'record_type' => $input['record_type'] ?? 'visit',
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'diagnosis' => $input['diagnosis'] ?? '',
            'notes' => $input['notes'] ?? '',
            'record_date' => $input['record_date'] ?? date('Y-m-d'),
        ]);

        Session::flash('success', 'Medical record created successfully.');
        $this->redirect('/provider/patients');
    }

    // ========== API ROUTES ==========

    public function index(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $patientId = isset($_GET['patient_id']) ? (int) $_GET['patient_id'] : null;
        $providerId = isset($_GET['provider_id']) ? (int) $_GET['provider_id'] : null;
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : 20;

        if ($patientId !== null) {
            $patient = $this->patientModel->find($patientId);

            if (!$patient) {
                $this->json(['error' => 'Patient not found'], 404);
                return;
            }

            $records = $this->model->findByPatientId($patientId);
            $this->json(['data' => $records, 'patient_id' => $patientId]);
            return;
        }

        if ($providerId !== null) {
            $provider = $this->providerModel->find($providerId);

            if (!$provider) {
                $this->json(['error' => 'Provider not found'], 404);
                return;
            }

            $records = $this->model->findByProviderId($providerId);
            $this->json(['data' => $records, 'provider_id' => $providerId]);
            return;
        }

        $result = $this->model->paginate($page, $perPage);
        $this->json($result);
    }

    public function show(string $id): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $record = $this->model->find((int) $id);

        if (!$record) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $this->auditService->log('view', 'medical_record', (int) $id, "Viewed medical record #{$id}");

        $this->json(['data' => $record]);
    }

    public function store(): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $input = $this->getInput();

        $patientId = $input['patient_id'] ?? null;
        $recordType = $input['record_type'] ?? null;
        $title = $input['title'] ?? null;
        $recordDate = $input['record_date'] ?? null;

        if (!$patientId || !$recordType || !$title || !$recordDate) {
            $this->json(['error' => 'Missing required fields: patient_id, record_type, title, record_date'], 422);
            return;
        }

        $patient = $this->patientModel->find((int) $patientId);

        if (!$patient) {
            $this->json(['error' => 'Patient not found'], 404);
            return;
        }

        $provider = $this->providerModel->findByUserId($user['id']);
        $providerId = $input['provider_id'] ?? ($provider ? $provider['id'] : null);

        $data = [
            'patient_id' => (int) $patientId,
            'provider_id' => $providerId ? (int) $providerId : null,
            'record_type' => $recordType,
            'title' => $title,
            'description' => $input['description'] ?? null,
            'diagnosis' => $input['diagnosis'] ?? null,
            'notes' => $input['notes'] ?? null,
            'record_date' => $recordDate,
        ];

        $id = $this->model->create($data);

        $this->auditService->log('create', 'medical_record', $id, "Created medical record #{$id} for patient #{$patientId}");

        $this->json(['message' => 'Record created', 'id' => $id], 201);
    }

    public function update(string $id): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $record = $this->model->find((int) $id);

        if (!$record) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $input = $this->getInput();

        $allowed = [
            'patient_id', 'provider_id', 'record_type', 'title',
            'description', 'diagnosis', 'notes', 'record_date',
        ];

        $updateData = array_intersect_key($input, array_flip($allowed));

        if (empty($updateData)) {
            $this->json(['error' => 'No valid fields to update'], 422);
            return;
        }

        $this->model->update((int) $id, $updateData);

        $this->auditService->log('update', 'medical_record', (int) $id, "Updated medical record #{$id}");

        $this->json(['message' => 'Record updated', 'data' => $this->model->find((int) $id)]);
    }

    public function destroy(string $id): void
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        if ($user['role'] !== 'admin' && $user['role'] !== 'provider') {
            $this->json(['error' => 'Insufficient permissions'], 403);
            return;
        }

        $record = $this->model->find((int) $id);

        if (!$record) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $this->auditService->log('delete', 'medical_record', (int) $id, "Deleted medical record #{$id} (patient #{$record['patient_id']})");

        $this->model->delete((int) $id);

        $this->json(['message' => 'Record deleted']);
    }
}
