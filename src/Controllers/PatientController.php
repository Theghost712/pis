<?php

declare(strict_types=1);

namespace PIS\Controllers;

use PIS\Models\Patient;

class PatientController extends Controller
{
    private Patient $model;

    public function __construct()
    {
        $this->model = new Patient();
    }

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
