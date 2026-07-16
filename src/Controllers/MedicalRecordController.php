<?php

declare(strict_types=1);

namespace PIS\Controllers;

use PIS\Models\MedicalRecord;

class MedicalRecordController extends Controller
{
    private MedicalRecord $model;

    public function __construct()
    {
        $this->model = new MedicalRecord();
    }

    public function index(): void
    {
        $records = $this->model->all();
        $this->json(['data' => $records]);
    }

    public function show(string $id): void
    {
        $record = $this->model->find((int) $id);

        if (!$record) {
            $this->json(['error' => 'Record not found'], 404);
            return;
        }

        $this->json(['data' => $record]);
    }

    public function store(): void
    {
        $input = $this->getInput();
        $id = $this->model->create($input);
        $this->json(['message' => 'Record created', 'id' => $id], 201);
    }

    public function update(string $id): void
    {
        $input = $this->getInput();
        $this->model->update((int) $id, $input);
        $this->json(['message' => 'Record updated']);
    }

    public function destroy(string $id): void
    {
        $this->model->delete((int) $id);
        $this->json(['message' => 'Record deleted']);
    }
}
