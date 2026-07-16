<?php

declare(strict_types=1);

namespace PIS\Controllers;

use PIS\Models\Provider;

class ProviderController extends Controller
{
    private Provider $model;

    public function __construct()
    {
        $this->model = new Provider();
    }

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
