<?php

namespace App\Rules;

use Symfony\Component\Validator\Constraint;

class RecordUnique extends Constraint
{
    public mixed $message = 'The record exists at "{{ model }}".';
    public string $mode = 'strict'; // If the constraint has configuration options, define them as public properties

    public array $input;
    public function __construct(
        array $input,
        string $message = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        $options['input'] = $input;
        parent::__construct($options, $groups, $payload);
        $this->message = $message ?? $this->message;
        $this->input = $input;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'input';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['input'];
    }
}