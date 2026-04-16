<?php
namespace App\Core\Validation;

final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private bool $validated = false;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        if ($this->validated) {
            return empty($this->errors);
        }

        $this->validated = true;

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->validate();
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function errors(): array
    {
        $this->validate();
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$ruleName, $ruleValue] = $this->parseRule($rule);

        switch ($ruleName) {
            case 'required':
                if ($value === null || trim((string)$value) === '') {
                    $this->addError($field, ucfirst($field) . ' is required.');
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a string.');
                }
                break;

            case 'int':
                if ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, ucfirst($field) . ' must be an integer.');
                }
                break;

            case 'email':
                if ($value !== null && trim((string)$value) !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email address.');
                }
                break;

            case 'min':
                if ($value !== null && mb_strlen((string)$value) < (int)$ruleValue) {
                    $this->addError($field, ucfirst($field) . " must be at least {$ruleValue} characters.");
                }
                break;

            case 'max':
                if ($value !== null && mb_strlen((string)$value) > (int)$ruleValue) {
                    $this->addError($field, ucfirst($field) . " must not be longer than {$ruleValue} characters.");
                }
                break;

            case 'same':
                $otherValue = $this->data[$ruleValue] ?? null;
                if ($value !== $otherValue) {
                    $this->addError($field, ucfirst($field) . " must match {$ruleValue}.");
                }
                break;

            case 'in':
                $allowed = array_map('trim', explode(',', (string)$ruleValue));
                if ($value !== null && !in_array((string)$value, $allowed, true)) {
                    $this->addError($field, ucfirst($field) . ' contains an invalid value.');
                }
                break;

            case 'regex':
                if ($value !== null && trim((string)$value) !== '') {
                    if (@preg_match($ruleValue, (string)$value) !== 1) {
                        $this->addError($field, ucfirst($field) . ' has invalid format.');
                    }
                }
                break;
        }
    }

    private function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, null];
        }

        [$name, $value] = explode(':', $rule, 2);

        return [$name, $value];
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}




// $data = [
//     'email' => $_POST['email'] ?? null,
//     'password' => $_POST['password'] ?? null,
//     'password_confirmation' => $_POST['password_confirmation'] ?? null,
// ];

// $rules = [
//     'email' => ['required', 'email', 'max:255'],
//     'password' => ['required', 'min:8', 'max:64'],
//     'password_confirmation' => ['required', 'same:password'],
// ];

// $validator = new Validator($data, $rules);

// if (!$validator->validate()) {
//     Response::validation($validator->errors());
// }