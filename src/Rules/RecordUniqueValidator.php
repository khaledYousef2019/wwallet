<?php

namespace App\Rules;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RecordUniqueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (
            !class_exists($constraint->input['model'])
            || !$constraint instanceof RecordUnique
        ) {
            throw new UnexpectedTypeException($constraint, RecordUnique::class);
        }

        if ($constraint->input['model']::where($constraint->input['field'], $value)->exists()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ model }}', (string) $value)
                ->addViolation();
        }
    }

}