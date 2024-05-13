<?php

namespace App\Rules;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SameFieldValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint SameField */
        $field = $constraint->field;

        // Get the object being validated
        $object = $this->context->getObject();

        // Check if the object exists and has the expected property
        if ($object !== null && property_exists($object, $field)) {
            // Get the value of the other field
            $otherFieldValue = $object->{$field};

            // Check if both values are not null and are different
            if ($value !== $otherFieldValue) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ field }}', $this->formatValue($field))
                    ->addViolation();
            }
        } else {
            // If the object or field doesn't exist, log a warning
            $this->context->buildViolation('Object or field does not exist.')
                ->addViolation();
        }
    }
}
