<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Provider;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Core\Session;

class ProviderController extends Controller
{
    private Provider $model;
    private Patient $patientModel;
    private MedicalRecord $recordModel;

    public function __construct()
    {
        $this->model = new Provider();
        $this->patientModel = new Patient();
        $this->recordModel = new MedicalRecord();
    }

    // ========== WEB ROUTES ==========

    public function patients(): void
    {
        $userId = Session::get('user_id');
        $provider = $this->model->findByUserId($userId);

        $records = $provider ? $this->recordModel->findByProviderId($provider->getId()) : [];
        $patientIds = array_unique(array_column($records, 'patient_id'));
        $patients = [];
        foreach ($patientIds as $pid) {
            $p = $this->patientModel->find($pid);
            if ($p) $patients[] = $p;
        }

        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];
        $this->view('provider.patients', ['user' => $user, 'patients' => $patients, 'currentPage' => 'patients']);
    }

    public function patientView(string $id): void
    {
        $patient = $this->patientModel->find((int) $id);
        $records = $patient ? $this->recordModel->findByPatientId($patient['id']) : [];
        $user = ['id' => Session::get('user_id'), 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $this->view('provider.patient_view', ['user' => $user, 'patient' => $patient, 'records' => $records, 'currentPage' => 'patients']);
    }

    public function referrals(): void
    {
        $user = ['id' => Session::get('user_id'), 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];
        $this->view('provider.referrals', ['user' => $user, 'currentPage' => 'referrals']);
    }

    public function records(): void
    {
        $userId = Session::get('user_id');
        $provider = $this->model->findByUserId($userId);
        $records = $provider ? $this->recordModel->findByProviderId($provider->getId()) : [];

        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];
        $this->view('provider.records', ['user' => $user, 'records' => $records, 'currentPage' => 'records']);
    }

    // ========== API ROUTES ==========

    public function index(): void
    {
        $providers = $this->model->all();
        $this->json(['data' => $providers]);
    }

    public function show(string $id): void
    {
        $provider = $this->model->find((int) $id);
        if (!$provider) {
            $this->json(['error' => 'Provider not found'], 404);
            return;
        }
        $this->json(['data' => $provider]);
    }

    public function store(): void
    {
        $input = $this->getInput();
        $id = $this->model->create($input);
        $this->json(['message' => 'Provider created', 'id' => $id], 201);
    }

    public function update(string $id): void
    {
        $input = $this->getInput();
        $this->model->update((int) $id, $input);
        $this->json(['message' => 'Provider updated']);
    }

    public function destroy(string $id): void
    {
        $this->model->delete((int) $id);
        $this->json(['message' => 'Provider deleted']);
    }
}
