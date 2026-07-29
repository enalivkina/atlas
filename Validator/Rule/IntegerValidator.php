<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;

final class IntegerValidator implements RuleValidatorInterface
{
    public function validate(mixed $value, array $options = []): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException($options['errorMessage'] ?? 'Значение должно быть числом');
        }

        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;

        if ($min !== null && $value < $min) {
            throw new ValidationException($options['minErrorMessage'] ?? "Значение должно быть не меньше $min");
        }

        if ($max !== null && $value > $max) {
            throw new ValidationException($options['minErrorMessage'] ?? "Значение должно быть не больше $max");
        }
    }
}
