<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;

final class SafeValidator implements RuleValidatorInterface
{
    /**
     * Всегда успешная валидация
     * @param mixed $value
     * @param array $options
     * @return void
     */
    public function validate(mixed $value, array $options = []): void {}
}
