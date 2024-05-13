<?php

namespace App\Services;

use App\Rules\RecordExist;
use App\Rules\SameField;
use Exception;
use App\Rules\RecordUnique;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

//class Validator
//{
//    private array $violations;
//    public function __construct($violations){
//        $this->violations = $violations;
//    }
//
//    /**
//     * @param array $values
//     * @param array $validationRules
//     * @return Validator
//     * @throws Exception
//     */
//    public static function validate(array $values, array $validationRules): Validator
//    {
//        $violations = [];
//
//        $validator = Validation::createValidator();
//        foreach ($values as $key => $value) {
//            $tempViolations = $validator->validate($value, $validationRules[$key]);
//            $violations[$key] = [];
//            foreach ($tempViolations as $v) {
//                $violations[$key][] = $v->getMessage();
//            }
//        }
//
//        return new self($violations);
//    }
//    public function failedValidation(): bool
//    {
//        return (count($this->violations) > 0);
//    }
//    public function getViolations(): array{
//        return array_filter($this->violations, function ($value) {
//            return !empty($value);
//        });
//    }
//}

class Validator
{
    private array $violations;

    public function __construct($violations)
    {
        $this->violations = $violations;
    }

    public static function make(array $data, array $rules): Validator
    {
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
                case 'exist':
                    if (count($parameters) < 2) {
                        throw new \InvalidArgumentException("Validation rule 'exist' requires at least two parameter (table name).");
                    }
                    $fieldName = $parameters[0];
                    $modelName = $parameters[1];
                    $constraints[] = new RecordExist([
                        'model' => $modelName,
                        'field' => $fieldName
                    ], 'This '.$fieldName . ' is Not Existed.');
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported validation rule: $ruleName");
            }
        }

        return $constraints;
    }
}
