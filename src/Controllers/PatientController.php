<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\Security;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\User;

class PatientController
{
    private Session $session;
    private Security $security;
    private Patient $model;
    private MedicalRecord $recordModel;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->session->start();
        $this->security = new Security();
        $this->model = new Patient();
        $this->recordModel = new MedicalRecord();

        if (!$this->session->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function profile(): void
    {
        $userId = $this->session->getUserId();
        $patient = $this->model->findByUserId($userId);

        ob_start();
        require __DIR__ . '/../Views/patient/profile.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function updateProfile(): void
    {
        $userId = $this->session->getUserId();
        $patient = $this->model->findByUserId($userId);

        if ($patient) {
            $allowed = ['date_of_birth', 'phone', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'blood_type', 'allergies'];
            $data = [];
            foreach ($allowed as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }
            if (!empty($data)) {
                $this->model->update($data);
            }
        }

        $this->session->setFlash('success', 'Profile updated successfully.');
        header('Location: /patient/profile');
        exit;
    }

    public function records(): void
    {
        $userId = $this->session->getUserId();
        $patient = $this->model->findByUserId($userId);
        $records = $patient ? $this->recordModel->findByPatientId($patient->getId()) : [];

        ob_start();
        require __DIR__ . '/../Views/patient/records.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/main.php';
    }
}
