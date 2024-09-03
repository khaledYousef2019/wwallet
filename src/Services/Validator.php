<?php

namespace App\Services;

use App\Rules\RecordExist;
use App\Rules\SameField;
use App\Rules\RecordUnique;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class Validator
{
    private array $violations;

    public function __construct(array $violations)
    {
        $this->violations = $violations;
    }

    public static function make(array $data, array $rules): Validator
    {
        // Sanitize input data
        $data = self::sanitizeInput($data);

        $violations = [];

        $validator = Validation::createValidator();

        foreach ($rules as $field => $rule) {
            $tempViolations = $validator->validate($data[$field], self::convertToConstraints($rule));
            $violations[$field] = [];
            foreach ($tempViolations as $v) {
                $violations[$field][] = $v->getMessage();
            }
        }

        return new self($violations);
    }

    public function failedValidation(): bool
    {
        return count($this->violations) > 0;
    }

    public function getViolations(): array
    {
        return array_filter($this->violations, function ($value) {
            return !empty($value);
        });
    }

    private static function convertToConstraints(string $rules): array
    {
        $constraints = [];
        $ruleList = explode('|', $rules);
        foreach ($ruleList as $rule) {
            $parts = explode(':', $rule);
            $ruleName = $parts[0];
            $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];
            switch ($ruleName) {
                case 'required':
                    $constraints[] = new NotBlank();
                    break;
                case 'string':
                    $constraints[] = new Type('string');
                    break;
                case 'email':
                    $constraints[] = new Email();
                    break;
                case 'min':
                    $constraints[] = new Length(['min' => $parameters[0]]);
                    break;
                case 'max':
                    $constraints[] = new Length(['max' => $parameters[0]]);
                    break;
                case 'same':
                    if (count($parameters) < 1) {
                        throw new \InvalidArgumentException("Validation rule 'same' requires at least one parameter (field name).");
                    }
                    $fieldName = $parameters[0];
                    $constraints[] = new SameField(['field' => $fieldName]);
                    break;
                case 'unique':
                    if (count($parameters) < 2) {
                        throw new \InvalidArgumentException("Validation rule 'unique' requires at least one parameter (table name).");
                    }
                    $fieldName = $parameters[0];
                    $modelName = $parameters[1];
                    $constraints[] = new RecordUnique([
                        'model' => $modelName,
                        'field' => $fieldName
                    ], $fieldName . ' is already existed.');
                    break;
                case 'exists':
                    if (count($parameters) < 2) {
                        throw new \InvalidArgumentException("Validation rule 'exist' requires at least two parameter (table name).");
                    }
                    $fieldName = $parameters[0];
                    $modelName = $parameters[1];
                    $constraints[] = new RecordExist([
                        'model' => $modelName,
                        'field' => $fieldName
                    ], 'This ' . $fieldName . ' is Not Existed.');
                    break;
                case 'strong_password':
                    $constraints[] = new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                        'message' => 'Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character.'
                    ]);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported validation rule: $ruleName");
            }
        }

        return $constraints;
    }

    private static function sanitizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Basic sanitization: strip tags, trim whitespace, and encode for output
                $data[$key] = htmlspecialchars(trim(strip_tags($value)), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                // Recursively sanitize nested arrays
                $data[$key] = self::sanitizeInput($value);
            }
        }
        return $data;
    }
}