<?php

declare(strict_types=1);

namespace App\Core;

class Validation
{
    private array $errors = [];
    private array $data = [];
    private array $validated = [];

    public function validate(array $data, array $rules): bool
    {
        $this->data = $data;
        $this->errors = [];
        $this->validated = [];

        foreach ($rules as $field => $ruleSet) {
            $fieldRules = explode('|', $ruleSet);
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;
        $ruleName = $rule;
        $parameter = null;

        if (strpos($rule, ':') !== false) {
            [$ruleName, $parameter] = explode(':', $rule);
        }

        switch ($ruleName) {
            case 'required':
                if ($this->isEmpty($value)) {
                    $this->addError($field, "The {$field} field is required.");
                } else {
                    $this->validated[$field] = $value;
                }
                break;

            case 'email':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$field} must be a valid email address.");
                }
                break;

            case 'min':
                if (!$this->isEmpty($value) && strlen((string) $value) < (int) $parameter) {
                    $this->addError($field, "The {$field} must be at least {$parameter} characters.");
                }
                break;

            case 'max':
                if (!$this->isEmpty($value) && strlen((string) $value) > (int) $parameter) {
                    $this->addError($field, "The {$field} must not exceed {$parameter} characters.");
                }
                break;

            case 'numeric':
                if (!$this->isEmpty($value) && !is_numeric($value)) {
                    $this->addError($field, "The {$field} must be a number.");
                }
                break;

            case 'integer':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, "The {$field} must be an integer.");
                }
                break;

            case 'date':
                if (!$this->isEmpty($value) && !strtotime($value)) {
                    $this->addError($field, "The {$field} must be a valid date.");
                }
                break;

            case 'regex':
                if (!$this->isEmpty($value) && !preg_match($parameter, (string) $value)) {
                    $this->addError($field, "The {$field} format is invalid.");
                }
                break;

            case 'in':
                if (!$this->isEmpty($value)) {
                    $allowed = array_map('trim', explode(',', $parameter));
                    if (!in_array($value, $allowed)) {
                        $this->addError($field, "The {$field} must be one of: " . implode(', ', $allowed));
                    }
                }
                break;

            case 'unique':
                if (!$this->isEmpty($value)) {
                    $db = Database::getInstance();
                    [$table, $column] = explode(',', $parameter);
                    $stmt = $db->prepareAndExecute(
                        "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?",
                        [$value]
                    );
                    if ($stmt->fetchColumn() > 0) {
                        $this->addError($field, "The {$field} is already taken.");
                    }
                }
                break;

            case 'confirmed':
                $confirmationField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmationField] ?? null)) {
                    $this->addError($field, "The {$field} confirmation does not match.");
                }
                break;

            case 'exists':
                if (!$this->isEmpty($value)) {
                    $db = Database::getInstance();
                    [$table, $column] = explode(',', $parameter);
                    $stmt = $db->prepareAndExecute(
                        "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?",
                        [$value]
                    );
                    if ($stmt->fetchColumn() == 0) {
                        $this->addError($field, "The {$field} does not exist.");
                    }
                }
                break;

            case 'boolean':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $this->addError($field, "The {$field} must be true or false.");
                }
                break;

            case 'nullable':
                if ($this->isEmpty($value)) {
                    unset($this->errors[$field]);
                }
                break;
        }
    }

    private function isEmpty($value): bool
    {
        if ($value === null) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && empty($value)) return true;
        return false;
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $errors) {
            return $errors[0] ?? null;
        }
        return null;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getValidatedData(): array
    {
        return $this->validated;
    }

    public function setValidatedData(array $data): void
    {
        $this->validated = $data;
    }
}
