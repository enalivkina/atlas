<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;
use DateMalformedStringException;

final class DateValidator implements RuleValidatorInterface
{
    public function validate(mixed $value, array $options = []): void
    {
        if (is_string($value) === false || $value === '') {
            throw new ValidationException($options['errorMessage'] ?? 'Значение должно быть строкой содержащей дату');
        }

        if (isset($options['format']) === true) {
            if (
                \DateTimeImmutable::createFromFormat($options['format'], $value) === false
                || \DateTimeImmutable::getLastErrors() !== false
            ) {
                throw new ValidationException($options['errorMessage'] ?? "Значение должно быть датой в формате {$options['format']}");
            }

            return;
        }

        try {
            new \DateTimeImmutable($value);
        } catch (DateMalformedStringException) {
            throw new ValidationException($options['errorMessage'] ?? 'Значение должно быть датой');
        }
    }
}
