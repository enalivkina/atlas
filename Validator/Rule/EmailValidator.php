<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;

final class EmailValidator implements RuleValidatorInterface
{
    public function validate(mixed $value, array $options = []): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException($options['errorMessage'] ?? 'Значение должно быть корректным email адресом');
        }
    }
}
