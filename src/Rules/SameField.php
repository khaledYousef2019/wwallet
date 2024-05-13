<?php

namespace App\Rules;

use Symfony\Component\Validator\Constraint;

class SameField extends Constraint
{
    public string $field;

    public $message = 'The values do not match.';

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!isset($options['field'])) {
            throw new \InvalidArgumentException("The option 'field' must be provided for constraint 'SameField'.");
        }

        $this->field = $options['field'];
    }
}
