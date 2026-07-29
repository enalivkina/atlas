<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;

final class RequiredValidator implements RuleValidatorInterface
{
    public function validate(mixed $value, array $options = []): void
    {
        if (is_null($value) === true || $value === '' || $value === []) {
            throw new ValidationException($options['errorMessage'] ?? 'Значение обязательно для заполнения');
        }
    }
}
