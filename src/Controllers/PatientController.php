<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Core\Session;

class PatientController extends Controller
{
    private Patient $model;
    private MedicalRecord $recordModel;

    public function __construct()
    {
        $this->model = new Patient();
        $this->recordModel = new MedicalRecord();
    }

    // ========== WEB ROUTES ==========

    public function profile(): void
    {
        $userId = Session::get('user_id');
        $patient = $this->model->findByUserId($userId);
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $this->view('patient.profile', ['user' => $user, 'patient' => $patient, 'currentPage' => 'profile']);
    }

    public function updateProfile(): void
    {
        $userId = Session::get('user_id');
        $patient = $this->model->findByUserId($userId);

        if ($patient) {
            $input = $this->getInput();
            $this->model->update($patient['id'], $input);
        }

        Session::flash('success', 'Profile updated successfully.');
        $this->redirect('/patient/profile');
    }

    public function records(): void
    {
        $userId = Session::get('user_id');
        $patient = $this->model->findByUserId($userId);
        $records = $patient ? $this->recordModel->findByPatientId($patient['id']) : [];
        $user = ['id' => $userId, 'name' => Session::get('user_name'), 'email' => Session::get('user_email'), 'role' => Session::get('user_role')];

        $this->view('patient.records', ['user' => $user, 'records' => $records, 'currentPage' => 'records']);
    }

    // ========== API ROUTES ==========

    public function index(): void
    {
        $patients = $this->model->all();
        $this->json(['data' => $patients]);
    }

    public function show(string $id): void
    {
        $patient = $this->model->find((int) $id);

        if (!$patient) {
            $this->json(['error' => 'Patient not found'], 404);
            return;
        }

        $this->json(['data' => $patient]);
    }

    public function store(): void
    {
        $input = $this->getInput();
        $id = $this->model->create($input);
        $this->json(['message' => 'Patient created', 'id' => $id], 201);
    }

    public function update(string $id): void
    {
        $input = $this->getInput();
        $this->model->update((int) $id, $input);
        $this->json(['message' => 'Patient updated']);
    }

    public function destroy(string $id): void
    {
        $this->model->delete((int) $id);
        $this->json(['message' => 'Patient deleted']);
    }
}
